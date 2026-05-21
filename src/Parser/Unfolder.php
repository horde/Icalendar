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
 * Unfolds iCalendar/vCard content lines.
 *
 * Handles RFC 5545 §3.1 line folding (CRLF + LWSP continuation),
 * mixed line endings (CR, LF, CRLF), UTF-8 BOM stripping, and
 * quoted-printable soft breaks (vCard 2.1 compatibility).
 */
final class Unfolder
{
    private const BOM = "\xEF\xBB\xBF";

    /**
     * Unfold a complete document string into logical lines.
     *
     * @param string $input Raw document content
     *
     * @return Generator<int, array{string, int, int}> Yields [logicalLine, lineNumber, byteOffset]
     */
    public function unfold(string $input): Generator
    {
        if (str_starts_with($input, self::BOM)) {
            $input = substr($input, 3);
        }

        $len = strlen($input);
        if ($len === 0) {
            return;
        }

        $pos = 0;
        $currentLine = '';
        $startLineNumber = 1;
        $startByteOffset = 0;
        $rawLineNumber = 1;

        while ($pos < $len) {
            $lineEndPos = $this->findLineEnding($input, $pos, $len);

            if ($lineEndPos === null) {
                $currentLine .= substr($input, $pos);
                break;
            }

            $currentLine .= substr($input, $pos, $lineEndPos - $pos);
            $pos = $this->skipLineEnding($input, $lineEndPos, $len);
            $rawLineNumber++;

            if ($pos < $len && ($input[$pos] === ' ' || $input[$pos] === "\t")) {
                $pos++;
                continue;
            }

            if ($this->isQpSoftBreak($currentLine) && $pos < $len) {
                $currentLine = substr($currentLine, 0, -1);
                continue;
            }

            yield [$currentLine, $startLineNumber, $startByteOffset];
            $currentLine = '';
            $startLineNumber = $rawLineNumber;
            $startByteOffset = $pos;
        }

        if ($currentLine !== '') {
            yield [$currentLine, $startLineNumber, $startByteOffset];
        }
    }

    /** Find the byte position of the next line ending (CR or LF). */
    private function findLineEnding(string $input, int $pos, int $len): ?int
    {
        while ($pos < $len) {
            $cr = strpos($input, "\r", $pos);
            $lf = strpos($input, "\n", $pos);

            if ($cr === false && $lf === false) {
                return null;
            }

            if ($cr === false) {
                return $lf;
            }

            if ($lf === false) {
                return $cr;
            }

            return min($cr, $lf);
        }

        return null;
    }

    /** Advance past a CR, LF, or CRLF sequence. */
    private function skipLineEnding(string $input, int $pos, int $len): int
    {
        if ($pos >= $len) {
            return $pos;
        }

        if ($input[$pos] === "\r") {
            $pos++;
            if ($pos < $len && $input[$pos] === "\n") {
                $pos++;
            }
            return $pos;
        }

        if ($input[$pos] === "\n") {
            return $pos + 1;
        }

        return $pos;
    }

    /**
     * Detect quoted-printable soft break: line ends with '=' and contains
     * QUOTED-PRINTABLE in the parameter region (before the first ':').
     */
    private function isQpSoftBreak(string $line): bool
    {
        if ($line === '' || $line[strlen($line) - 1] !== '=') {
            return false;
        }

        $colonPos = strpos($line, ':');
        $paramRegion = $colonPos !== false ? substr($line, 0, $colonPos) : $line;

        return stripos($paramRegion, 'QUOTED-PRINTABLE') !== false;
    }
}
