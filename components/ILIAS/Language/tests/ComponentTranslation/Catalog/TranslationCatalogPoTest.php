<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\ComponentTranslation\Catalog;

use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\Catalog\TranslationEntry;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * TranslationCatalog's `.po` side (fromPoString()/fromPoFile()/toPoString()), i.e. what the adapter
 * guarantees on top of gettext/gettext's StrictPoLoader and PoGenerator. The central property is
 * the round trip: the overlay's bookkeeping lives in `.po` comments, so everything written must be
 * read back exactly - and everything broken must surface as RuntimeException, the only exception
 * type the callers (MigratedLanguageFileSync, InstalledLanguageDatabaseRepository) catch.
 */
class TranslationCatalogPoTest extends TestCase
{
    private ?string $directory = null;

    protected function tearDown(): void
    {
        if ($this->directory !== null) {
            \MigratedPoFixture::removeDirectory($this->directory);
        }
    }

    private function temporaryDirectory(): string
    {
        if ($this->directory === null) {
            $this->directory = sys_get_temp_dir() . '/ilias_translation_catalog_' . bin2hex(random_bytes(4));
            mkdir($this->directory, 0775);
        }

        return $this->directory;
    }

    private static function roundTrip(TranslationCatalog $catalog): TranslationCatalog
    {
        return TranslationCatalog::fromPoString($catalog->toPoString());
    }

    private static function singleEntryCatalog(TranslationEntry $entry): TranslationCatalog
    {
        $catalog = new TranslationCatalog();
        $catalog->add($entry);

        return $catalog;
    }

    private static function entry(?string $context, string $id, string $translation): TranslationEntry
    {
        $entry = new TranslationEntry($context, $id);
        $entry->translate($translation);

        return $entry;
    }

    #[DataProvider('values')]
    public function testValuesRoundTripExactly(string $value): void
    {
        $read = self::roundTrip(self::singleEntryCatalog(self::entry('mod', 'id', $value)))->find('mod', 'id');

        $this->assertNotNull($read);
        $this->assertSame($value, $read->getTranslation());
    }

    public static function values(): array
    {
        return [
            'empty' => [''],
            'zero' => ['0'],
            'zero with a line break' => ["0\n"],
            'double zero' => ['00'],
            'plain' => ['Hallo'],
            'double quote' => ['Klick "hier"'],
            'backslash' => ['C:\\temp\\'],
            'backslash before quote' => ['a\\"b'],
            'only a backslash' => ['\\'],
            'tab and carriage return' => ["a\tb\rc"],
            'multiline' => ["Zeile 1\nZeile 2\nZeile 3"],
            'trailing newline' => ["Zeile\n"],
            'only newlines' => ["\n\n"],
            'leading and trailing spaces' => ['  Hallo  '],
            'unicode' => ['Ünïcödé – 日本語 🙂'],
            'placeholder and html' => ['<a href="%1$s">%s</a>'],
            'literal escape sequence text' => ['\\n is not a newline'],
            'hash at start' => ['# not a comment'],
            'msgid-like text' => ["msgid \"x\"\nmsgstr \"y\""],
        ];
    }

    /**
     * PoGenerator treats a falsy translation as absent - without the adapter's workaround "0" (it
     * occurs in the legacy ilias_fr.lang/ilias_pl.lang) would be written as an empty msgstr.
     */
    public function testTheValueZeroIsWrittenAsExactlyZero(): void
    {
        $po = self::singleEntryCatalog(self::entry('mod', 'zero', '0'))->toPoString();

        $this->assertStringContainsString("msgctxt \"mod\"\nmsgid \"zero\"\nmsgstr \"0\"\n", $po);
        $this->assertStringNotContainsString("\0", $po);
    }

    /**
     * The workaround works on a copy: generating must not change the catalog it was called on (a
     * leaked "0\0" would, e.g., make the next toPoString()/toMoString() or a value comparison differ).
     */
    public function testGeneratingDoesNotChangeTheCatalog(): void
    {
        $entry = self::entry('mod', 'zero', '0');
        $catalog = self::singleEntryCatalog($entry);

        $first_po = $catalog->toPoString();
        $first_mo = $catalog->toMoString();

        $this->assertSame('0', $entry->getTranslation());
        $this->assertSame('0', $catalog->find('mod', 'zero')?->getTranslation());
        $this->assertSame($first_po, $catalog->toPoString());
        $this->assertSame($first_mo, $catalog->toMoString());
    }

    public function testIdentifierAndContextRoundTripIncludingSpecialCharacters(): void
    {
        $catalog = self::singleEntryCatalog(self::entry("ctx \"q\"", "id\\with\nnewline", 'v'));

        $this->assertSame('v', self::roundTrip($catalog)->find("ctx \"q\"", "id\\with\nnewline")?->getTranslation());
    }

    /**
     * Boundary of the "falsy" workaround: an identifier "0" is not special.
     */
    public function testAnIdentifierZeroRoundTrips(): void
    {
        $catalog = self::singleEntryCatalog(self::entry('mod', '0', 'null'));

        $this->assertSame('null', self::roundTrip($catalog)->find('mod', '0')?->getTranslation());
    }

    /**
     * The library cannot represent a context or msgid_plural of "" or "0" (PoGenerator/MoGenerator
     * skip falsy ones, which would silently merge the message into the context-less one). Generating
     * such a catalog throws instead of writing a different catalog.
     */
    #[DataProvider('unrepresentableMessages')]
    public function testAnUnrepresentableContextOrPluralIdThrowsWhenGenerating(string $po, bool $mo): void
    {
        $catalog = TranslationCatalog::fromPoString($po);

        $this->expectException(RuntimeException::class);
        $mo ? $catalog->toMoString() : $catalog->toPoString();
    }

    public static function unrepresentableMessages(): array
    {
        $cases = [
            'empty context' => "msgctxt \"\"\nmsgid \"a\"\nmsgstr \"b\"\n",
            'context zero' => "msgctxt \"0\"\nmsgid \"a\"\nmsgstr \"b\"\n",
            'empty plural id' => "msgid \"a\"\nmsgid_plural \"\"\nmsgstr[0] \"b\"\nmsgstr[1] \"c\"\n",
            'plural id zero' => "msgid \"a\"\nmsgid_plural \"0\"\nmsgstr[0] \"b\"\nmsgstr[1] \"c\"\n",
        ];
        $data = [];
        foreach ($cases as $name => $po) {
            $data[$name . ', .po'] = [$po, false];
            $data[$name . ', .mo'] = [$po, true];
        }

        return $data;
    }

    public function testAnEntryCreatedWithAContextZeroThrowsWhenGenerating(): void
    {
        $catalog = self::singleEntryCatalog(self::entry('0', 'a', 'b'));

        $this->expectException(RuntimeException::class);
        $catalog->toPoString();
    }

    /**
     * "original: ..." comments are compared with the value byte for byte - surrounding whitespace
     * must survive; the loader must strip exactly the one space after the comment marker.
     */
    public function testCommentsAndFlagsRoundTripWithoutTrimming(): void
    {
        $entry = self::entry('mod', 'id', 'v');
        $entry->addTranslatorComment('original:  Hallo  ');
        $entry->addTranslatorComment('');
        $entry->addTranslatorComment(' leading space');
        $entry->addTranslatorComment('trailing space ');
        $entry->addTranslatorComment('.looks like an extracted marker');
        $entry->addExtractedComment('  extracted  ');
        $entry->addFlag('php-format');
        $entry->addFlag('fuzzy');

        $read = self::roundTrip(self::singleEntryCatalog($entry))->find('mod', 'id');

        $this->assertNotNull($read);
        $this->assertSame(
            ['original:  Hallo  ', '', ' leading space', 'trailing space ', '.looks like an extracted marker'],
            $read->getTranslatorComments()
        );
        $this->assertSame(['  extracted  '], $read->getExtractedComments());
        $this->assertSame(['fuzzy', 'php-format'], $read->getFlags());
        $this->assertTrue($read->hasFlag('fuzzy'));
    }

    /**
     * Layout of comments and flags as the library writes them (characterization): an empty comment
     * is "# " (the old writer wrote "#"), flags are sorted and joined by "," without a space.
     */
    public function testTheCommentAndFlagLayout(): void
    {
        $entry = self::entry('mod', 'id', 'v');
        $entry->addTranslatorComment('');
        $entry->addTranslatorComment('  note ');
        $entry->addExtractedComment('ext');
        $entry->addFlag('php-format');
        $entry->addFlag('fuzzy');

        $this->assertStringContainsString(
            "# \n#   note \n#. ext\n#, fuzzy,php-format\nmsgctxt \"mod\"\nmsgid \"id\"\nmsgstr \"v\"\n",
            self::singleEntryCatalog($entry)->toPoString()
        );
    }

    public function testCommentsWrittenWithoutASpaceAfterTheMarkerAreReadAsIs(): void
    {
        $entry = TranslationCatalog::fromPoString("#note\n#.ext\n#\nmsgid \"id\"\nmsgstr \"v\"\n")->find(null, 'id');

        $this->assertNotNull($entry);
        $this->assertSame(['note', ''], $entry->getTranslatorComments());
        $this->assertSame(['ext'], $entry->getExtractedComments());
    }

    /**
     * A comment is a single `.po` line: a line break inside it would end the comment and corrupt
     * the file, so it is replaced by a space when added.
     */
    public function testALineBreakInACommentIsReplacedByASpace(): void
    {
        $entry = self::entry('mod', 'id', 'v');
        $entry->addTranslatorComment("eins\nzwei\r\ndrei\rvier");
        $entry->addExtractedComment("a\nb");

        $read = self::roundTrip(self::singleEntryCatalog($entry))->find('mod', 'id');

        $this->assertSame(['eins zwei drei vier'], $entry->getTranslatorComments());
        $this->assertNotNull($read);
        $this->assertSame(['eins zwei drei vier'], $read->getTranslatorComments());
        $this->assertSame(['a b'], $read->getExtractedComments());
    }

    /**
     * ILIAS itself has no plural API any more, but a loaded plural message is carried along and
     * written back unchanged - also after its singular translation or comments were changed.
     */
    public function testALoadedPluralMessageRoundTripsByteIdentically(): void
    {
        $po = "msgid \"\"\nmsgstr \"\"\n\"Plural-Forms: nplurals=3; plural=(n==1 ? 0 : n%10>=2 && n%10<=4 ? 1 : 2);\\n\"\n\n"
            . "msgctxt \"mod\"\nmsgid \"item\"\nmsgid_plural \"items\"\nmsgstr[0] \"%d Eintrag\"\nmsgstr[1] \"%d Einträge\"\n"
            . "msgstr[2] \"%d Einträgen\"\n";

        $catalog = TranslationCatalog::fromPoString($po);

        $this->assertSame($po, $catalog->toPoString());
        $entry = $catalog->find('mod', 'item');
        $this->assertNotNull($entry);
        $this->assertSame('%d Eintrag', $entry->getTranslation());

        $entry->translate('%d Stück');
        $entry->addTranslatorComment('note');

        $this->assertSame(
            str_replace("msgctxt \"mod\"\nmsgid \"item\"", "# note\nmsgctxt \"mod\"\nmsgid \"item\"", str_replace('"%d Eintrag"', '"%d Stück"', $po)),
            $catalog->toPoString()
        );
    }

    /**
     * Documented deviation from the GNU tools: the number of msgstr[n] written follows the
     * Plural-Forms header - missing forms are written empty, surplus ones are dropped.
     */
    #[DataProvider('pluralFormCounts')]
    public function testTheNumberOfWrittenPluralFormsFollowsThePluralFormsHeader(string $header, string $forms, string $expected): void
    {
        $po = "msgid \"\"\nmsgstr \"\"\n" . $header . "\n"
            . "msgid \"item\"\nmsgid_plural \"items\"\n" . $forms;

        $this->assertSame(
            "msgid \"\"\nmsgstr \"\"\n" . $header . "\nmsgid \"item\"\nmsgid_plural \"items\"\n" . $expected,
            TranslationCatalog::fromPoString($po)->toPoString()
        );
    }

    public static function pluralFormCounts(): array
    {
        $two = "msgstr[0] \"a\"\nmsgstr[1] \"b\"\n";
        $three = $two . "msgstr[2] \"c\"\n";

        return [
            'as many as declared' => ["\"Plural-Forms: nplurals=3; plural=(n != 1);\\n\"\n", $three, $three],
            'fewer than declared' => ["\"Plural-Forms: nplurals=3; plural=(n != 1);\\n\"\n", $two, $two . "msgstr[2] \"\"\n"],
            'more than declared' => ["\"Plural-Forms: nplurals=2; plural=(n != 1);\\n\"\n", $three, $two],
            'no header' => ['', $two, $two],
        ];
    }

    public function testASingularTranslationZeroOfAPluralMessageIsWrittenToThePo(): void
    {
        $po = "msgid \"\"\nmsgstr \"\"\n\nmsgid \"a\"\nmsgid_plural \"as\"\nmsgstr[0] \"0\"\nmsgstr[1] \"0\"\n";

        $this->assertSame($po, TranslationCatalog::fromPoString($po)->toPoString());
    }

    public function testHeadersAreSortedByNameRegardlessOfTheOrderTheyWereSetIn(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('X-Domain', 'tos');
        $catalog->setHeader('Plural-Forms', 'nplurals=2; plural=(n != 1);');
        $catalog->setHeader('Content-Type', 'text/plain; charset=UTF-8');
        $catalog->setHeader('Language', 'de');

        $expected = [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Language' => 'de',
            'Plural-Forms' => 'nplurals=2; plural=(n != 1);',
            'X-Domain' => 'tos',
        ];
        $this->assertSame($expected, $catalog->getHeaders());
        $this->assertSame(
            "msgid \"\"\nmsgstr \"\"\n\"Content-Type: text/plain; charset=UTF-8\\n\"\n\"Language: de\\n\"\n"
            . "\"Plural-Forms: nplurals=2; plural=(n != 1);\\n\"\n\"X-Domain: tos\\n\"\n",
            $catalog->toPoString()
        );
        $read = self::roundTrip($catalog);
        $this->assertSame($expected, $read->getHeaders());
        $this->assertSame([], $read->getEntries(), 'the header is not an entry');
    }

    public function testSetHeaderReplacesAValueTrimsItAndGetHeaderReturnsNullForAMissingOne(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Language', 'fr');
        $catalog->setHeader('Language', " \t de ");

        $this->assertSame('de', $catalog->getHeader('Language'));
        $this->assertSame(['Language' => 'de'], $catalog->getHeaders());
        $this->assertNull($catalog->getHeader('X-Domain'));
    }

    /**
     * The `.po` header block is split at line breaks when read - a value containing one could not
     * be read back unchanged, so it is refused (and the header keeps its previous value).
     */
    #[DataProvider('headerValuesWithALineBreak')]
    public function testSetHeaderRefusesAValueWithALineBreak(string $value): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('X-Note', 'bisher');

        try {
            $catalog->setHeader('X-Note', $value);
            $this->fail('Expected an InvalidArgumentException');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('X-Note', $e->getMessage());
        }

        $this->assertSame(['X-Note' => 'bisher'], $catalog->getHeaders());
    }

    public static function headerValuesWithALineBreak(): array
    {
        return [
            'LF inside' => ["a\nb"],
            'CR inside' => ["a\rb"],
            'CRLF inside' => ["a\r\nb"],
            'trailing LF' => ["a\n"],
            'leading CR' => ["\ra"],
            'only CRLF' => ["\r\n"],
        ];
    }

    /**
     * PoGenerator writes header values verbatim - a quote or backslash would end the string early
     * and corrupt the whole file.
     */
    public function testHeaderValuesWithQuotesAndBackslashesAreEscaped(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('X-Note', 'a "quoted" C:\\pfad\\');
        $catalog->setHeader('X-Zero', '0');

        $po = $catalog->toPoString();

        $this->assertStringContainsString("\"X-Note: a \\\"quoted\\\" C:\\\\pfad\\\\\\n\"\n", $po);
        $this->assertSame(
            ['X-Note' => 'a "quoted" C:\\pfad\\', 'X-Zero' => '0'],
            TranslationCatalog::fromPoString($po)->getHeaders()
        );
    }

    /**
     * An escaped CR that stays inside a header value after loading could be read but never written
     * back (setHeader() refuses it) - such a file counts as broken.
     */
    #[DataProvider('headersWithALineBreakAfterLoading')]
    public function testAHeaderValueKeepingALineBreakAfterLoadingIsRejected(string $po): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The value of header "X-A" contains a line break.');
        TranslationCatalog::fromPoString($po);
    }

    public static function headersWithALineBreakAfterLoading(): array
    {
        return [
            'CR inside' => ["msgid \"\"\nmsgstr \"\"\n\"X-A: a\\rb\\n\"\n"],
            'CR inside, last header without a trailing \\n' => ["msgid \"\"\nmsgstr \"\"\n\"X-A: a\\rb\"\n"],
            'CR inside, after a valid header' => [
                "msgid \"\"\nmsgstr \"\"\n\"Language: de\\n\"\n\"X-A: a\\r\\rb\\n\"\n\nmsgid \"id\"\nmsgstr \"v\"\n",
            ],
        ];
    }

    public function testFromPoFileNamesTheFileOfAHeaderWithALineBreak(): void
    {
        $file = $this->temporaryDirectory() . '/header_de.po';
        file_put_contents($file, "msgid \"\"\nmsgstr \"\"\n\"X-A: a\\rb\\n\"\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Invalid PO file ".*header_de\.po": The value of header "X-A" contains a line break\.$/');
        TranslationCatalog::fromPoFile($file);
    }

    /**
     * Boundary: line breaks the loader removes itself leave a valid header - a CR at either end is
     * trimmed, and an escaped LF inside a value cannot survive loading at all, because the header
     * block is split at LF and a line without "Name:" is appended to the previous header
     * (characterization of gettext/gettext: "a\nb" is read as "ab").
     */
    #[DataProvider('headersLosingTheirLineBreakWhenLoaded')]
    public function testAHeaderWhoseLineBreakIsRemovedWhenLoadedStaysValid(string $header_line, string $expected): void
    {
        $catalog = TranslationCatalog::fromPoString("msgid \"\"\r\nmsgstr \"\"\r\n" . $header_line . "\r\n");

        $this->assertSame(['X-A' => $expected], $catalog->getHeaders());
        $this->assertSame("msgid \"\"\nmsgstr \"\"\n\"X-A: " . $expected . "\\n\"\n", $catalog->toPoString());
    }

    public static function headersLosingTheirLineBreakWhenLoaded(): array
    {
        return [
            'trailing CR' => ['"X-A: tail\\r\\n"', 'tail'],
            'leading CR' => ['"X-A: \\rhead\\n"', 'head'],
            'escaped LF inside' => ['"X-A: a\\nb\\n"', 'ab'],
            'no line break' => ['"X-A: v\\n"', 'v'],
        ];
    }

    public function testTheWriterLayoutMatchesTheShippedFiles(): void
    {
        $catalog = new TranslationCatalog();
        $catalog->setHeader('Language', 'de');
        $catalog->add(self::entry('tos', 'tos_agreement', "Zeile 1\nZeile 2"));
        $catalog->add(self::entry('tos', 'tos_empty', ''));

        $this->assertSame(
            "msgid \"\"\nmsgstr \"\"\n\"Language: de\\n\"\n\n"
            . "msgctxt \"tos\"\nmsgid \"tos_agreement\"\nmsgstr \"\"\n\"Zeile 1\\n\"\n\"Zeile 2\"\n\n"
            . "msgctxt \"tos\"\nmsgid \"tos_empty\"\nmsgstr \"\"\n",
            $catalog->toPoString()
        );
    }

    public function testReadsMultilineStringsCrLfLineEndingsAndABom(): void
    {
        $po = "\xEF\xBB\xBFmsgid \"\"\r\nmsgstr \"\"\r\n\"Language: de\\n\"\r\n\r\n"
            . "msgctxt \"mod\"\r\nmsgid \"id\"\r\nmsgstr \"\"\r\n\"eins \"\r\n\"zwei\"\r\n";

        $catalog = TranslationCatalog::fromPoString($po);

        $this->assertSame('de', $catalog->getHeader('Language'));
        $this->assertSame('eins zwei', $catalog->find('mod', 'id')?->getTranslation());
        $this->assertStringStartsWith("msgid \"\"\nmsgstr \"\"\n", $catalog->toPoString(), 'no BOM, LF only');
    }

    /**
     * A BOM that is not at the very start is not skipped - it is data, i.e. a syntax error here.
     */
    public function testOnlyALeadingBomIsSkipped(): void
    {
        $this->expectException(RuntimeException::class);
        TranslationCatalog::fromPoString("msgid \"a\"\nmsgstr \"b\"\n\xEF\xBB\xBFmsgid \"c\"\nmsgstr \"d\"\n");
    }

    /**
     * Every value is used as UTF-8 as it is - no charset conversion takes place anywhere. Content
     * that is not valid UTF-8 at all (e.g. Latin-1 encoded bytes carrying a translation) must
     * therefore be rejected outright, before the underlying loader even attempts to parse it.
     */
    public function testRejectsContentThatIsNotValidUtf8(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not valid UTF-8');
        // "\xE4" (a bare Latin-1 "ä") is not a valid UTF-8 byte sequence on its own.
        TranslationCatalog::fromPoString("msgid \"id\"\nmsgstr \"caf\xE4\"\n");
    }

    /**
     * A `.po` can be valid UTF-8 while its "Content-Type" header declares another charset (e.g. copied from a
     * Latin-1-era file). Values are read and written as UTF-8 verbatim, so honoring that declaration would mislabel the
     * content: any charset other than UTF-8 is rejected.
     */
    public function testRejectsAContentTypeHeaderDeclaringAnyCharsetOtherThanUtf8(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported charset "ISO-8859-1"');
        TranslationCatalog::fromPoString(
            "msgid \"\"\nmsgstr \"\"\n\"Content-Type: text/plain; charset=ISO-8859-1\\n\"\n\n"
            . "msgid \"id\"\nmsgstr \"value\"\n"
        );
    }

    public function testDecodesControlEscapes(): void
    {
        $catalog = TranslationCatalog::fromPoString("msgid \"id\"\nmsgstr \"\\101\\x42\\a\\b\\f\\v\\t\\r\\n\"\n");

        $this->assertSame("AB\x07\x08\x0c\x0b\t\r\n", $catalog->find(null, 'id')?->getTranslation());
    }

    /**
     * Obsolete messages are dropped on load - and therefore never written back.
     */
    public function testDropsObsoleteMessages(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgid \"id\"\nmsgstr \"v\"\n\n#~ msgctxt \"mod\"\n#~ msgid \"gone\"\n#~ msgstr \"weg\"\n"
        );

        $this->assertCount(1, $catalog->getEntries());
        $this->assertNull($catalog->find('mod', 'gone'));
        $this->assertNull($catalog->find(null, 'gone'));
        $this->assertStringNotContainsString('gone', $catalog->toPoString());
    }

    /**
     * References and previous-message lines have no API any more, but are carried along untouched.
     */
    public function testReferencesAndPreviousMessageLinesAreWrittenBackAsLoaded(): void
    {
        $po = "msgid \"\"\nmsgstr \"\"\n\n#: file.php:12\n#: other.php\n#, fuzzy\n#| msgid \"old\"\nmsgid \"id\"\nmsgstr \"v\"\n";

        $catalog = TranslationCatalog::fromPoString($po);

        $this->assertSame([], $catalog->find(null, 'id')?->getTranslatorComments());
        $this->assertSame($po, $catalog->toPoString());
    }

    public function testTheSameIdInDifferentContextsIsNoDuplicate(): void
    {
        $catalog = TranslationCatalog::fromPoString(
            "msgctxt \"a\"\nmsgid \"id\"\nmsgstr \"A\"\n\nmsgctxt \"b\"\nmsgid \"id\"\nmsgstr \"B\"\n\nmsgid \"id\"\nmsgstr \"ohne\"\n"
        );

        $this->assertSame('A', $catalog->find('a', 'id')?->getTranslation());
        $this->assertSame('B', $catalog->find('b', 'id')?->getTranslation());
        $this->assertSame('ohne', $catalog->find(null, 'id')?->getTranslation());
        $this->assertSame(['A', 'B', 'ohne'], array_map(
            static fn(TranslationEntry $entry): string => $entry->getTranslation(),
            $catalog->getEntries()
        ));
    }

    /**
     * An empty file or one without a header block is a valid (empty or header-less) catalog.
     */
    #[DataProvider('validButEmptyPo')]
    public function testAnEmptyOrHeaderlessPoIsNoError(string $po, int $expected_entries): void
    {
        $catalog = TranslationCatalog::fromPoString($po);

        $this->assertCount($expected_entries, $catalog->getEntries());
        $this->assertSame([], $catalog->getHeaders());
    }

    public static function validButEmptyPo(): array
    {
        return [
            'empty' => ['', 0],
            'only a BOM' => ["\xEF\xBB\xBF", 0],
            'only blank lines' => ["\n\r\n\n", 0],
            'only a comment' => ["# just a comment\n", 0],
            'no header' => ["msgid \"a\"\nmsgstr \"b\"\n", 1],
            'empty header' => ["msgid \"\"\nmsgstr \"\"\n", 0],
        ];
    }

    /**
     * Everything StrictPoLoader rejects - and all of it as RuntimeException (the library's own
     * exceptions are plain \Exception, which no caller would catch).
     */
    #[DataProvider('brokenPo')]
    public function testThrowsARuntimeExceptionForBrokenContent(string $po): void
    {
        $this->expectException(RuntimeException::class);
        TranslationCatalog::fromPoString($po);
    }

    public static function brokenPo(): array
    {
        $mo_catalog = new TranslationCatalog();
        $mo_catalog->add(self::entry('mod', 'id', 'v'));

        return [
            'unterminated string' => ["msgid \"abc\nmsgstr \"\"\n"],
            'escaped closing quote' => ["msgid \"abc\\\"\nmsgstr \"\"\n"],
            'unquoted value' => ["msgid abc\nmsgstr \"\"\n"],
            'unknown keyword' => ["msgfoo \"abc\"\n"],
            'keyword without value' => ["msgid\n"],
            'continuation without keyword' => ["\"dangling\"\n"],
            'text after the closing quote' => ["msgid \"a\" trailing\nmsgstr \"\"\n"],
            'invalid escape sequence' => ["msgid \"a\\q\"\nmsgstr \"\"\n"],
            'octal escape above 127' => ["msgid \"a\"\nmsgstr \"\\303\\244\"\n"],
            'missing msgstr before the next message' => ["msgid \"a\"\n\nmsgid \"b\"\nmsgstr \"B\"\n"],
            'missing msgstr at the end' => ["msgid \"a\"\nmsgstr \"A\"\n\nmsgid \"b\"\n"],
            'msgstr without msgid' => ["msgstr \"A\"\n"],
            'duplicate message' => ["msgid \"a\"\nmsgstr \"first\"\n\nmsgid \"a\"\nmsgstr \"second\"\n"],
            'duplicate message with context' => ["msgctxt \"m\"\nmsgid \"a\"\nmsgstr \"1\"\n\nmsgctxt \"m\"\nmsgid \"a\"\nmsgstr \"2\"\n"],
            'gap in the plural forms' => ["msgid \"a\"\nmsgid_plural \"as\"\nmsgstr[0] \"x\"\nmsgstr[2] \"z\"\n"],
            'plural forms not starting at 0' => ["msgid \"a\"\nmsgid_plural \"as\"\nmsgstr[1] \"y\"\n"],
            'binary data' => ["\x00\x01\x02\xff\xfe binary"],
            'a .mo file' => [$mo_catalog->toMoString()],
            'truncated inside a string' => ["msgctxt \"mod\"\nmsgid \"id\"\nmsgstr \"Hal"],
            'truncated inside a keyword' => ["msgctxt \"mod\"\nmsgid \"id\"\nmsg"],
            'truncated after msgctxt' => ["msgctxt \"mod\"\n"],
        ];
    }

    public function testTheLineOfTheErrorIsReported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/line 3\b/');
        TranslationCatalog::fromPoString("msgid \"a\"\nmsgstr \"A\"\nbroken\n");
    }

    /**
     * The adapter's temporary error handler must be removed again on success and on failure -
     * otherwise every later warning of the request would be turned into an exception.
     */
    public function testTheErrorHandlerIsRestoredAfterReading(): void
    {
        $handler = static fn(): bool => false;
        set_error_handler($handler);
        try {
            TranslationCatalog::fromPoString("msgid \"a\"\nmsgstr \"b\"\n");
            try {
                TranslationCatalog::fromPoString('broken');
                $this->fail('Expected a RuntimeException');
            } catch (RuntimeException) {
            }

            $current = set_error_handler(null);
            restore_error_handler();
            $this->assertSame($handler, $current);
        } finally {
            restore_error_handler();
        }
    }

    public function testFromPoFileReadsAFile(): void
    {
        $file = $this->temporaryDirectory() . '/mod_de.po';
        file_put_contents($file, "msgid \"\"\nmsgstr \"\"\n\"Language: de\\n\"\n\nmsgctxt \"mod\"\nmsgid \"id\"\nmsgstr \"v\"\n");

        $catalog = TranslationCatalog::fromPoFile($file);

        $this->assertSame('de', $catalog->getHeader('Language'));
        $this->assertSame('v', $catalog->find('mod', 'id')?->getTranslation());
    }

    public function testFromPoFileNamesTheFileOfABrokenPo(): void
    {
        $file = $this->temporaryDirectory() . '/broken_de.po';
        file_put_contents($file, "msgid \"a\"\nmsgstr \"A\"\nbroken\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Invalid PO file ".*broken_de\.po": .*line 3\b/');
        TranslationCatalog::fromPoFile($file);
    }

    public function testFromPoFileThrowsForAMissingFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Could not read PO file/');
        TranslationCatalog::fromPoFile($this->temporaryDirectory() . '/missing.po');
    }

    public function testFromPoFileThrowsForADirectory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Could not read PO file/');
        TranslationCatalog::fromPoFile($this->temporaryDirectory());
    }

    public function testFromPoFileThrowsForAnUnreadableFile(): void
    {
        $file = $this->temporaryDirectory() . '/unreadable.po';
        file_put_contents($file, "msgid \"a\"\nmsgstr \"b\"\n");
        chmod($file, 0000);
        if (is_readable($file)) {
            $this->markTestSkipped('File permissions are not enforced for this user (root).');
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Could not read PO file/');
        TranslationCatalog::fromPoFile($file);
    }

    /**
     * MigratedLanguageFileSync seeds a new overlay from a clone of the shipped catalog and relies on
     * the shipped one keeping its values; a shallow clone would share the messages.
     */
    public function testCloneIsIndependentOfTheOriginal(): void
    {
        $original = self::singleEntryCatalog(self::entry('common', 'save', 'Speichern'));
        $original->setHeader('Language', 'de');
        $original_po = $original->toPoString();

        $clone = clone $original;
        $cloned_entry = $clone->find('common', 'save');
        $cloned_entry->translate('Sichern');
        $cloned_entry->addFlag('fuzzy');
        $cloned_entry->setExtractedComments(['changed']);
        $clone->add($cloned_entry);
        $clone->add(self::entry('common', 'cancel', 'Abbrechen'));
        $clone->setHeader('Language', 'en');

        $this->assertSame($original_po, $original->toPoString());
        $this->assertSame('Speichern', $original->find('common', 'save')->getTranslation());
        $this->assertNull($original->find('common', 'cancel'));
        $this->assertSame('Sichern', $clone->find('common', 'save')->getTranslation());
        $this->assertSame('en', $clone->getHeader('Language'));
    }

    /**
     * @return list<string> the ids in the order the catalog holds them
     */
    private static function idsOf(TranslationCatalog $catalog): array
    {
        return array_map(static fn(TranslationEntry $entry): string => $entry->getId(), $catalog->getEntries());
    }

    private static function catalogOfIds(string ...$ids): TranslationCatalog
    {
        $catalog = new TranslationCatalog();
        foreach ($ids as $id) {
            $catalog->add(self::entry('common', $id, $id));
        }

        return $catalog;
    }

    public function testAddInIdOrderInsertsAtTheFront(): void
    {
        $catalog = self::catalogOfIds('m', 'z');

        $catalog->addInIdOrder(self::entry('common', 'a', 'A'));

        $this->assertSame(['a', 'm', 'z'], self::idsOf($catalog));
    }

    public function testAddInIdOrderInsertsInTheMiddle(): void
    {
        $catalog = self::catalogOfIds('a', 'z');

        $catalog->addInIdOrder(self::entry('common', 'm', 'M'));

        $this->assertSame(['a', 'm', 'z'], self::idsOf($catalog));
    }

    public function testAddInIdOrderInsertsAtTheEnd(): void
    {
        $catalog = self::catalogOfIds('a', 'm');

        $catalog->addInIdOrder(self::entry('common', 'z', 'Z'));

        $this->assertSame(['a', 'm', 'z'], self::idsOf($catalog));
    }

    /**
     * An entry with an id (and context) the catalog already has is replaced in place, like add() -
     * neither removed and re-appended nor duplicated.
     */
    public function testAddInIdOrderReplacesAnExistingEntryInPlace(): void
    {
        $catalog = self::catalogOfIds('a', 'm', 'z');

        $catalog->addInIdOrder(self::entry('common', 'm', 'replaced'));

        $this->assertSame(['a', 'm', 'z'], self::idsOf($catalog));
        $this->assertSame('replaced', $catalog->find('common', 'm')->getTranslation());
    }

    public function testAddInIdOrderOnAnEmptyCatalogAddsTheOnlyEntry(): void
    {
        $catalog = new TranslationCatalog();

        $catalog->addInIdOrder(self::entry('common', 'a', 'A'));

        $this->assertSame(['a'], self::idsOf($catalog));
    }
}
