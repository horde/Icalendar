<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Parser;

use Horde\Icalendar\Parser\Unfolder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Unfolder::class)]
class UnfolderTest extends TestCase
{
    private Unfolder $unfolder;

    protected function setUp(): void
    {
        $this->unfolder = new Unfolder();
    }

    public function testSimpleUnfoldedLines(): void
    {
        $input = "VERSION:2.0\r\nPRODID:-//Test//EN\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(2, $lines);
        $this->assertSame('VERSION:2.0', $lines[0][0]);
        $this->assertSame('PRODID:-//Test//EN', $lines[1][0]);
    }

    public function testCrlfFolding(): void
    {
        $input = "DESCRIPTION:This is a\r\n  long description\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('DESCRIPTION:This is a long description', $lines[0][0]);
    }

    public function testTabContinuation(): void
    {
        $input = "DESCRIPTION:Part one\r\n\tpart two\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('DESCRIPTION:Part onepart two', $lines[0][0]);
    }

    public function testLfOnlyFolding(): void
    {
        $input = "SUMMARY:Hello\n World\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('SUMMARY:HelloWorld', $lines[0][0]);
    }

    public function testCrOnlyFolding(): void
    {
        $input = "SUMMARY:Hello\r World\r";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('SUMMARY:HelloWorld', $lines[0][0]);
    }

    public function testMultipleFoldsOnSameLine(): void
    {
        $input = "DESCRIPTION:One\r\n Two\r\n Three\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('DESCRIPTION:OneTwoThree', $lines[0][0]);
    }

    public function testBomStripping(): void
    {
        $input = "\xEF\xBB\xBFVERSION:2.0\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('VERSION:2.0', $lines[0][0]);
    }

    public function testLineNumberTracking(): void
    {
        $input = "LINE1:value1\r\nLINE2:value2\r\nLINE3:value3\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertSame(1, $lines[0][1]);
        $this->assertSame(2, $lines[1][1]);
        $this->assertSame(3, $lines[2][1]);
    }

    public function testLineNumberWithFolding(): void
    {
        // Folded line spans raw lines 1-2; next logical line starts at raw line 3
        $input = "DESCRIPTION:Long\r\n  value\r\nVERSION:2.0\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(2, $lines);
        $this->assertSame(1, $lines[0][1]);
        $this->assertSame(3, $lines[1][1]);
    }

    public function testByteOffsetTracking(): void
    {
        $input = "AB:1\r\nCD:2\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertSame(0, $lines[0][2]);
        $this->assertSame(6, $lines[1][2]);
    }

    public function testEmptyInput(): void
    {
        $lines = iterator_to_array($this->unfolder->unfold(''));
        $this->assertCount(0, $lines);
    }

    public function testMixedLineEndings(): void
    {
        $input = "A:1\r\nB:2\nC:3\rD:4\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(4, $lines);
        $this->assertSame('A:1', $lines[0][0]);
        $this->assertSame('B:2', $lines[1][0]);
        $this->assertSame('C:3', $lines[2][0]);
        $this->assertSame('D:4', $lines[3][0]);
    }

    public function testNoTrailingNewline(): void
    {
        $input = "VERSION:2.0";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('VERSION:2.0', $lines[0][0]);
    }

    public function testQpSoftBreak(): void
    {
        $input = "NOTE;ENCODING=QUOTED-PRINTABLE:First line=\r\nSecond line\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('NOTE;ENCODING=QUOTED-PRINTABLE:First lineSecond line', $lines[0][0]);
    }

    public function testQpMultipleSoftBreaks(): void
    {
        $input = "NOTE;ENCODING=QUOTED-PRINTABLE:A=\r\nB=\r\nC\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        $this->assertCount(1, $lines);
        $this->assertSame('NOTE;ENCODING=QUOTED-PRINTABLE:ABC', $lines[0][0]);
    }

    public function testEqualsSignNotQpWithoutEncoding(): void
    {
        // Line ends with = but no QUOTED-PRINTABLE in params — not a soft break
        $input = "SUMMARY:2+2=\r\n4\r\n";
        $lines = iterator_to_array($this->unfolder->unfold($input));

        // Not treated as QP soft break, but "4" doesn't start with SP/TAB
        // so these are two separate lines
        $this->assertCount(2, $lines);
        $this->assertSame('SUMMARY:2+2=', $lines[0][0]);
        $this->assertSame('4', $lines[1][0]);
    }

    #[DataProvider('foldedDocumentProvider')]
    public function testFoldedDocument(string $input, array $expectedLines): void
    {
        $lines = iterator_to_array($this->unfolder->unfold($input));
        $logicalLines = array_column($lines, 0);

        $this->assertSame($expectedLines, $logicalLines);
    }

    public static function foldedDocumentProvider(): array
    {
        return [
            'minimal vcalendar' => [
                "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nEND:VCALENDAR\r\n",
                ['BEGIN:VCALENDAR', 'VERSION:2.0', 'END:VCALENDAR'],
            ],
            'folded description' => [
                "BEGIN:VEVENT\r\nDESCRIPTION:This is a lo\r\n ng description\r\n  that spans multiple\r\n \tlines\r\nEND:VEVENT\r\n",
                ['BEGIN:VEVENT', "DESCRIPTION:This is a long description that spans multiple\tlines", 'END:VEVENT'],
            ],
        ];
    }
}
