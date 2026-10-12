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

class LanguageInstallationManagerMigratedDroppedKeysTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    /**
     * A no-longer-shipped entry whose local value equals the previously shipped one must be dropped from lng_data,
     * lng_modules and the overlay (resolveMigratedModule()'s local-entries loop); via an update.
     */
    public function testAKeyDroppedFromTheShippedPoIsRemovedWhenItsLocalValueMatchesTheOriginal(): void
    {
        $this->shipPo(['farewell' => 'Tschüss']); // 'greeting' is no longer shipped
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Hallo']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertArrayNotHasKey('greeting', $this->lngModules()[self::MODULE]);
        $this->assertSame(['greeting'], $this->droppedIdentifiers(self::MODULE));
        // nothing local is left, so the overlay is removed completely
        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'dropped from the overlay as well');
    }

    /**
     * A real local change must survive its key disappearing from the shipped `.po`.
     */
    public function testAKeyDroppedFromTheShippedPoButWithARealLocalChangeIsKept(): void
    {
        $this->shipPo(['farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => [
            'value' => 'Servus',
            'original' => 'Hallo',
            'local_change' => '2025-05-05T05:05:05Z',
        ]]);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Servus']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Servus', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertSame([], $this->droppedIdentifiers(self::MODULE), 'a real local change is never dropped');
        $this->assertSame('Servus', $this->overlayPo()->find(null, 'greeting')->getTranslation());
    }

    /**
     * A corrupt overlay must not be treated as L === O: no three-way decision is possible, so the entry stays.
     */
    public function testAKeyDroppedFromTheShippedPoWithACorruptOverlayIsKept(): void
    {
        $this->expectErrorLog();
        $this->shipPo(['farewell' => 'Tschüss']);
        $this->seedOverlay(['farewell' => ['value' => 'Tschüss', 'original' => 'Tschüss']]);
        file_put_contents($this->overlayBase() . '.po', "msgid \"kaputt\n");
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Hallo']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Hallo', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertSame([], $this->droppedIdentifiers(self::MODULE));
    }

    /**
     * A customizing value equal to the previously shipped one must not be dropped as an unchanged entry once its
     * key disappears from the shipped `.po`.
     */
    public function testACustomizingValueForAKeyDroppedFromTheShippedPoStaysLocal(): void
    {
        $this->shipPo(['farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Hallo']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Hallo', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertSame(['value' => 'Hallo', 'local_change' => self::NOW], $this->lngData()['pilot|greeting']);
        $this->assertSame([], $this->droppedIdentifiers(self::MODULE));
    }

    /**
     * MigratedLanguageFileSync::sync() must preserve "original" for an entry the `.po` currently lacks; without O,
     * the three-way decision once the key ships again would keep the stale local value.
     */
    public function testAKeyThatReturnsAfterBeingUnshippedUsesThePreservedOriginalOnUpdate(): void
    {
        $this->shipPo(['farewell' => 'Tschüss']); // 'greeting' not shipped this run
        $this->seedOverlay(['greeting' => [
            'value' => 'Angepasst',
            'original' => 'Hallo',
            'local_change' => '2025-05-05T05:05:05Z',
        ]]);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Angepasst']];

        $this->manager()->insertLanguageForInstallation('de');
        $this->assertSame(
            'Angepasst',
            $this->lngModules()[self::MODULE]['greeting'],
            'sanity: kept in run 1 because L != O'
        );

        $this->queries = [];
        $this->shipPo(['greeting' => 'Neu', 'farewell' => 'Tschüss']); // the key ships again, with a new value
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Hallo']]; // reverted to the (preserved) original

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(
            'Neu',
            $this->lngModules()[self::MODULE]['greeting'],
            'L === preserved O, only S changed -> S wins, thanks to the original surviving the unshipped run'
        );
        $this->assertSame(['value' => 'Neu', 'local_change' => null], $this->lngData()['pilot|greeting']);
    }

    /**
     * The whole shipped `.po` missing and later back: meanwhile the module is not migrated and falls back to the
     * plain `.lang` file, and the overlay stays byte-for-byte untouched even though an update runs.
     */
    public function testAWholeMissingShippedPoIsBridgedByThePreservedOverlayAcrossTwoUpdates(): void
    {
        $this->seedOverlay([
            'A' => ['value' => 'A1', 'original' => 'A1'],
            'B' => ['value' => 'B1', 'original' => 'B1'],
            'C' => ['value' => 'C_custom', 'original' => 'C1', 'local_change' => '2025-01-01T00:00:00Z'],
        ]);
        $po_before_the_gap = file_get_contents($this->overlayBase() . '.po');
        $mo_before_the_gap = file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase()));
        // no shipped `.po` for the whole gap: the module falls back to its plain `.lang` file
        $this->writeLang('components/pilot/lang/ilias_de.lang', ['A#:#A1', 'B#:#B1', 'C#:#C_custom']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(
            ['A' => 'A1', 'B' => 'B1', 'C' => 'C_custom'],
            $this->lngModules()[self::MODULE],
            'sanity: the .lang fallback is used while the .po is missing'
        );
        $this->assertSame(
            ['value' => 'C_custom', 'local_change' => null],
            $this->lngData()['pilot|C'],
            'the DB no longer sees C as a local change while the module is not migrated'
        );
        $this->assertSame($po_before_the_gap, file_get_contents($this->overlayBase() . '.po'), 'the overlay is untouched during the gap');
        $this->assertSame($mo_before_the_gap, file_get_contents(MigratedPoFixture::overlayMo($this->overlayBase())), 'the overlay is untouched during the gap');

        // the module is migrated again - A's shipped value changed, B's did not, C is not shipped
        // with the (never-shipped) customized value at all
        $this->queries = [];
        $this->shipPo(['A' => 'A2', 'B' => 'B1', 'C' => 'C1']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(
            ['C' => 'C_custom', 'A' => 'A2', 'B' => 'B1'],
            $this->lngModules()[self::MODULE]
        );
        $this->assertSame(['value' => 'A2', 'local_change' => null], $this->lngData()['pilot|A'], 'L === preserved O -> S wins');
        $this->assertSame(['value' => 'B1', 'local_change' => null], $this->lngData()['pilot|B'], 'unchanged');
        $this->assertSame(
            ['value' => 'C_custom', 'local_change' => '2025-01-01 00:00:00'],
            $this->lngData()['pilot|C'],
            'the overlay-only local change is taken over again - the known limitation above'
        );
        // A and B carry the shipped values and leave the full legacy overlay, only the local change C stays
        $overlay = $this->overlayPo();
        $this->assertNull($overlay->find(null, 'A'));
        $this->assertNull($overlay->find(null, 'B'));
        $this->assertSame('A2', $this->effective()['A']['value']);
        $this->assertSame('C_custom', $overlay->find(null, 'C')->getTranslation());
        $this->assertNotNull(LocalChangeComments::getLocalChange($overlay->find(null, 'C')));
    }
}
