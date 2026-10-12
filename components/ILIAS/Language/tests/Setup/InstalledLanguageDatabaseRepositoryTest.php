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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\Setup\InstalledLanguageDatabaseRepository;
use ILIAS\Language\Setup\FixedInstallationRoot;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

/**
 * Database-backed methods run against an ilDBInterface mock returning controlled rows; filesystem-backed methods
 * use a real temporary installation root, like LanguageInstallationManagerTest.
 */
class InstalledLanguageDatabaseRepositoryTest extends TestCase
{
    private function createReadDatabaseMock(): Stub&ilDBInterface
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value, string $type): string => "'" . (string) $value . "'"
        );
        $db->method('like')->willReturnCallback(
            static fn(string $column, string $type, string $value = "?", bool $ci = true): string =>
                $column . " LIKE " . "'" . $value . "'"
        );

        return $db;
    }

    /**
     * The read methods call fetchObject() on the database object, not on the statement, so the rows are stubbed on
     * $db. query() is left to each test, so no two stubs compete.
     *
     * @param list<array<string, mixed>> $rows each row as an associative array of column => value
     */
    private function configureDbToReturnObjectRows(Stub&ilDBInterface $db, array $rows): void
    {
        $objects = array_map(static fn(array $row): \stdClass => (object) $row, $rows);
        $objects[] = null; // terminates the while ($row = ...) loop
        $db->method('fetchObject')->willReturn(...$objects);
    }

    /**
     * @param list<array<string, mixed>> $rows each row as an associative array of column => value
     */
    private function createStatementReturningAssocRows(array $rows): Stub&ilDBStatement
    {
        $statement = $this->createStub(ilDBStatement::class);
        $rows[] = null; // terminates the while ($row = ...) loop
        $statement->method('fetchRow')->willReturn(...$rows);

        return $statement;
    }

    private function createRepository(
        ilDBInterface $db,
        string $absolute_path = '/does/not/matter/for/db-only/tests'
    ): InstalledLanguageDatabaseRepository {
        return new InstalledLanguageDatabaseRepository(
            $db,
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
            new FixedInstallationRoot($absolute_path)
        );
    }

    public function testGetAvailableLanguagesMapsObjIdAndStatusToTheCorrectLanguageNotSwapped(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createStub(ilDBStatement::class));
        $this->configureDbToReturnObjectRows($db, [
            ['title' => 'de', 'description' => 'installed', 'obj_id' => 5],
            ['title' => 'en', 'description' => 'not_installed', 'obj_id' => 8],
        ]);

        $result = $this->createRepository($db)->getAvailableLanguages();

        $this->assertSame(
            [
                'de' => ['obj_id' => 5, 'status' => 'installed'],
                'en' => ['obj_id' => 8, 'status' => 'not_installed'],
            ],
            $result
        );
    }

    public function testGetAvailableLanguagesReturnsEmptyArrayWhenNoLanguagesAreKnown(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createStub(ilDBStatement::class));
        $this->configureDbToReturnObjectRows($db, []);

        $this->assertSame([], $this->createRepository($db)->getAvailableLanguages());
    }

    public function testGetInstalledLanguagesQueriesForInstalledPrefixAndReturnsTitles(): void
    {
        $db = $this->createReadDatabaseMock();
        $captured_query = null;
        $db->method('query')->willReturnCallback(
            function (string $query) use (&$captured_query): ilDBStatement {
                $captured_query = $query;
                return $this->createStub(ilDBStatement::class);
            }
        );
        $this->configureDbToReturnObjectRows($db, [
            ['title' => 'de'],
            ['title' => 'en'],
        ]);

        $result = $this->createRepository($db)->getInstalledLanguages();

        $this->assertSame(['de', 'en'], $result);
        $this->assertStringContainsString("type = 'lng'", $captured_query);
        $this->assertStringContainsString("description LIKE 'installed%'", $captured_query);
    }

    public function testGetInstalledLocalLanguagesQueriesForExactlyInstalledLocalAndReturnsTitles(): void
    {
        $db = $this->createReadDatabaseMock();
        $captured_query = null;
        $db->method('query')->willReturnCallback(
            function (string $query) use (&$captured_query): ilDBStatement {
                $captured_query = $query;
                return $this->createStub(ilDBStatement::class);
            }
        );
        $this->configureDbToReturnObjectRows($db, [
            ['title' => 'de'],
        ]);

        $result = $this->createRepository($db)->getInstalledLocalLanguages();

        $this->assertSame(['de'], $result);
        $this->assertStringContainsString("description = 'installed_local'", $captured_query);
    }

    public function testGetLocalChangesDefaultsMinAndMaxDateWhenEmptyStringsArePassed(): void
    {
        $db = $this->createReadDatabaseMock();
        $captured_query = null;
        $db->method('query')->willReturnCallback(
            function (string $query) use (&$captured_query): ilDBStatement {
                $captured_query = $query;
                return $this->createStatementReturningAssocRows([]);
            }
        );

        $this->createRepository($db)->getLocalChanges('de', '', '');

        $this->assertStringContainsString("'1980-01-01 00:00:00'", $captured_query);
        $this->assertStringContainsString("'2200-01-01 00:00:00'", $captured_query);
    }

    public function testGetLocalChangesKeepsExplicitlyGivenDatesInsteadOfDefaulting(): void
    {
        $db = $this->createReadDatabaseMock();
        $captured_query = null;
        $db->method('query')->willReturnCallback(
            function (string $query) use (&$captured_query): ilDBStatement {
                $captured_query = $query;
                return $this->createStatementReturningAssocRows([]);
            }
        );

        $this->createRepository($db)->getLocalChanges('de', '2020-01-01 00:00:00', '2021-01-01 00:00:00');

        $this->assertStringContainsString("'2020-01-01 00:00:00'", $captured_query);
        $this->assertStringContainsString("'2021-01-01 00:00:00'", $captured_query);
        $this->assertStringNotContainsString('1980-01-01', $captured_query);
        $this->assertStringNotContainsString('2200-01-01', $captured_query);
    }

    public function testGetLocalChangesStructuresResultAsModuleThenIdentifierThenValue(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createStatementReturningAssocRows([
            ['module' => 'common', 'identifier' => 'foo', 'value' => 'Foo Value'],
            ['module' => 'common', 'identifier' => 'bar', 'value' => 'Bar Value'],
            ['module' => 'other', 'identifier' => 'baz', 'value' => 'Baz Value'],
        ]));

        $result = $this->createRepository($db)->getLocalChanges('de');

        $this->assertSame(
            [
                'common' => ['foo' => 'Foo Value', 'bar' => 'Bar Value'],
                'other' => ['baz' => 'Baz Value'],
            ],
            $result
        );
    }

    public function testGetLocalChangesReturnsEmptyArrayWhenNothingMatches(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createStatementReturningAssocRows([]));

        $this->assertSame([], $this->createRepository($db)->getLocalChanges('de'));
    }

    public function testGetLanguageEntriesStructuresResultAsModuleThenIdentifierThenValue(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createStatementReturningAssocRows([
            ['module' => 'common', 'identifier' => 'foo', 'value' => 'Foo Value'],
            ['module' => 'common', 'identifier' => 'bar', 'value' => 'Bar Value'],
            ['module' => 'other', 'identifier' => 'baz', 'value' => 'Baz Value'],
        ]));

        $result = $this->createRepository($db)->getLanguageEntries('de');

        $this->assertSame(
            [
                'common' => ['foo' => 'Foo Value', 'bar' => 'Bar Value'],
                'other' => ['baz' => 'Baz Value'],
            ],
            $result
        );
    }

    public function testGetLanguageEntriesReturnsEmptyArrayWhenNothingMatches(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createStatementReturningAssocRows([]));

        $this->assertSame([], $this->createRepository($db)->getLanguageEntries('de'));
    }

    /**
     * Unlike getLocalChanges(), getLanguageEntries() returns every stored entry of the language key, so its query
     * must not filter on local_change.
     */
    public function testGetLanguageEntriesQueryDoesNotFilterByLocalChange(): void
    {
        $db = $this->createReadDatabaseMock();
        $captured_query = null;
        $db->method('query')->willReturnCallback(
            function (string $query) use (&$captured_query): ilDBStatement {
                $captured_query = $query;
                return $this->createStatementReturningAssocRows([]);
            }
        );

        $this->createRepository($db)->getLanguageEntries('de');

        $this->assertStringContainsString("lang_key = 'de'", $captured_query);
        $this->assertStringNotContainsString('local_change', $captured_query);
    }

    public function testGetLocalLanguagesExtractsKeysFromLocalFilesAndIgnoresUnrelatedFiles(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/customizing/ilias_de.lang.local', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/ilias_en.lang.local', 'irrelevant content');
        // wrong naming scheme (missing .local suffix, a typo): ignored
        file_put_contents($root . '/lang/customizing/ilias_fr.lang', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/README.md', 'irrelevant content');

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $result = $repository->getLocalLanguages();

            sort($result);
            $this->assertSame(['de', 'en'], $result);
            $this->assertSame(array_values(array_unique($result)), array_values($result));
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * The discovery regex must accept exactly ParsesLanguageKeyList::LANGUAGE_KEY_FORMAT ('/^[a-z]{2}$/'): other
     * file names must be ignored, or they would fail later in the Activity's stricter validation.
     */
    public function testGetLocalLanguagesIgnoresFilesWithAnInvalidLanguageKeyShapeInTheirName(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/customizing/ilias_de.lang.local', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/ilias_DE.lang.local', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/ilias_12.lang.local', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/ilias_Fr.lang.local', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/ilias_deu.lang.local', 'irrelevant content');

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $result = $repository->getLocalLanguages();

            $this->assertSame(['de'], $result);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * Same for getInstallableLanguages() (lang/) instead of getLocalLanguages() (lang/customizing/).
     */
    public function testGetInstallableLanguagesIgnoresFilesWithAnInvalidLanguageKeyShapeInTheirName(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', 'irrelevant content');
        file_put_contents($root . '/lang/ilias_DE.lang', 'irrelevant content');
        file_put_contents($root . '/lang/ilias_12.lang', 'irrelevant content');

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $result = $repository->getInstallableLanguages();

            $this->assertSame(['de'], $result);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * The regex ends in `\z`, not `$`, which in PCRE also matches before a trailing newline; file names may
     * legally contain "\n".
     */
    public function testGetInstallableLanguagesIgnoresAFileNameWithATrailingNewline(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', 'irrelevant content');
        // distinct key "zz": a wrongly accepted trailing "\n" would show up as an extra entry, not collapse into "de"
        file_put_contents($root . "/lang/ilias_zz.lang\n", 'irrelevant content');

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $result = $repository->getInstallableLanguages();

            $this->assertSame(['de'], $result);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testGetLocalLanguagesReturnsEmptyArrayWhenCustomizingDirectoryIsEmpty(): void
    {
        $root = $this->createTempInstallationRoot();

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);
            $this->assertSame([], $repository->getLocalLanguages());
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testGetInstallableLanguagesExtractsKeysFromMainFilesAndIgnoresUnrelatedFiles(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', 'irrelevant content');
        file_put_contents($root . '/lang/ilias_en.lang', 'irrelevant content');
        // a local file is not installable, nor is a name outside the "ilias_xx.lang" scheme
        file_put_contents($root . '/lang/ilias_fr.lang.local', 'irrelevant content');
        file_put_contents($root . '/lang/notes.txt', 'irrelevant content');

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $result = $repository->getInstallableLanguages();

            sort($result);
            $this->assertSame(['de', 'en'], $result);
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testCheckLocalLanguageFileReturnsFalseWhenNoLocalFileExists(): void
    {
        $root = $this->createTempInstallationRoot();

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);
            // unlike checkLanguage(), a missing local file is not acceptable here
            $this->assertFalse($repository->checkLocalLanguageFile('de'));
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testCheckLocalLanguageFileReturnsTrueForAValidLocalFile(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents(
            $root . '/lang/customizing/ilias_de.lang.local',
            "<!-- language file start -->\ncommon#:#test#:#Custom Value\n"
        );

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);
            $this->assertTrue($repository->checkLocalLanguageFile('de'));
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testCheckLocalLanguageFileReturnsFalseForAnInvalidLocalFile(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents(
            $root . '/lang/customizing/ilias_de.lang.local',
            "common#:#test#:#Custom Value\n"
        );

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);
            $this->assertFalse($repository->checkLocalLanguageFile('de'));
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testCheckLocalLanguageFileReturnsFalseForMalformedLine(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents(
            $root . '/lang/customizing/ilias_de.lang.local',
            "<!-- language file start -->\nonly_one_field\n"
        );

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);
            $this->assertFalse($repository->checkLocalLanguageFile('de'));
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * getCustomizingDirectories() always yields one directory in production, so the "zero directories" branch
     * (`return false`) is reachable only with a mocked manager, which this class otherwise avoids.
     */
    public function testCheckLocalLanguageFileReturnsFalseWhenNoCustomizingDirectoryIsConfiguredAtAll(): void
    {
        $manager = $this->createStub(LanguageFileDirectoryManager::class);
        $manager->method('getCustomizingDirectories')->willReturnCallback(
            static function (): \Generator {
                return;
                yield; // unreachable; makes this a generator that yields nothing
            }
        );

        $repository = new InstalledLanguageDatabaseRepository(
            $this->createReadDatabaseMock(),
            $manager,
            new FixedInstallationRoot('/does/not/matter/for/this/test')
        );

        $this->assertFalse($repository->checkLocalLanguageFile('de'));
    }

    /**
     * checkLanguage() for a migrated module: the shipped `.po` is the only source of its values, so an unparsable one
     * invalidates the language. Builds a root with a valid main lang file and a "pilot" component whose shipped de
     * `.po` has $po_content (null = no `.po`).
     */
    private function checkLanguageWithShippedPo(?string $po_content, bool $local_directory = false): bool
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', "<!-- language file start -->\ncommon#:#yes#:#Ja\n");
        mkdir($root . '/components/pilot/lang', 0777, true);
        if ($po_content !== null) {
            file_put_contents($root . '/components/pilot/lang/pilot_de.po', $po_content);
        }
        $pilot = MigratedPoFixture::directory('pilot', 'components/pilot/lang/', $local_directory);

        try {
            $repository = new InstalledLanguageDatabaseRepository(
                $this->createReadDatabaseMock(),
                $local_directory
                    ? new LanguageFileDirectoryManager($pilot, new MainLanguageFileDirectory())
                    : new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory(), $pilot),
                new FixedInstallationRoot($root)
            );
            return $repository->checkLanguage('de');
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testCheckLanguageAcceptsAParsableShippedPo(): void
    {
        $this->assertTrue($this->checkLanguageWithShippedPo("msgctxt \"pilot\"\nmsgid \"greeting\"\nmsgstr \"Hallo\"\n"));
    }

    /**
     * Everything gettext/gettext's StrictPoLoader rejects invalidates the language, including what the old parser
     * tolerated (a duplicate, a message without msgstr).
     */
    #[DataProvider('unparsableShippedPo')]
    public function testCheckLanguageRejectsAnUnparsableShippedPo(string $po_content): void
    {
        $this->assertFalse($this->checkLanguageWithShippedPo($po_content));
    }

    public static function unparsableShippedPo(): array
    {
        $message = "msgctxt \"pilot\"\nmsgid \"greeting\"\nmsgstr \"Hallo\"\n";

        return [
            'unterminated string' => ["msgctxt \"pilot\"\nmsgid \"abc\nmsgstr \"Hallo\"\n"],
            'duplicate message' => [$message . "\n" . $message],
            'message without msgstr' => ["msgctxt \"pilot\"\nmsgid \"greeting\"\n"],
            'truncated inside a string' => [substr($message, 0, -4)],
            'binary data' => ["\xde\x12\x04\x95\x00\x00\x00\x00"],
            'header value with a line break' => ["msgid \"\"\nmsgstr \"\"\n\"X-A: a\\rb\\n\"\n\n" . $message],
        ];
    }

    /**
     * A BOM and CRLF line endings are no reason to refuse the language.
     */
    public function testCheckLanguageAcceptsAShippedPoWithBomAndCrLf(): void
    {
        $this->assertTrue($this->checkLanguageWithShippedPo(
            "\xEF\xBB\xBFmsgid \"\"\r\nmsgstr \"\"\r\n\"Language: de\\n\"\r\n\r\nmsgctxt \"pilot\"\r\nmsgid \"greeting\"\r\nmsgstr \"Hallo\"\r\n"
        ));
    }

    public function testCheckLanguageAcceptsAModuleWithoutShippedPoForTheLanguage(): void
    {
        $this->assertTrue($this->checkLanguageWithShippedPo(null));
    }

    public function testCheckLanguageDoesNotParsePoFilesOfTheLocalDirectory(): void
    {
        $this->assertTrue($this->checkLanguageWithShippedPo("msgid \"abc\n", true));
    }

    /**
     * An empty `.po` is a valid, empty catalog.
     */
    public function testCheckLanguageAcceptsAnEmptyShippedPo(): void
    {
        $this->assertTrue($this->checkLanguageWithShippedPo(''));
    }

    /**
     * checkShippedLanguage() is checkLanguage() without the customizing directory: an invalid "ilias_de.lang.local"
     * must not block it.
     */
    public function testCheckShippedLanguageIgnoresAnInvalidCustomizingFileThatMakesCheckLanguageFail(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', "<!-- language file start -->\ncommon#:#yes#:#Ja\n");
        file_put_contents($root . '/lang/customizing/ilias_de.lang.local', "<!-- language file start -->\nonly_one_field\n");

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $this->assertTrue($repository->checkShippedLanguage('de'), 'the invalid customizing file is never consulted');
            $this->assertFalse($repository->checkLanguage('de'), 'precondition: the same file makes checkLanguage() fail');
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * An invalid global "ilias_de.lang" is part of getDirectories(), so checkShippedLanguage() fails like checkLanguage().
     */
    public function testCheckShippedLanguageRejectsAnInvalidGlobalLanguageFile(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', "common#:#yes#:#Ja\n");

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $this->assertFalse($repository->checkShippedLanguage('de'));
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * An unparsable shipped `.po` of a PO-maintained module is part of getDirectories() too, so
     * checkShippedLanguage() fails like checkLanguage().
     */
    public function testCheckShippedLanguageRejectsAnUnparsableShippedPo(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', "<!-- language file start -->\ncommon#:#yes#:#Ja\n");
        mkdir($root . '/components/pilot/lang', 0777, true);
        file_put_contents($root . '/components/pilot/lang/pilot_de.po', "msgctxt \"pilot\"\nmsgid \"abc\nmsgstr \"Hallo\"\n");
        $pilot = MigratedPoFixture::directory('pilot', 'components/pilot/lang/');

        try {
            $repository = new InstalledLanguageDatabaseRepository(
                $this->createReadDatabaseMock(),
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory(), $pilot),
                new FixedInstallationRoot($root)
            );

            $this->assertFalse($repository->checkShippedLanguage('de'));
        } finally {
            $this->removeDirectory($root);
        }
    }

    private function createTempInstallationRoot(): string
    {
        $dir = sys_get_temp_dir() . '/ilias_lang_repo_test_' . bin2hex(random_bytes(8));
        mkdir($dir . '/lang/customizing', 0777, true);
        return $dir;
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
