<?php

declare(strict_types=1);

/**
 * Copyright 2003-2026 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @author    Jan Schneider <jan@horde.org>
 * @author    Michael J Rubinsky <mrubinsk@horde.org>
 * @author    Chuck Hagenbuch <chuck@horde.org>
 * @author    Ralf Lang <ralf.lang@ralf-lang.de>
 * @category  Horde
 * @copyright 2003-2026 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package   Icalendar
 */

namespace Horde\Icalendar\Parser;

use Generator;

/**
 * Parses iCalendar/vCard content lines (RFC 5545 §3.1, RFC 6350 §3).
 *
 * Uses a position-tracking tokenizer approach: strcspn() for fast
 * delimiter scanning, character-by-character only within quoted regions.
 *
 * Does NOT interpret property values — produces raw ContentLine objects.
 */
final class ContentLineParser
{
    public function __construct(
        private readonly bool $strict = false,
    ) {}

    /**
     * Parse a complete document string.
     *
     * @return Generator<int, ContentLine>
     */
    public function parse(string $input): Generator
    {
        $unfolder = new Unfolder();

        foreach ($unfolder->unfold($input) as [$line, $lineNumber, $byteOffset]) {
            if ($line === '') {
                continue;
            }
            yield $this->lexLine($line, $lineNumber, $byteOffset);
        }
    }

    /**
     * Parse a single pre-unfolded content line.
     */
    public function parseLine(string $line, int $lineNumber = 1, int $byteOffset = 0): ContentLine
    {
        return $this->lexLine($line, $lineNumber, $byteOffset);
    }

    /** Tokenize a single unfolded line into a ContentLine. */
    private function lexLine(string $line, int $lineNumber, int $byteOffset): ContentLine
    {
        $len = strlen($line);
        $pos = 0;
        $errors = [];

        // 1. Extract group prefix and property name
        $nameEnd = strcspn($line, '.;:', $pos);

        $group = null;
        $name = '';

        if ($nameEnd < $len && $line[$nameEnd] === '.') {
            $group = substr($line, 0, $nameEnd);
            $pos = $nameEnd + 1;
            $nameEnd2 = strcspn($line, ';:', $pos);
            $name = substr($line, $pos, $nameEnd2);
            $pos = $pos + $nameEnd2;
        } else {
            $name = substr($line, 0, $nameEnd);
            $pos = $nameEnd;
        }

        if ($name === '' || !$this->isValidName($name)) {
            $errors[] = ParseError::InvalidName;
            if ($this->strict) {
                throw new ParseException('Invalid property name', $lineNumber, $byteOffset);
            }
        }

        $name = strtoupper($name);

        // 2. Parse parameters
        $parameters = [];

        while ($pos < $len && $line[$pos] === ';') {
            $pos++;

            // Extract parameter name
            $paramNameEnd = strcspn($line, '=;:', $pos);
            $paramName = substr($line, $pos, $paramNameEnd);
            $pos += $paramNameEnd;

            if ($paramName === '' || !$this->isValidName($paramName)) {
                $errors[] = ParseError::InvalidParamName;
                if ($this->strict) {
                    throw new ParseException(
                        "Invalid parameter name: \"$paramName\"",
                        $lineNumber,
                        $byteOffset,
                    );
                }
            }

            $paramNameUpper = strtoupper($paramName);

            // Check if there's a value (= follows)
            $values = [];
            if ($pos < $len && $line[$pos] === '=') {
                $pos++;

                // Parse comma-separated values
                do {
                    [$value, $pos, $parseErrors] = $this->parseParamValue($line, $pos, $len);
                    $values[] = $value;
                    $errors = array_merge($errors, $parseErrors);
                } while ($pos < $len && $line[$pos] === ',' && ++$pos);
            } else {
                // Naked param (vCard 2.1 style): TEL;HOME:...
                $values = [''];
            }

            // Merge duplicate parameter names
            if (isset($parameters[$paramNameUpper])) {
                $existing = $parameters[$paramNameUpper];
                $values = array_merge($existing->values, $values);
            }

            $parameters[$paramNameUpper] = new Parameter($paramNameUpper, $values);
        }

        // 3. Extract value
        $value = '';
        if ($pos < $len && $line[$pos] === ':') {
            $pos++;
            $value = substr($line, $pos);
        } else {
            $errors[] = ParseError::MissingColon;
            if ($this->strict) {
                throw new ParseException('Missing colon separator', $lineNumber, $byteOffset);
            }
            // Best effort: treat remainder as value
            if ($pos < $len) {
                $value = substr($line, $pos);
            }
        }

        return new ContentLine(
            $group,
            $name,
            $parameters,
            $value,
            $lineNumber,
            $byteOffset,
            $errors,
        );
    }

    /**
     * Parse a single parameter value (quoted or unquoted).
     *
     * @return array{string, int, ParseError[]} [value, newPosition, errors]
     */
    private function parseParamValue(string $line, int $pos, int $len): array
    {
        $errors = [];

        if ($pos < $len && $line[$pos] === '"') {
            // Quoted string — scan to closing DQUOTE
            $pos++;
            $closeQuote = strpos($line, '"', $pos);

            if ($closeQuote === false) {
                $errors[] = ParseError::UnterminatedQuotedString;
                // Take rest of param region up to ; or : as value
                $endPos = $pos + strcspn($line, ';:', $pos);
                $value = substr($line, $pos, $endPos - $pos);
                return [$value, $endPos, $errors];
            }

            $value = substr($line, $pos, $closeQuote - $pos);
            return [$value, $closeQuote + 1, $errors];
        }

        // Unquoted — scan SAFE-CHAR set (stop at ; : , ")
        $endPos = $pos + strcspn($line, ';:,"', $pos);
        $value = substr($line, $pos, $endPos - $pos);

        return [$value, $endPos, $errors];
    }

    /** Check whether a string is a valid iCalendar name token. */
    private function isValidName(string $name): bool
    {
        return $name !== '' && strspn($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-') === strlen($name);
    }
}
