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
use ILIAS\Language\ComponentTranslation\Catalog\TranslationCatalog;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\LanguageDataNotSavedException;
use ILIAS\Language\Setup\LanguageInstallationManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LanguageInstallationManagerMigratedAfterWriteTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    public function testAModuleArrayThatCannotBeReadBackThrowsLanguageDataNotSaved(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->collation_rows = [
            ['module' => 'common', 'lang_array' => serialize(['yes' => 'Ja'])],
            ['module' => self::MODULE, 'lang_array' => 'a:1:{s:8:"greeting";s:5:"Hal'],
        ];
        $invalidated = [];
        $cwd = getcwd();

        try {
            $this->manager(null, static function (string $lang_key) use (&$invalidated): void {
                $invalidated[] = $lang_key;
            })->insertLanguageForInstallation('de');
            $this->fail('Expected a LanguageDataNotSavedException');
        } catch (LanguageDataNotSavedException $e) {
            $this->assertSame(409, $e->getCode());
            $this->assertStringContainsString("'pilot'", $e->getMessage());
            $this->assertStringContainsString("'de'", $e->getMessage());
        }

        $this->assertSame([], $invalidated, 'no cache invalidation for data that was not saved correctly');
        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'no overlay for data that was not saved correctly');
        $this->assertSame($cwd, getcwd(), 'the working directory is restored on the exception path');
        // counts only the collation query of the lng_modules write; migratedModuleRemarks() issues its own lng_data SELECT beforehand
        $collation_query = array_values(array_filter(
            $this->queries,
            static fn(string $q): bool => str_starts_with($q, 'SELECT') && str_contains($q, 'lang_array') && str_contains($q, 'lng_modules')
        ));
        $this->assertCount(1, $collation_query);
        $this->assertStringContainsString("module IN ('pilot')", $collation_query[0]);
    }

    public function testASerializedFalseIsAlsoDetectedAsNotSaved(): void
    {
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->collation_rows = [['module' => 'common', 'lang_array' => serialize(false)]];

        $this->expectException(LanguageDataNotSavedException::class);
        $this->manager()->insertLanguageForInstallation('de');
    }

    public function testTheLanguageCacheIsInvalidatedWithTheLanguageKeyBeforeTheOverlayIsSynced(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $overlay_po = $this->overlayBase() . '.po';
        $calls = [];

        $this->manager(null, function (string $lang_key) use (&$calls, $overlay_po): void {
            $calls[] = [$lang_key, count(array_filter($this->queries, static fn(string $q): bool => str_starts_with($q, /** @lang text */ 'INSERT INTO lng_modules'))), is_file($overlay_po)];
        })->insertLanguageForInstallation('de');

        $this->assertSame([['de', 1, false]], $calls, 'once, after the lng_modules write, before the overlay sync');
    }

    public function testAFailingCacheInvalidatorIsLoggedAndSwallowed(): void
    {
        $this->expectErrorLog();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);

        $this->manager(null, static function (): void {
            throw new RuntimeException('cache is down');
        })->insertLanguageForInstallation('de');

        $this->assertSame(['greeting' => 'Hallo'], $this->lngModules()[self::MODULE]);
        // a shipped value writes no file; the sync still running shows in the removal of an outdated overlay
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()), 'the overlay sync still ran: it removed the outdated overlay');
        $this->assertStringContainsString('cache is down', $this->errorLog());
    }

    /**
     * Root-proof overlay failure (a file where the overlay's "lang" directory must be created): logged,
     * the database write stays.
     */
    public function testAnOverlaySyncFailureIsLoggedAndTheDatabaseWriteStays(): void
    {
        $this->expectErrorLog();
        $this->shipPo(['greeting' => 'Hallo']);
        // a local change: only then an overlay has to be written (delta)
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Servus']];
        rmdir($this->root . '/client-data');
        mkdir($this->root . '/client-data');
        file_put_contents($this->root . '/client-data/lang', 'not a directory');

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $unwritten = $this->manager()->insertLanguageForInstallation('de');
        } finally {
            restore_error_handler();
        }

        $this->assertSame(['greeting' => 'Servus'], $this->lngModules()[self::MODULE]);
        $this->assertStringContainsString('Could not sync migrated language file for module "pilot"', $this->errorLog());
        $this->assertSame([self::MODULE], $unwritten, 'the failed module is returned so callers can report it');
    }

    /**
     * The same failure is returned by all three write paths - and only the migrated module whose
     * overlay failed is listed, never a non-migrated one written in the same run.
     */
    #[DataProvider('writePaths')]
    public function testEveryWritePathReturnsOnlyTheModulesWhoseOverlayCouldNotBeWritten(string $method, bool $writes_overlay): void
    {
        if ($writes_overlay) {
            $this->expectErrorLog();
        }
        $this->shipPo(['greeting' => 'Hallo']);
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Servus']);
        $this->db_entries = [self::MODULE => ['greeting' => 'Hallo'], 'common' => ['yes' => 'Ja']];
        file_put_contents($this->root . '/client-data/lang', 'not a directory');

        set_error_handler(static fn(): bool => true, E_WARNING);
        try {
            $unwritten = $this->manager()->$method('de');
        } finally {
            restore_error_handler();
        }

        // "remove local changes" leaves only shipped values: no overlay to write, nothing can fail
        $this->assertSame($writes_overlay ? [self::MODULE] : [], $unwritten);
    }

    #[DataProvider('writePaths')]
    public function testEveryWritePathReturnsNothingWhenTheOverlayWasWritten(string $method, bool $writes_overlay): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Servus']);
        $this->db_entries = [self::MODULE => ['greeting' => 'Hallo'], 'common' => ['yes' => 'Ja']];

        $this->assertSame([], $this->manager()->$method('de'));
        // only a path that keeps the customizing value writes an overlay
        $this->assertSame($writes_overlay, is_file(MigratedPoFixture::overlayMo($this->overlayBase())));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function writePaths(): array
    {
        return [
            'install/update' => ['insertLanguageForInstallation', true],
            'remove local changes' => ['insertLanguageForRemovingLocalChanges', false],
            'apply local changes' => ['insertLanguageForApplyingLocalChanges', true],
        ];
    }

    /**
     * No overlay is maintained without client data directory - that is not a failure to report.
     */
    public function testWithoutClientDataDirNothingIsReturnedAsUnwritten(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);

        $this->assertSame([], $this->manager(static fn(): ?string => null)->insertLanguageForInstallation('de'));
    }

    /**
     * Checked against the client data directory the instance maintains the overlay in (resolved at
     * call time), for the migrated modules of the given languages only.
     */
    public function testFindUnwritableOverlayDirectoriesChecksTheResolvedClientDataDir(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $blocked = $this->root . '/blocked';
        mkdir($blocked);
        file_put_contents($blocked . '/lang', 'not a directory');
        $target = $this->root . '/client-data';
        $manager = $this->manager(static function () use (&$target): ?string {
            return $target;
        });

        $this->assertSame([], $manager->findUnwritableOverlayDirectories(['de']));

        $target = $blocked;
        $this->assertSame([$blocked . '/lang/pilot/de'], $manager->findUnwritableOverlayDirectories(['de']));
        $this->assertSame([], $manager->findUnwritableOverlayDirectories(['en']), 'pilot is not migrated for en');

        $target = null;
        $this->assertSame([], $manager->findUnwritableOverlayDirectories(['de']));
    }

    public function testTheResolversClientDataDirIsUsedAndResolvedAtCallTime(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $elsewhere = $this->root . '/elsewhere';
        mkdir($elsewhere);
        $target = null;
        $manager = $this->manager(static function () use (&$target): ?string {
            return $target;
        });

        $target = $elsewhere;
        // a local change, so there is an overlay to write (delta)
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Servus']];
        $manager->insertLanguageForInstallation('de');

        $this->assertFileExists(MigratedPoFixture::overlayMo($elsewhere . '/lang/pilot/de/pilot_de'));
        $this->assertDirectoryDoesNotExist($this->root . '/client-data/lang');
    }

    #[DataProvider('noClientDataDir')]
    public function testWithoutClientDataDirTheDatabaseIsWrittenButNoOverlay(bool $with_resolver): void
    {
        $this->shipPo(['greeting' => 'Hallo']);

        $this->manager(static fn(): ?string => null, null, $with_resolver)->insertLanguageForInstallation('de');

        $this->assertSame(['greeting' => 'Hallo'], $this->lngModules()[self::MODULE]);
        $this->assertDirectoryDoesNotExist($this->root . '/client-data/lang');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function noClientDataDir(): array
    {
        return ['resolver returns null' => [true], 'no resolver' => [false]];
    }

    /**
     * Shipped ilias_fa.lang/ilias_nl.lang contain duplicates: the first occurrence wins, the update
     * does not abort, and the duplicates are reported.
     */
    public function testADuplicateInOneFileKeepsTheFirstOccurrenceAndIsReported(): void
    {
        $this->expectErrorLog();
        $this->writeLang('lang/ilias_de.lang', [
            'common#:#yes#:#Ja',
            'common#:#yes#:#Doppelt',
            'common#:#no#:#Nein',
        ]);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['yes' => 'Ja', 'no' => 'Nein'], $this->lngModules()['common']);
        $this->assertSame(['value' => 'Ja', 'local_change' => null], $this->lngData()['common|yes']);
        $this->assertStringContainsString('Duplicate language entries for language "de"', $this->errorLog());
        $this->assertStringContainsString('common#:#yes', $this->errorLog());
    }

    public function testTheSameEntryInTwoDifferentFilesIsNotADuplicate(): void
    {
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#yes#:#Jawohl']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['yes' => 'Jawohl'], $this->lngModules()['common']);
        $this->assertStringNotContainsString('Duplicate', $this->errorLog());
    }
}
