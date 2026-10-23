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

use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\LanguageInstallationManager;
use ILIAS\Language\Setup\TranslationCache;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Focused coverage for LanguageInstallationManager. In particular, these
 * tests pin down last_update handling for object_data bookkeeping rows: the
 * UPDATE path (registerInstalledLanguage()'s update branch, and
 * installLanguages()' uninstall branch) sets last_update via the manager's
 * injectable clock, so a test can assert the exact value instead of only
 * that *some* value was set - while the INSERT path deliberately keeps using
 * $db->now() instead, which is asserted explicitly as well. The clock exists
 * for testability, not because the date was ever computed incorrectly.
 */
class LanguageInstallationManagerTest extends TestCase
{
    private function createDatabaseMock(): MockObject&ilDBInterface
    {
        $db = $this->createMock(ilDBInterface::class);
        $db->method('nextId')->willReturn(42);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value): string => "'" . (string) $value . "'"
        );
        $db->method('now')->willReturn('NOW()');
        $db->method('manipulate')->willReturn(1);

        return $db;
    }

    private function createManager(
        ilDBInterface $db,
        InstalledLanguageRepository $repository,
        ?\DateTimeImmutable $now = null
    ): LanguageInstallationManager {
        return new LanguageInstallationManager(
            $db,
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                new MainLanguageFileDirectory()
            ),
            (string) realpath(__DIR__ . '/../../../../../'),
            $repository,
            $now !== null ? static fn(): \DateTimeImmutable => $now : null
        );
    }

    public function testRegisterInstalledLanguageUpdateUsesInjectedClockForLastUpdate(): void
    {
        $db = $this->createDatabaseMock();
        $db->expects($this->once())
            ->method('manipulate')
            ->with($this->logicalAnd(
                $this->stringContains(/** @lang text */ 'UPDATE object_data'),
                $this->stringContains("last_update = '2026-01-02 03:04:05'")
            ));

        $manager = $this->createManager(
            $db,
            $this->createMock(InstalledLanguageRepository::class),
            new \DateTimeImmutable('2026-01-02 03:04:05', new \DateTimeZone('UTC'))
        );

        $manager->registerInstalledLanguage(
            'de',
            ['de' => ['obj_id' => 7, 'status' => 'not_installed']],
            []
        );
    }

    public function testRegisterInstalledLanguageInsertUsesDbNowNotTheClock(): void
    {
        $db = $this->createDatabaseMock();
        $db->expects($this->once())
            ->method('manipulate')
            ->with($this->logicalAnd(
                $this->stringContains(/** @lang text */ 'INSERT INTO object_data'),
                $this->stringContains('NOW(),NOW()')
            ));

        $manager = $this->createManager(
            $db,
            $this->createMock(InstalledLanguageRepository::class),
            new \DateTimeImmutable('2026-01-02 03:04:05', new \DateTimeZone('UTC'))
        );

        $manager->registerInstalledLanguage('de', [], []);
    }

    public function testInstallLanguagesSetsLastUpdateOnUninstallUsingInjectedClock(): void
    {
        $db = $this->createDatabaseMock();
        $calls = [];
        $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
            $calls[] = $query;
            return 1;
        });

        $repository = $this->createMock(InstalledLanguageRepository::class);
        $repository->method('getAvailableLanguages')->willReturn([
            'fr' => ['obj_id' => 3, 'status' => 'installed'],
        ]);
        $repository->method('getLocalLanguages')->willReturn([]);

        $manager = $this->createManager(
            $db,
            $repository,
            new \DateTimeImmutable('2026-01-02 03:04:05', new \DateTimeZone('UTC'))
        );

        // 'fr' is no longer requested: flushed, bookkeeping row flipped to "not_installed" with a fresh last_update
        $result = $manager->installLanguages([]);

        $this->assertTrue($result);
        $bookkeeping_update = array_values(array_filter(
            $calls,
            static fn(string $query): bool => str_contains($query, /** @lang text */ 'UPDATE object_data')
        ));
        $this->assertCount(1, $bookkeeping_update);
        $this->assertStringContainsString("description = 'not_installed'", $bookkeeping_update[0]);
        $this->assertStringContainsString("last_update = '2026-01-02 03:04:05'", $bookkeeping_update[0]);
    }

    public function testFlushLanguageForInstallationKeepsLocalChanges(): void
    {
        $db = $this->createDatabaseMock();
        $db->expects($this->once())
            ->method('manipulate')
            ->with($this->logicalAnd(
                $this->stringContains(/** @lang text */ 'DELETE FROM lng_data'),
                $this->stringContains('local_change IS NULL')
            ));

        $manager = $this->createManager($db, $this->createMock(InstalledLanguageRepository::class));

        $manager->flushLanguageForInstallation('de');
    }

    public function testFlushLanguageForUninstallationDeletesEverything(): void
    {
        $db = $this->createDatabaseMock();
        $calls = [];
        $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
            $calls[] = $query;
            return 1;
        });

        $manager = $this->createManager($db, $this->createMock(InstalledLanguageRepository::class));

        $manager->flushLanguageForUninstallation('de');

        $this->assertCount(2, $calls);
        $this->assertStringContainsString(/** @lang text */ 'DELETE FROM lng_data', $calls[0]);
        $this->assertStringNotContainsString('local_change IS NULL', $calls[0]);
        $this->assertStringContainsString(/** @lang text */ 'DELETE FROM lng_modules', $calls[1]);
    }

    /**
     * Reinstalling a language while removing its local changes must not re-apply the customizing directory's file,
     * which would reinstate the data being removed (see ilObjLanguageFolderGUI::uninstallChangesObject()).
     */
    public function testInsertLanguageForRemovingLocalChangesIgnoresCustomizingDirectory(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'test', 'Global Value']]);
        $this->writeLangFile($root . '/lang/customizing/ilias_de.lang.local', [['common', 'test', 'Custom Value']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            // no DB local changes are consulted: a clean re-seed has nothing to merge
            $repository->expects($this->never())->method('getLocalChanges');

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $manager->insertLanguageForRemovingLocalChanges('de');

            $insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')
            ));
            $this->assertCount(1, $insert);
            $this->assertStringContainsString("'Global Value'", $insert[0]);
            $this->assertStringNotContainsString('Custom Value', $insert[0]);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * The ordinary installation path still merges the customizing directory's file on top of the global one.
     */
    public function testInsertLanguageForInstallationStillMergesCustomizingDirectory(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'test', 'Global Value']]);
        $this->writeLangFile($root . '/lang/customizing/ilias_de.lang.local', [['common', 'test', 'Custom Value']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository,
                static fn(): \DateTimeImmutable => new \DateTimeImmutable('2026-01-02 03:04:05', new \DateTimeZone('UTC'))
            );

            $manager->insertLanguageForInstallation('de');

            $insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')
            ));
            $this->assertCount(1, $insert);
            $this->assertStringContainsString("'Global Value'", $insert[0]);
            $this->assertStringContainsString("'Custom Value'", $insert[0]);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * The local-changes lookup depends only on language and file mtime and nothing in the loop writes to the
     * database, so it runs once per directory, not once per line of a customizing file; the query count must not
     * grow with the file.
     */
    public function testLocalChangesAreLookedUpOncePerDirectoryNotPerEntry(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [
            ['common', 'locally_changed', 'Global Value'],
            ['common', 'plain', 'Plain Global'],
        ]);

        $local_entries = [['common', 'newer_in_db', 'From Local File']];
        for ($i = 0; $i < 200; $i++) {
            $local_entries[] = ['common', 'override_' . $i, 'Local ' . $i];
        }
        $this->writeLangFile($root . '/lang/customizing/ilias_de.lang.local', $local_entries);

        try {
            $calls = 0;
            $inserts = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(
                static function (string $query) use (&$inserts): int {
                    if (str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')) {
                        $inserts[] = $query;
                    }
                    return 1;
                }
            );

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('getLocalChanges')->willReturnCallback(
                static function (string $lang_key, string $min_date = '') use (&$calls): array {
                    $calls++;
                    // no date: the seed of changes already in the database; with a date: changes newer than the customizing file
                    return $min_date === ''
                        ? ['common' => ['locally_changed' => 'DB Local Change']]
                        : ['common' => ['newer_in_db' => 'DB Is Newer']];
                }
            );

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository,
                static fn(): \DateTimeImmutable => new \DateTimeImmutable('2026-01-02 03:04:05', new \DateTimeZone('UTC'))
            );

            $manager->insertLanguageForInstallation('de');

            // one seed lookup plus one for the single customizing directory, independent of the 201 entries
            $this->assertSame(2, $calls);

            $this->assertCount(1, $inserts);
            $insert = $inserts[0];
            // a global entry with a local change in the database keeps the database value
            $this->assertStringNotContainsString("'Global Value'", $insert);
            $this->assertStringContainsString("'Plain Global'", $insert);
            // a customizing entry with an even newer database change is not overwritten by the file either
            $this->assertStringNotContainsString("'From Local File'", $insert);
            $this->assertStringContainsString("'Local 0'", $insert);
            $this->assertStringContainsString("'Local 199'", $insert);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * insertLanguageForApplyingLocalChanges() reads only the customizing directory: no base content in the INSERT and
     * no flush (DELETE FROM lng_data).
     */
    public function testInsertLanguageForApplyingLocalChangesOnlyReadsCustomizingDirectory(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'test', 'Global Value']]);
        $this->writeLangFile($root . '/lang/customizing/ilias_de.lang.local', [['common', 'test', 'Custom Value']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->expects($this->once())
                ->method('getLanguageEntries')
                ->with('de')
                ->willReturn([]);
            // insertLanguage()'s "newer change in the DB?" check still calls getLocalChanges() once per local directory;
            // the seed itself comes from getLanguageEntries() alone
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $manager->insertLanguageForApplyingLocalChanges('de');

            $insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')
            ));
            $this->assertCount(1, $insert);
            $this->assertStringContainsString("'Custom Value'", $insert[0]);
            $this->assertStringNotContainsString('Global Value', $insert[0]);

            $flush = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'DELETE FROM lng_data')
            ));
            $this->assertCount(0, $flush);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * insertLanguage() rebuilds the lng_modules row from the seed plus the given directories: if the seed held only
     * local changes (getLocalChanges()) instead of every stored entry (getLanguageEntries()), a base entry the
     * customizing file does not override would vanish from the row.
     */
    public function testInsertLanguageForApplyingLocalChangesPreservesUnrelatedSeedEntriesInModulesCache(): void
    {
        $root = $this->createTempInstallationRoot();
        // only 'overridden' is touched by the customizing file; 'untouched_by_customizing' must survive via the seed
        $this->writeLangFile(
            $root . '/lang/customizing/ilias_de.lang.local',
            [['common', 'overridden', 'New Custom Value']]
        );

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->expects($this->once())->method('getLanguageEntries')->with('de')->willReturn([
                'common' => [
                    'overridden' => 'Old Value',
                    'untouched_by_customizing' => 'Still Here',
                ],
            ]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $manager->insertLanguageForApplyingLocalChanges('de');

            $modules_insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_modules')
            ));
            $this->assertCount(1, $modules_insert);
            $serialized = serialize([
                'overridden' => 'New Custom Value',
                'untouched_by_customizing' => 'Still Here',
            ]);
            $this->assertStringContainsString($serialized, $modules_insert[0]);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * installLanguages() registers a language that passes validation and is not yet in object_data via the INSERT
     * branch of registerInstalledLanguage().
     */
    public function testInstallLanguagesRegistersBrandNewLanguage(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'greeting', 'Hallo']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->expects($this->once())->method('checkLanguage')->with('de')->willReturn(true);
            $repository->method('getAvailableLanguages')->willReturn([]);
            $repository->method('getLocalLanguages')->willReturn([]);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $result = $manager->installLanguages(['de']);

            $this->assertTrue($result);

            $inserts_object_data = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO object_data')
            ));
            $this->assertCount(1, $inserts_object_data);
            $this->assertStringContainsString("'de'", $inserts_object_data[0]);
            $this->assertStringContainsString("'installed'", $inserts_object_data[0]);
            $this->assertStringNotContainsString("'installed_local'", $inserts_object_data[0]);

            $keep_local_flush = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_contains($query, /** @lang text */ 'DELETE FROM lng_data')
                    && str_contains($query, 'local_change IS NULL')
            ));
            $this->assertCount(1, $keep_local_flush);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * A language failing checkLanguage() is reported as failed (a list, not `true`) and skipped in the second pass:
     * neither re-synced nor flushed, even though it is known in object_data.
     */
    public function testInstallLanguagesReportsFailedLanguageAndLeavesItUntouched(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'greeting', 'Hallo']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('checkLanguage')->willReturnMap([
                ['de', true],
                ['xx', false],
            ]);
            $repository->method('getAvailableLanguages')->willReturn([
                // known from a previous installation, its language file has become invalid
                'xx' => ['obj_id' => 5, 'status' => 'installed'],
            ]);
            $repository->method('getLocalLanguages')->willReturn([]);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $result = $manager->installLanguages(['de', 'xx']);

            $this->assertSame(['xx'], $result);

            $inserts_object_data = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO object_data')
            ));
            $this->assertCount(1, $inserts_object_data);
            $this->assertStringContainsString("'de'", $inserts_object_data[0]);

            // 'xx' is known (obj_id 5) but failed validation: no flush, no status update, no resync
            foreach ($calls as $query) {
                $this->assertStringNotContainsString("obj_id = '5'", $query);
                $this->assertStringNotContainsString("lang_key = 'xx'", $query);
            }
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * A known, still requested language gets its bookkeeping row refreshed via registerInstalledLanguage(), without a
     * full flushLanguage("all") or a forced "not_installed" status.
     */
    public function testInstallLanguagesResyncsAlreadyKnownRequestedLanguage(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_fr.lang', [['common', 'greeting', 'Bonjour']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('checkLanguage')->willReturn(true);
            $repository->method('getAvailableLanguages')->willReturn([
                'fr' => ['obj_id' => 9, 'status' => 'installed'],
            ]);
            $repository->method('getLocalLanguages')->willReturn([]);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $result = $manager->installLanguages(['fr']);
            $this->assertTrue($result);

            // no full flush: flushLanguage("all") deletes without a module filter, unlike insertLanguage()'s own
            // per-module cleanup ("AND module IN (...)")
            $full_flush_deletes = array_values(array_filter(
                $calls,
                static fn(string $query): bool => $query === "DELETE FROM lng_modules WHERE lang_key = 'fr'"
            ));
            $this->assertCount(0, $full_flush_deletes);

            foreach ($calls as $query) {
                $this->assertStringNotContainsString('not_installed', $query);
            }

            // ...but the bookkeeping row is refreshed via UPDATE, keeping "installed"
            $object_data_updates = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'UPDATE object_data')
            ));
            $this->assertCount(1, $object_data_updates);
            $this->assertStringContainsString("obj_id = '9'", $object_data_updates[0]);
            $this->assertStringContainsString("description = 'installed'", $object_data_updates[0]);
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * A language already marked "not_installed" is still flushed but gets no redundant
     * "UPDATE ... description = 'not_installed'": strpos("not_installed", "installed") is 3, so the guard must be
     * `=== 0`, not `!== false` or `>= 0`.
     */
    public function testInstallLanguagesDoesNotReUpdateAlreadyNotInstalledLanguage(): void
    {
        $db = $this->createDatabaseMock();
        $calls = [];
        $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
            $calls[] = $query;
            return 1;
        });

        $repository = $this->createMock(InstalledLanguageRepository::class);
        $repository->method('getAvailableLanguages')->willReturn([
            'de' => ['obj_id' => 4, 'status' => 'not_installed'],
        ]);
        $repository->method('getLocalLanguages')->willReturn([]);

        $manager = $this->createManager($db, $repository);

        $result = $manager->installLanguages([]);

        $this->assertTrue($result);

        $this->assertStringContainsString(
            "DELETE FROM lng_modules WHERE lang_key = 'de'",
            implode("\n", $calls)
        );

        // ...but no UPDATE: it was already "not_installed"
        $updates = array_values(array_filter(
            $calls,
            static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'UPDATE object_data')
        ));
        $this->assertCount(0, $updates);
    }

    /**
     * A .lang line may carry a "###comment" suffix (e.g. "...#:#Delete Answer###fau: testNav"). The value is truncated
     * at "###" and the comment must survive into lng_data.remarks, as in the former
     * ilObjLanguageDBAccess::insertLangEntries().
     */
    public function testInsertLanguageStoresCommentSuffixInRemarksColumn(): void
    {
        $root = $this->createTempInstallationRoot();
        file_put_contents(
            $root . '/lang/ilias_de.lang',
            "<!-- language file start -->\n"
                . "assessment#:#discard_answer#:#Delete Answer###fau: testNav\n"
        );

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('assessment')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $manager->insertLanguageForInstallation('de');

            $insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')
            ));
            $this->assertCount(1, $insert);

            // the value is truncated at "###": the comment must not leak into it
            $this->assertStringContainsString("'Delete Answer'", $insert[0]);
            $this->assertStringNotContainsString('Delete Answer###', $insert[0]);

            // the comment is preserved in the remarks column, not discarded as NULL
            $this->assertStringContainsString(
                "'fau: testNav'",
                $insert[0],
                'The "###" comment suffix must be stored in the remarks column of lng_data, not discarded.'
            );
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * A line without a "###" suffix leaves the remarks column NULL, not an empty string or the text "null".
     */
    public function testInsertLanguageLeavesRemarksNullWhenLineHasNoCommentSuffix(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'test', 'Plain Value']]);

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository
            );

            $manager->insertLanguageForInstallation('de');

            $insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')
            ));
            $this->assertCount(1, $insert);
            $this->assertStringContainsString("'Plain Value'", $insert[0]);
            // the mock's quote() casts values to string, so NULL shows as an empty quoted string before the closing paren
            $this->assertMatchesRegularExpression(
                "/,'',''\\)/",
                $insert[0],
                'Expected the tuple to end in (...,local_change=\'\',remarks=\'\') for a line without a comment suffix.'
            );
        } finally {
            $this->removeDirectory($root);
        }
    }

    /**
     * The two-field lines (identifier#:#value) of a component directory are written with the directory's prefix as
     * module, not with the first field misread as module. The read path (checkLanguage()) is covered in
     * ilSetupLanguageTest.
     */
    public function testInsertLanguageUsesDirectoryPrefixAsModuleForComponentFiles(): void
    {
        $root = $this->createTempInstallationRoot();
        mkdir($root . '/lang/file', 0777, true);
        file_put_contents(
            $root . '/lang/file/ilias_de.lang',
            "<!-- language file start -->\nadd_file#:#Add File\n"
        );

        try {
            $calls = [];
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('file')");
            $db->method('manipulate')->willReturnCallback(static function (string $query) use (&$calls): int {
                $calls[] = $query;
                return 1;
            });

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('getLocalChanges')->willReturn([]);

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(
                    new CustomizingLanguageFileDirectory(),
                    new MainLanguageFileDirectory(),
                    $this->createPrefixedComponentDirectory('lang/file/', 'file')
                ),
                $root,
                $repository
            );

            $manager->insertLanguageForInstallation('de');

            $insert = array_values(array_filter(
                $calls,
                static fn(string $query): bool => str_starts_with($query, /** @lang text */ 'INSERT INTO lng_data')
            ));
            $this->assertCount(1, $insert);
            // module=prefix, identifier=first field, value=second field
            $this->assertStringContainsString("('file','add_file','de','Add File'", $insert[0]);
        } finally {
            $this->removeDirectory($root);
        }
    }

    private function createPrefixedComponentDirectory(string $path, string $prefix): LanguageFileDirectory
    {
        return new class ($path, $prefix) implements LanguageFileDirectory {
            public function __construct(private string $path, private string $prefix)
            {
            }

            public function getPrefix(): string
            {
                return $this->prefix;
            }

            public function getPath(): string
            {
                return $this->path;
            }

            public function getSuffix(): string
            {
                return '';
            }

            public function isLocal(): bool
            {
                return false;
            }
        };
    }

    public function testInsertLanguageInvalidatesTheTranslationCache(): void
    {
        $root = $this->createTempInstallationRoot();
        $this->writeLangFile($root . '/lang/ilias_de.lang', [['common', 'test', 'Global Value']]);

        try {
            $db = $this->createDatabaseMock();
            $db->method('in')->willReturn("module IN ('common')");

            $repository = $this->createMock(InstalledLanguageRepository::class);
            $repository->method('getLocalChanges')->willReturn([]);

            $cache = $this->createMock(TranslationCache::class);
            $cache->expects($this->once())->method('invalidate')->with('de');

            $manager = new LanguageInstallationManager(
                $db,
                new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
                $root,
                $repository,
                null,
                $cache
            );

            $manager->insertLanguageForInstallation('de');
        } finally {
            $this->removeDirectory($root);
        }
    }

    public function testFlushLanguageForUninstallationInvalidatesTheTranslationCache(): void
    {
        $cache = $this->createMock(TranslationCache::class);
        $cache->expects($this->once())->method('invalidate')->with('de');

        $manager = new LanguageInstallationManager(
            $this->createDatabaseMock(),
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
            (string) realpath(__DIR__ . '/../../../../../'),
            $this->createMock(InstalledLanguageRepository::class),
            null,
            $cache
        );

        $manager->flushLanguageForUninstallation('de');
    }

    public function testFlushLanguageForInstallationKeepsTheTranslationCache(): void
    {
        $cache = $this->createMock(TranslationCache::class);
        $cache->expects($this->never())->method('invalidate');

        $manager = new LanguageInstallationManager(
            $this->createDatabaseMock(),
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), new MainLanguageFileDirectory()),
            (string) realpath(__DIR__ . '/../../../../../'),
            $this->createMock(InstalledLanguageRepository::class),
            null,
            $cache
        );

        $manager->flushLanguageForInstallation('de');
    }

    private function createTempInstallationRoot(): string
    {
        $dir = sys_get_temp_dir() . '/ilias_lang_test_' . bin2hex(random_bytes(8));
        mkdir($dir . '/lang/customizing', 0777, true);
        return $dir;
    }

    /**
     * @param list<array{0: string, 1: string, 2: string}> $entries module, identifier, value
     */
    private function writeLangFile(string $path, array $entries): void
    {
        $lines = ["<!-- language file start -->"];
        foreach ($entries as [$module, $identifier, $value]) {
            $lines[] = "{$module}#:#{$identifier}#:#{$value}";
        }
        file_put_contents($path, implode("\n", $lines) . "\n");
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
