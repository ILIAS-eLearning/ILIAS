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

class LanguageInstallationManagerMigratedLocalChangesActionsTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    public function testRemovingLocalChangesRebuildsTheOverlayFromTheShippedPo(): void
    {
        $this->shipPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z'],
            'farewell' => ['value' => 'Tschüss', 'original' => 'Tschüss'],
            'added_locally' => ['value' => 'Eigene Variable', 'local_change' => '2025-06-06T06:06:06Z'],
        ]);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Aus Customizing']);

        $this->manager()->insertLanguageForRemovingLocalChanges('de');

        $this->assertSame(
            [
                'pilot|greeting' => ['value' => 'Hallo', 'local_change' => null],
                'pilot|farewell' => ['value' => 'Tschüss', 'local_change' => null],
            ],
            $this->lngData()
        );
        $this->assertSame(['greeting' => 'Hallo', 'farewell' => 'Tschüss'], $this->lngModules()[self::MODULE]);
        // "remove local changes" leaves no local change: the overlay is removed
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase()));
        $effective = $this->effective();
        $this->assertSame(['greeting', 'farewell'], array_keys($effective), 'the locally added variable is gone');
        $this->assertSame('Hallo', $effective['greeting']['value']);
        $this->assertFalse($effective['greeting']['local_change']);
    }

    /**
     * Applying local changes reconciles a migrated module with its shipped `.po` like an update:
     * the not locally changed entries are rewritten from the shipped `.po` (lng_data rows without
     * local_change), the customizing file goes on top as local change and the overlay follows.
     */
    public function testApplyingLocalChangesPutsTheCustomizingValueOnTopOfTheStoredEntries(): void
    {
        $this->shipPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
            'farewell' => ['value' => 'Tschüss', 'original' => 'Tschüss'],
        ]);
        $this->db_entries = [self::MODULE => ['greeting' => 'Hallo', 'farewell' => 'Tschüss'], 'common' => ['yes' => 'Ja']];
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Grüß Gott']);

        $this->manager()->insertLanguageForApplyingLocalChanges('de');

        $this->assertSame(
            [
                'pilot|greeting' => ['value' => 'Grüß Gott', 'local_change' => self::NOW],
                'pilot|farewell' => ['value' => 'Tschüss', 'local_change' => null],
            ],
            $this->lngData()
        );
        $this->assertSame(
            [self::MODULE => ['greeting' => 'Grüß Gott', 'farewell' => 'Tschüss'], 'common' => ['yes' => 'Ja']],
            $this->lngModules()
        );
        $greeting = $this->overlayPo()->find(null, 'greeting');
        $this->assertSame('Grüß Gott', $greeting->getTranslation());
        $this->assertSame('Hallo', LocalChangeComments::getOriginal($greeting));
        $this->assertNotNull(LocalChangeComments::getLocalChange($greeting));
    }

    /**
     * The pre-INSERT "DELETE FROM lng_data WHERE ... local_change IS NULL AND module IN (...)"
     * ($delete_unchanged_migrated_rows) replaces flushing first: without it a shipped key dropped from the `.po`
     * between two "apply local changes" runs keeps its stale row.
     */
    public function testApplyingLocalChangesReconcilesEveryCaseInOneSweepAndPurgesTheStaleUnchangedRow(): void
    {
        $this->shipPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss', 'obsolete' => 'Alt']);
        $this->manager()->insertLanguageForInstallation('de');
        $this->assertSame(
            ['value' => 'Alt', 'local_change' => null],
            $this->lngData()['pilot|obsolete'],
            'sanity: the row exists after the first install'
        );

        $this->shipPo(['greeting' => 'Servus', 'farewell' => 'Pfiat di']); // 'obsolete' dropped from the shipped po
        $this->seedOverlay([
            'greeting' => ['value' => 'Hallo', 'original' => 'Hallo'],
            'farewell' => ['value' => 'Auf Wiedersehen', 'original' => 'Tschüss', 'local_change' => '2025-04-04T04:04:04Z'],
            'added_locally' => ['value' => 'Eigene Var', 'local_change' => '2025-03-03T03:03:03Z'],
        ]);
        $this->db_local_changes = [self::MODULE => ['farewell' => 'Auf Wiedersehen']];
        $this->db_entries = [
            'common' => ['yes' => 'Ja', 'no' => 'Nein'],
            self::MODULE => ['greeting' => 'Hallo', 'farewell' => 'Tschüss', 'obsolete' => 'Alt'],
        ];

        $this->manager()->insertLanguageForApplyingLocalChanges('de');

        $this->assertSame(
            'Servus',
            $this->lngModules()[self::MODULE]['greeting'],
            'stored "Hallo", shipped now "Servus" -> "Servus"'
        );
        $this->assertSame(
            'Auf Wiedersehen',
            $this->lngModules()[self::MODULE]['farewell'],
            'a real local change (L != S != O) is kept'
        );
        $this->assertSame(
            'Eigene Var',
            $this->lngModules()[self::MODULE]['added_locally'],
            'an overlay-only local change is taken over'
        );
        $this->assertArrayNotHasKey('obsolete', $this->lngModules()[self::MODULE]);
        $this->assertSame(
            ['yes' => 'Ja', 'no' => 'Nein'],
            $this->lngModules()['common'],
            'a non-migrated module in the seed stays complete'
        );

        $this->assertSame(['value' => 'Servus', 'local_change' => null], $this->lngData()['pilot|greeting']);
        $this->assertSame(
            ['value' => 'Eigene Var', 'local_change' => '2025-03-03 03:03:03'],
            $this->lngData()['pilot|added_locally']
        );
        $this->assertArrayNotHasKey(
            'pilot|obsolete',
            $this->lngData(),
            'the DELETE ... local_change IS NULL bulk purge took effect: the stale unchanged row is gone'
        );
        $this->assertNotEmpty(
            array_filter(
                $this->queries,
                static fn(string $q): bool => str_starts_with($q, 'DELETE FROM lng_data')
                    && str_contains($q, 'local_change IS NULL')
                    && str_contains($q, "identifier NOT IN ('")
            ),
            'the bulk delete of unchanged migrated rows was actually issued'
        );
    }

    /**
     * Written first, purged after: an aborted write must leave the former rows, not a gap. Both
     * happen under one table lock of lng_data.
     */
    public function testApplyingLocalChangesWritesTheRowsBeforeItPurgesTheStaleOnes(): void
    {
        $this->shipPo(['greeting' => 'Hallo', 'obsolete' => 'Alt']);
        $this->manager()->insertLanguageForInstallation('de');
        $this->shipPo(['greeting' => 'Servus']);
        $already_recorded = count($this->queries);

        $this->manager()->insertLanguageForApplyingLocalChanges('de');

        $statements = [];
        foreach (array_slice($this->queries, $already_recorded) as $query) {
            if (str_starts_with($query, 'INSERT INTO lng_data')) {
                $statements[] = 'write';
            } elseif (str_starts_with($query, 'DELETE FROM lng_data') && str_contains($query, 'local_change IS NULL')) {
                $statements[] = 'purge';
            }
        }
        $this->assertSame(['write', 'purge'], array_values(array_unique($statements)));
    }
}
