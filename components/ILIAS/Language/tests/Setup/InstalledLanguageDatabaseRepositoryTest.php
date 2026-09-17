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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Focused coverage for InstalledLanguageDatabaseRepository, which previously
 * had no dedicated test class at all. Database-backed methods are exercised
 * with an ilDBInterface mock returning controlled rows (real assertions on
 * the resulting arrays and on the SQL fragments built from quote()/like(),
 * not just mock call-count expectations); filesystem-backed methods use a
 * real temporary installation root, mirroring the pattern in
 * LanguageInstallationManagerTest.
 */
class InstalledLanguageDatabaseRepositoryTest extends TestCase
{
    private function createReadDatabaseMock(): MockObject&ilDBInterface
    {
        $db = $this->createMock(ilDBInterface::class);
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
     * getInstalledLanguages()/getInstalledLocalLanguages()/getAvailableLanguages()
     * call fetchObject() on the *database* object itself (passing the
     * statement in), not on the statement - so the row sequence is stubbed on
     * $db here. query() is deliberately left untouched - each test stubs it
     * itself (once) so there is no ambiguity about which of two competing
     * stubs for the same method wins.
     *
     * @param list<array<string, mixed>> $rows each row as an associative array of column => value
     */
    private function configureDbToReturnObjectRows(MockObject&ilDBInterface $db, array $rows): void
    {
        $objects = array_map(static fn(array $row): \stdClass => (object) $row, $rows);
        $objects[] = null; // terminates the while ($row = ...) loop
        $db->method('fetchObject')->willReturn(...$objects);
    }

    /**
     * @param list<array<string, mixed>> $rows each row as an associative array of column => value
     */
    private function createStatementReturningAssocRows(array $rows): MockObject&ilDBStatement
    {
        $statement = $this->createMock(ilDBStatement::class);
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
            $absolute_path
        );
    }

    public function testGetAvailableLanguagesMapsObjIdAndStatusToTheCorrectLanguageNotSwapped(): void
    {
        $db = $this->createReadDatabaseMock();
        $db->method('query')->willReturn($this->createMock(ilDBStatement::class));
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
        $db->method('query')->willReturn($this->createMock(ilDBStatement::class));
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
                return $this->createMock(ilDBStatement::class);
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
                return $this->createMock(ilDBStatement::class);
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
     * The defining difference to getLocalChanges(): getLanguageEntries()
     * must return every stored entry for the language key regardless of
     * local_change, so its query must not filter on local_change at all -
     * unlike getLocalChanges(), which restricts to a local_change date
     * range (see testGetLocalChangesDefaultsMinAndMaxDateWhenEmptyStringsArePassed).
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
        // Wrong naming scheme (missing .local suffix, and a typo) - must be ignored.
        file_put_contents($root . '/lang/customizing/ilias_fr.lang', 'irrelevant content');
        file_put_contents($root . '/lang/customizing/README.md', 'irrelevant content');

        try {
            $repository = $this->createRepository($this->createReadDatabaseMock(), $root);

            $result = $repository->getLocalLanguages();

            sort($result);
            $this->assertSame(['de', 'en'], $result);
            // array_unique invariant: no duplicate keys in the result.
            $this->assertSame(array_values(array_unique($result)), array_values($result));
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * Regression test: the discovery regex must accept exactly the same
     * shape as \ILIAS\Language\Activities\ParsesLanguageKeyList::LANGUAGE_KEY_FORMAT
     * ('/^[a-z]{2}$/') - two lowercase ASCII letters, nothing else. A file
     * whose "language key" part is uppercase, a digit, or longer/shorter
     * than two characters must be silently ignored here, not listed as a
     * local language: previously such a file WAS listed (any two
     * characters were accepted), only to fail later at the stricter
     * ParsesLanguageKeyList validation once an Activity actually tried to
     * install/refresh it.
     */
    public function testGetLocalLanguagesIgnoresFilesWithAnInvalidLanguageKeyShapeInTheirName(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/customizing/ilias_de.lang.local', 'irrelevant content');
        // Uppercase letters - would fail ParsesLanguageKeyList's '/^[a-z]{2}$/' later.
        file_put_contents($root . '/lang/customizing/ilias_DE.lang.local', 'irrelevant content');
        // Digits instead of letters.
        file_put_contents($root . '/lang/customizing/ilias_12.lang.local', 'irrelevant content');
        // Mixed case.
        file_put_contents($root . '/lang/customizing/ilias_Fr.lang.local', 'irrelevant content');
        // Three letters - too long for the fixed two-letter format.
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
     * Same regression as above, for getInstallableLanguages() (the main
     * lang/ directory) rather than getLocalLanguages() (lang/customizing/).
     */
    public function testGetInstallableLanguagesIgnoresFilesWithAnInvalidLanguageKeyShapeInTheirName(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents($root . '/lang/ilias_de.lang', 'irrelevant content');
        // Uppercase letters - would fail ParsesLanguageKeyList's '/^[a-z]{2}$/' later.
        file_put_contents($root . '/lang/ilias_DE.lang', 'irrelevant content');
        // Digits instead of letters.
        file_put_contents($root . '/lang/ilias_12.lang', 'irrelevant content');

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
        // Wrong naming scheme - a local file must not be picked up as installable,
        // nor a file that doesn't follow the "ilias_xx.lang" scheme at all.
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
            // Unlike checkLanguage(), a missing local file is NOT acceptable here.
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
        // Missing the "<!-- language file start -->" header entirely.
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
        // Valid header, but the line does not split into exactly 3 fields.
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
     * checkLocalLanguageFile() used to be a `foreach (...) { return ...; }`
     * loop that only ever looked at the first yielded directory (reading
     * like an accidental early return) and fell through to `return false;`
     * after the loop for zero directories. It was rewritten to make "only
     * the first directory, false if there is none" explicit via
     * iterator_to_array(). LanguageFileDirectoryManager::getCustomizingDirectories()
     * always yields exactly one directory in production (see its own
     * docblock/implementation), so this "zero directories" branch is not
     * reachable through the real manager - it is only reachable by mocking
     * the manager, which is otherwise a concrete collaborator this test
     * class deliberately avoids mocking (see createRepository()). Covering
     * it here guards the explicit `if ($directories === []) { return false; }`
     * branch against a future refactor reintroducing the old foreach-return
     * (which would throw/warn on an empty generator instead of cleanly
     * returning false, or silently do the wrong thing for a differently
     * shaped collaborator).
     */
    public function testCheckLocalLanguageFileReturnsFalseWhenNoCustomizingDirectoryIsConfiguredAtAll(): void
    {
        $manager = $this->createMock(LanguageFileDirectoryManager::class);
        $manager->method('getCustomizingDirectories')->willReturnCallback(
            static function (): \Generator {
                return;
                yield; // unreachable; makes this a generator that yields nothing
            }
        );

        $repository = new InstalledLanguageDatabaseRepository(
            $this->createReadDatabaseMock(),
            $manager,
            '/does/not/matter/for/this/test'
        );

        $this->assertFalse($repository->checkLocalLanguageFile('de'));
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
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
