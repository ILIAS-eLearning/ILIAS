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

/**
 * ilLanguageFile::keepOriginalLines(): kept lines are emitted exactly as read, without whitespace normalisation.
 */
class ilLanguageFileTest extends ilLanguageBaseTestCase
{
    private const string HEADER = "HEADER\n<!-- language file start -->";

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ILIAS_HTTP_PATH' => 'http://localhost', 'ILIAS_VERSION' => 'test'] as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
        $this->setGlobalVariable('lng', $this->createStub(ilLanguage::class));
        $this->setGlobalVariable('ilUser', $this->createStub(ilObjUser::class));
        $this->file = sys_get_temp_dir() . '/ilias_lang_file_test_' . bin2hex(random_bytes(4)) . '.lang';
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }

        parent::tearDown();
    }

    /** @param list<string> $lines */
    private function read(array $lines): ilLanguageFile
    {
        file_put_contents($this->file, self::HEADER . "\n" . implode("\n", $lines) . "\n");
        $language_file = new ilLanguageFile($this->file, 'zz');
        $this->assertTrue($language_file->read(), $language_file->getErrorMessage());

        return $language_file;
    }

    /** @return list<string> */
    private static function builtLines(ilLanguageFile $language_file): array
    {
        return explode("\n", substr($language_file->build(self::HEADER), strlen(self::HEADER) + 1));
    }

    public function testWithoutKeptLinesEveryLineIsNormalisedAsBefore(): void
    {
        $language_file = $this->read(["a#:#x#:#Wert  ", "\tb#:#y#:#Zwei###Kommentar "]);

        $this->assertSame(['a#:#x#:#Wert', 'b#:#y#:#Zwei###Kommentar'], self::builtLines($language_file));
    }

    public function testAKeptLineIsWrittenExactlyAsReadIncludingSurroundingWhitespace(): void
    {
        $language_file = $this->read(["a#:#x#:#Wert  \t", " b#:#y#:#Zwei###Kommentar ", 'c#:#z#:#Drei  ']);

        $language_file->keepOriginalLines(['a#:#x', 'b#:#y']);

        $this->assertSame(
            ["a#:#x#:#Wert  \t", " b#:#y#:#Zwei###Kommentar ", 'c#:#z#:#Drei'],
            self::builtLines($language_file)
        );
    }

    /** Values and comments set after read() do not change a kept line, also one without surrounding whitespace. */
    public function testAKeptLineIgnoresValuesAndCommentsSetAfterReading(): void
    {
        $language_file = $this->read(['a#:#x#:#Wert###Kommentar', 'b#:#y#:#Zwei', 'c#:#z#:#Drei']);
        $language_file->keepOriginalLines(['a#:#x', 'b#:#y']);

        $language_file->setAllValues(['a#:#x' => 'Neu', 'b#:#y' => 'Neu', 'c#:#z' => 'Neu']);
        $language_file->setAllComments(['b#:#y' => 'neuer Kommentar', 'c#:#z' => 'neuer Kommentar']);

        $this->assertSame(
            ['a#:#x#:#Wert###Kommentar', 'b#:#y#:#Zwei', 'c#:#z#:#Neu###neuer Kommentar'],
            self::builtLines($language_file)
        );
    }

    public function testUnknownKeysAreIgnoredAndTheValuesSetDetermineOrderAndPresence(): void
    {
        $language_file = $this->read(['a#:#x#:#Eins ', 'b#:#y#:#Zwei ', 'c#:#z#:#Drei ']);
        $language_file->keepOriginalLines(['a#:#x', 'c#:#z', 'd#:#new']);

        $language_file->setAllValues(['c#:#z' => 'Neu', 'd#:#new' => 'Neu', 'b#:#y' => 'Neu']);

        $this->assertSame(['c#:#z#:#Drei ', 'd#:#new#:#Neu', 'b#:#y#:#Neu'], self::builtLines($language_file));
    }

    public function testReadingAgainForgetsTheKeptLines(): void
    {
        $language_file = $this->read(['a#:#x#:#Eins ']);
        $language_file->keepOriginalLines(['a#:#x']);

        file_put_contents($this->file, self::HEADER . "\na#:#x#:#Zwei  \n");
        $this->assertTrue($language_file->read());

        $this->assertSame(['a#:#x#:#Zwei'], self::builtLines($language_file));
    }

    /** write() keeps the line as read too; with Windows line endings the line break is not part of it. */
    public function testWriteEmitsTheKeptLineAsReadWithoutItsLineBreak(): void
    {
        file_put_contents($this->file, self::HEADER . "\r\na#:#x#:#Eins \r\nb#:#y#:#Zwei \r\n");
        $language_file = new ilLanguageFile($this->file, 'zz');
        $this->assertTrue($language_file->read());
        $language_file->keepOriginalLines(['a#:#x']);

        $language_file->write(self::HEADER);

        $this->assertSame(self::HEADER . "\na#:#x#:#Eins \nb#:#y#:#Zwei", file_get_contents($this->file));
    }
}
