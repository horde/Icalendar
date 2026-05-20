<?php

declare(strict_types=1);

namespace Horde\Icalendar\Test\Unit\Parser;

use Generator;
use Horde\Icalendar\Parser\ContentLine;
use Horde\Icalendar\Parser\ContentLineParser;
use Horde\Icalendar\Parser\ParseError;
use Horde\Icalendar\Parser\ParseException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentLineParser::class)]
class ContentLineParserTest extends TestCase
{
    private ContentLineParser $parser;

    protected function setUp(): void
    {
        $this->parser = new ContentLineParser();
    }

    // =========================================================================
    // Basic parsing
    // =========================================================================

    public function testSimpleLine(): void
    {
        $cl = $this->parser->parseLine('VERSION:2.0');

        $this->assertNull($cl->group);
        $this->assertSame('VERSION', $cl->name);
        $this->assertSame([], $cl->parameters);
        $this->assertSame('2.0', $cl->value);
        $this->assertFalse($cl->hasErrors());
    }

    public function testEmptyValue(): void
    {
        $cl = $this->parser->parseLine('PRODID:');

        $this->assertSame('PRODID', $cl->name);
        $this->assertSame('', $cl->value);
    }

    public function testValueContainingColon(): void
    {
        $cl = $this->parser->parseLine('SUMMARY:Meeting at 10:30');

        $this->assertSame('SUMMARY', $cl->name);
        $this->assertSame('Meeting at 10:30', $cl->value);
    }

    public function testCaseNormalization(): void
    {
        $cl = $this->parser->parseLine('dtstart;tzid=America/New_York:20260520T090000');

        $this->assertSame('DTSTART', $cl->name);
        $this->assertTrue($cl->hasParameter('TZID'));
    }

    // =========================================================================
    // Parameters
    // =========================================================================

    public function testSingleParam(): void
    {
        $cl = $this->parser->parseLine('DTSTART;VALUE=DATE:20260520');

        $this->assertSame('DTSTART', $cl->name);
        $this->assertTrue($cl->hasParameter('VALUE'));
        $this->assertSame('DATE', $cl->getParameter('VALUE')->getValue());
        $this->assertSame('20260520', $cl->value);
    }

    public function testMultipleParams(): void
    {
        $cl = $this->parser->parseLine('DTSTART;TZID=America/New_York;VALUE=DATE-TIME:20260520T090000');

        $this->assertSame('DTSTART', $cl->name);
        $this->assertSame('America/New_York', $cl->getParameter('TZID')->getValue());
        $this->assertSame('DATE-TIME', $cl->getParameter('VALUE')->getValue());
        $this->assertSame('20260520T090000', $cl->value);
    }

    public function testQuotedParamValue(): void
    {
        $cl = $this->parser->parseLine('ORGANIZER;CN="Klä,rchen; Mül:ler":mailto:test@example.com');

        $this->assertSame('ORGANIZER', $cl->name);
        $this->assertSame('Klä,rchen; Mül:ler', $cl->getParameter('CN')->getValue());
        $this->assertSame('mailto:test@example.com', $cl->value);
    }

    public function testMultiValueParam(): void
    {
        $cl = $this->parser->parseLine('TEL;TYPE=WORK,VOICE:+1234567890');

        $this->assertSame('TEL', $cl->name);
        $param = $cl->getParameter('TYPE');
        $this->assertTrue($param->isMultiValue());
        $this->assertSame(['WORK', 'VOICE'], $param->values);
    }

    public function testDuplicateParamsMerged(): void
    {
        $cl = $this->parser->parseLine('TEL;TYPE=WORK;TYPE=VOICE:+1234567890');

        $param = $cl->getParameter('TYPE');
        $this->assertSame(['WORK', 'VOICE'], $param->values);
    }

    public function testNakedParam(): void
    {
        // vCard 2.1 style: param without =value
        $cl = $this->parser->parseLine('TEL;HOME:+1234567890');

        $this->assertTrue($cl->hasParameter('HOME'));
        $this->assertSame('', $cl->getParameter('HOME')->getValue());
    }

    public function testMultipleNakedParams(): void
    {
        $cl = $this->parser->parseLine('TEL;HOME;VOICE:+1234567890');

        $this->assertTrue($cl->hasParameter('HOME'));
        $this->assertTrue($cl->hasParameter('VOICE'));
    }

    // =========================================================================
    // Group prefix
    // =========================================================================

    public function testGroupPrefix(): void
    {
        $cl = $this->parser->parseLine('item1.TEL:+1234567890');

        $this->assertSame('item1', $cl->group);
        $this->assertSame('TEL', $cl->name);
        $this->assertSame('+1234567890', $cl->value);
    }

    public function testGroupWithParams(): void
    {
        $cl = $this->parser->parseLine('item1.X-ABLABEL;VALUE=TEXT:Home');

        $this->assertSame('item1', $cl->group);
        $this->assertSame('X-ABLABEL', $cl->name);
        $this->assertSame('TEXT', $cl->getParameter('VALUE')->getValue());
        $this->assertSame('Home', $cl->value);
    }

    // =========================================================================
    // X-names
    // =========================================================================

    public function testXNameProperty(): void
    {
        $cl = $this->parser->parseLine('X-WR-CALNAME:My Calendar');

        $this->assertSame('X-WR-CALNAME', $cl->name);
        $this->assertSame('My Calendar', $cl->value);
    }

    public function testXParamName(): void
    {
        $cl = $this->parser->parseLine('ATTENDEE;X-NUM-GUESTS=3:mailto:test@example.com');

        $this->assertSame('3', $cl->getParameter('X-NUM-GUESTS')->getValue());
    }

    // =========================================================================
    // Error handling (lenient mode)
    // =========================================================================

    public function testMissingColonLenient(): void
    {
        $cl = $this->parser->parseLine('INVALID-LINE-NO-COLON');

        $this->assertTrue($cl->hasErrors());
        $this->assertContains(ParseError::MissingColon, $cl->errors);
    }

    public function testMissingColonStrict(): void
    {
        $strictParser = new ContentLineParser(strict: true);

        $this->expectException(ParseException::class);
        $strictParser->parseLine('NO-COLON-HERE');
    }

    public function testInvalidNameStrict(): void
    {
        $strictParser = new ContentLineParser(strict: true);

        $this->expectException(ParseException::class);
        $strictParser->parseLine('INVALID NAME:value');
    }

    public function testUnterminatedQuotedString(): void
    {
        $cl = $this->parser->parseLine('ORGANIZER;CN="Unclosed:mailto:test@example.com');

        $this->assertTrue($cl->hasErrors());
        $this->assertContains(ParseError::UnterminatedQuotedString, $cl->errors);
    }

    // =========================================================================
    // Full document parsing
    // =========================================================================

    public function testFullDocument(): void
    {
        $input = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            . "BEGIN:VEVENT\r\nSUMMARY:Meeting\r\nDTSTART;VALUE=DATE:20260520\r\n"
            . "END:VEVENT\r\nEND:VCALENDAR\r\n";

        $lines = iterator_to_array($this->parser->parse($input));

        $this->assertCount(8, $lines);
        $this->assertSame('BEGIN', $lines[0]->name);
        $this->assertSame('VCALENDAR', $lines[0]->value);
        $this->assertSame('VERSION', $lines[1]->name);
        $this->assertSame('SUMMARY', $lines[4]->name);
        $this->assertSame('Meeting', $lines[4]->value);
        $this->assertSame('END', $lines[7]->name);
    }

    public function testFoldedDocumentParsedCorrectly(): void
    {
        $input = "BEGIN:VEVENT\r\nDESCRIPTION:This is a lo\r\n ng description\r\nEND:VEVENT\r\n";

        $lines = iterator_to_array($this->parser->parse($input));

        $this->assertCount(3, $lines);
        $this->assertSame('DESCRIPTION', $lines[1]->name);
        $this->assertSame('This is a long description', $lines[1]->value);
    }

    public function testParseReturnsGenerator(): void
    {
        $result = $this->parser->parse("VERSION:2.0\r\n");

        $this->assertInstanceOf(Generator::class, $result);
    }

    public function testLineNumbersInDocument(): void
    {
        $input = "A:1\r\nB:2\r\nC:3\r\n";
        $lines = iterator_to_array($this->parser->parse($input));

        $this->assertSame(1, $lines[0]->lineNumber);
        $this->assertSame(2, $lines[1]->lineNumber);
        $this->assertSame(3, $lines[2]->lineNumber);
    }

    public function testLineNumbersWithFolding(): void
    {
        $input = "A:long\r\n value\r\nB:short\r\n";
        $lines = iterator_to_array($this->parser->parse($input));

        $this->assertCount(2, $lines);
        $this->assertSame(1, $lines[0]->lineNumber);
        $this->assertSame(3, $lines[1]->lineNumber);
    }

    // =========================================================================
    // Edge cases
    // =========================================================================

    public function testBase64ValuePassthrough(): void
    {
        $b64 = base64_encode(random_bytes(100));
        $cl = $this->parser->parseLine('PHOTO;ENCODING=b;TYPE=JPEG:' . $b64);

        $this->assertSame($b64, $cl->value);
        $this->assertSame('b', $cl->getParameter('ENCODING')->getValue());
    }

    public function testVeryLongValue(): void
    {
        $value = str_repeat('A', 10000);
        $cl = $this->parser->parseLine('X-LONG:' . $value);

        $this->assertSame($value, $cl->value);
    }

    public function testParamValueWithSlash(): void
    {
        $cl = $this->parser->parseLine('DTSTART;TZID=America/New_York:20260520T090000');

        $this->assertSame('America/New_York', $cl->getParameter('TZID')->getValue());
    }

    public function testQuotedParamWithQuotedColonAndSemicolon(): void
    {
        $cl = $this->parser->parseLine('X-PROP;X-META="val;ue:one":content');

        $this->assertSame('val;ue:one', $cl->getParameter('X-META')->getValue());
        $this->assertSame('content', $cl->value);
    }

    public function testEmptyDocument(): void
    {
        $lines = iterator_to_array($this->parser->parse(''));
        $this->assertCount(0, $lines);
    }

    #[DataProvider('realWorldLinesProvider')]
    public function testRealWorldLines(string $line, string $expectedName, string $expectedValue): void
    {
        $cl = $this->parser->parseLine($line);

        $this->assertSame($expectedName, $cl->name);
        $this->assertSame($expectedValue, $cl->value);
        $this->assertFalse($cl->hasErrors());
    }

    public static function realWorldLinesProvider(): array
    {
        return [
            'begin vcalendar' => ['BEGIN:VCALENDAR', 'BEGIN', 'VCALENDAR'],
            'end vcalendar' => ['END:VCALENDAR', 'END', 'VCALENDAR'],
            'version' => ['VERSION:2.0', 'VERSION', '2.0'],
            'prodid' => ['PRODID:-//Google Inc//Google Calendar 70.9054//EN', 'PRODID', '-//Google Inc//Google Calendar 70.9054//EN'],
            'calscale' => ['CALSCALE:GREGORIAN', 'CALSCALE', 'GREGORIAN'],
            'method' => ['METHOD:REQUEST', 'METHOD', 'REQUEST'],
            'uid' => ['UID:abc123@example.com', 'UID', 'abc123@example.com'],
            'url' => ['URL:https://example.com/event?id=123&type=cal', 'URL', 'https://example.com/event?id=123&type=cal'],
        ];
    }
}
