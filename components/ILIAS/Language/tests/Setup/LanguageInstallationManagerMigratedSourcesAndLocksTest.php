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

class LanguageInstallationManagerMigratedSourcesAndLocksTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    /**
     * The tos lines could be removed from lang/ilias_xx.lang: the database is filled from the
     * shipped `.po` alone.
     */
    public function testValuesOfAMigratedModuleComeFromThePoEvenWithoutAnyLangLine(): void
    {
        $this->shipPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(
            [
                'common|yes' => ['value' => 'Ja', 'local_change' => null],
                'pilot|greeting' => ['value' => 'Hallo', 'local_change' => null],
                'pilot|farewell' => ['value' => 'Tschüss', 'local_change' => null],
            ],
            $this->lngData()
        );
        $this->assertSame(['greeting' => 'Hallo', 'farewell' => 'Tschüss'], $this->lngModules()[self::MODULE]);
    }

    public function testLangLinesOfAMigratedModuleInNonLocalFilesAreIgnored(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->writeLang('lang/ilias_de.lang', ['pilot#:#greeting#:#Alt aus lang/', 'pilot#:#stale#:#Veraltet']);
        $this->writeLang('components/pilot/lang/ilias_de.lang', ['greeting#:#Alt aus Komponente']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['greeting' => 'Hallo'], $this->lngModules()[self::MODULE]);
        $this->assertArrayNotHasKey('pilot|stale', $this->lngData());
    }

    /**
     * Only languages that ship a `.po` are migrated: for another language the `.lang` lines are used.
     */
    public function testAModuleIsOnlyMigratedForLanguagesWithAShippedPo(): void
    {
        $this->shipPo(['greeting' => 'Hallo'], 'de');
        $this->writeLang('components/pilot/lang/ilias_en.lang', ['greeting#:#Hello']);

        $this->manager()->insertLanguageForInstallation('en');

        $this->assertSame(['greeting' => 'Hello'], $this->lngModules()[self::MODULE]);
        $this->assertDirectoryDoesNotExist($this->root . '/client-data/lang');
    }

    /**
     * An installation without local changes writes no overlay file: the shipped state is served as is.
     */
    public function testInstallWithoutLocalChangesWritesNoOverlay(): void
    {
        $this->shipPo(['greeting' => ['value' => 'Hello', 'fuzzy' => true]]);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertDirectoryDoesNotExist($this->root . '/client-data/lang');
        $this->assertSame(
            ['greeting' => ['value' => 'Hello', 'local_change' => false, 'local_change_date' => null, 'original' => 'Hello']],
            $this->effective()
        );
    }

    /**
     * A module that already has an overlay when insertLanguage() decides what to lock is locked
     * (MigratedLanguageFileSync::withOverlayLock()) for the whole run, not just its final sync(), so a concurrent
     * writer cannot interleave with the read-modify-write in between.
     */
    public function testAModuleWithAnExistingOverlayIsLockedForTheWholeRunNotJustItsOwnSync(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z']]);
        $lock_file = MigratedPoFixture::overlayLock($this->overlayBase());
        $held_locks = new ReflectionProperty(MigratedLanguageFileSync::class, 'held_locks');

        $observed = null;
        $manager = $this->manager(null, function () use (&$observed, $held_locks, $lock_file): void {
            $observed = array_key_exists($lock_file, $held_locks->getValue());
        });

        $manager->insertLanguageForInstallation('de');

        $this->assertTrue($observed, 'the module\'s overlay lock must already be held at this point in the run');
        // released again once the whole run is done
        $this->assertSame([], $held_locks->getValue());
    }

    /**
     * Deadlock protection: modules with an overlay are locked in one global order (module name, byte-wise),
     * whatever order getDirectories() hands them out in.
     */
    public function testLocksAreAcquiredInSortedOrderRegardlessOfTheContributedDirectoryOrder(): void
    {
        $first_module = MigratedPoFixture::directory('zmod', 'components/pilot/lang/');
        $second_module = MigratedPoFixture::directory('amod', 'components/pilot/lang/');
        MigratedPoFixture::writePo(
            $this->root . '/components/pilot/lang/zmod_de.po',
            MigratedPoFixture::catalog('zmod', ['greeting' => 'Hallo'])
        );
        MigratedPoFixture::writePo(
            $this->root . '/components/pilot/lang/amod_de.po',
            MigratedPoFixture::catalog('amod', ['greeting' => 'Hallo'])
        );
        MigratedPoFixture::writePair(
            $this->root . '/client-data/lang/zmod/de/zmod_de',
            MigratedPoFixture::catalog('zmod', ['greeting' => ['value' => 'Servus', 'original' => 'Hallo']])
        );
        MigratedPoFixture::writePair(
            $this->root . '/client-data/lang/amod/de/amod_de',
            MigratedPoFixture::catalog('amod', ['greeting' => ['value' => 'Servus', 'original' => 'Hallo']])
        );
        $held_locks = new ReflectionProperty(MigratedLanguageFileSync::class, 'held_locks');
        $observed_order = null;

        $manager = new LanguageInstallationManager(
            $this->db(),
            // "zmod" contributed before "amod", the opposite of sorted order
            new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $first_module, $second_module),
            $this->root,
            $this->repository(),
            static fn(): \DateTimeImmutable => new \DateTimeImmutable(self::NOW, new \DateTimeZone('UTC')),
            new CallbackTranslationCache(function () use (&$observed_order, $held_locks): void {
                $observed_order = array_map(
                    static fn(string $lock_file): string => basename(dirname($lock_file, 2)) . '/' . basename($lock_file),
                    array_keys($held_locks->getValue())
                );
            }),
            fn(): string => $this->root . '/client-data'
        );

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['amod/lock', 'zmod/lock'], $observed_order);
    }

    /**
     * A module without an overlay at that point is never locked (no lock file is created).
     */
    public function testAModuleWithoutAnOverlayThatGetsOneCreatedDuringTheRunIsReportedAndTheOverlaySurvives(): void
    {
        $this->expectErrorLog();
        $this->shipPo(['greeting' => 'Hallo']);
        $concurrent_edit = MigratedPoFixture::catalog(self::MODULE, [
            'greeting' => ['value' => 'Von einem parallelen Admin-Edit', 'original' => 'Hallo', 'local_change' => '2020-01-01T00:00:00Z'],
        ]);

        $manager = $this->manager(null, function () use ($concurrent_edit): void {
            MigratedPoFixture::writePair($this->overlayBase(), $concurrent_edit);
        });

        $unwritten = $manager->insertLanguageForInstallation('de');

        $this->assertSame([self::MODULE], $unwritten);
        $this->assertSame(
            'Von einem parallelen Admin-Edit',
            // untouched, so still as the fixture wrote it: with the module as msgctxt
            $this->overlayPo()->find(self::MODULE, 'greeting')->getTranslation(),
            'the concurrently-written overlay must survive untouched'
        );
    }
}
