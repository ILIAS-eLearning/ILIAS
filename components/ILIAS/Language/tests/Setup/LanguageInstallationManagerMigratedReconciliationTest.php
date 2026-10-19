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

class LanguageInstallationManagerMigratedReconciliationTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    /**
     * @param ?string $local L from the database seed (null = no local change)
     * @param ?string $original O, the overlay's "original" (null = no overlay entry)
     */
    #[DataProvider('threeWayCases')]
    public function testThreeWayReconciliationOfADatabaseLocalChange(
        ?string $local,
        ?string $original,
        string $shipped,
        string $expected_value,
        bool $expected_row_written_as_shipped
    ): void {
        $this->shipPo(['greeting' => $shipped]);
        if ($original !== null) {
            $this->seedOverlay(['greeting' => [
                'value' => $local ?? $original,
                'original' => $original,
                'local_change' => $local !== null && $local !== $original ? '2025-05-05T05:05:05Z' : null,
            ]]);
        }
        if ($local !== null) {
            $this->db_local_changes = [self::MODULE => ['greeting' => $local]];
        }

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame($expected_value, $this->lngModules()[self::MODULE]['greeting']);
        if ($expected_row_written_as_shipped) {
            $this->assertSame(['value' => $shipped, 'local_change' => null], $this->lngData()['pilot|greeting']);
        } elseif ($original !== null) {
            // with an overlay it is the only source: the row is written from it, never read from lng_data
            $this->assertSame($expected_value, $this->lngData()['pilot|greeting']['value']);
        } else {
            $this->assertArrayNotHasKey('pilot|greeting', $this->lngData(), 'the local lng_data row is kept as it is');
        }
        // S is served from the shipped .po; only a winning L stays in the overlay, with "original" moved to S
        $this->assertSame($expected_value, $this->effective()['greeting']['value']);
        if ($expected_value === $shipped) {
            $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'no local change - no overlay');
            return;
        }
        $overlay_entry = $this->overlayPo()->find(null, 'greeting');
        $this->assertSame($expected_value, $overlay_entry->getTranslation());
        $this->assertSame($shipped, LocalChangeComments::getOriginal($overlay_entry), 'original always moves to S');
        $this->assertNotNull(LocalChangeComments::getLocalChange($overlay_entry));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function threeWayCases(): array
    {
        return [
            'no L -> S' => [null, 'Alt', 'Neu', 'Neu', true],
            'no L, no overlay -> S' => [null, null, 'Neu', 'Neu', true],
            'L === S -> S, no longer local' => ['Neu', 'Alt', 'Neu', 'Neu', true],
            'L === O, only S changed -> S' => ['Alt', 'Alt', 'Neu', 'Neu', true],
            'L differs from O and S -> L wins' => ['Eigen', 'Alt', 'Neu', 'Eigen', false],
            'L differs, S unchanged -> L wins' => ['Eigen', 'Alt', 'Alt', 'Eigen', false],
            'L without overlay -> L wins' => ['Eigen', null, 'Neu', 'Eigen', false],
        ];
    }

    /**
     * A customizing-file value always stays local, even when it equals the previously shipped value.
     */
    public function testAValueFromTheCustomizingFileAlwaysStaysLocal(): void
    {
        $this->shipPo(['greeting' => 'Neu']);
        $this->seedOverlay(['greeting' => ['value' => 'Alt', 'original' => 'Alt']]);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Alt']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => 'Alt', 'local_change' => self::NOW], $this->lngData()['pilot|greeting']);
        $this->assertSame('Alt', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertNotNull(LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'greeting')));
    }

    /**
     * The overlay holds only the identifiers the customizing file mentions, not other shipped entries of the module.
     */
    public function testACustomizingFileOnlyPutsItsOwnKeysIntoTheDelta(): void
    {
        $this->shipPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Aus Customizing']);

        $this->manager()->insertLanguageForInstallation('de');

        $overlay_identifiers = array_map(
            static fn($entry): string => $entry->getId(),
            $this->overlayPo()->getEntries()
        );
        $this->assertSame(['greeting'], $overlay_identifiers);
        $this->assertSame('Tschüss', $this->lngModules()[self::MODULE]['farewell']);
    }

    /**
     * A customizing entry with markup that TranslationMarkupPolicy disallows and that really changes something is
     * left out and reported via getSkippedInvalidMarkupEntries(); the rest of the run is installed. Uses the
     * non-migrated module "common": the check is independent of the PO/MO pilot.
     */
    public function testACustomizingEntryWithDisallowedMarkupIsSkippedButTheRestIsInstalled(): void
    {
        $this->expectManagerWarning();
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja', 'common#:#bad#:#Shipped harmless']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', [
            'common#:#yes#:#Jawohl',
            'common#:#bad#:#<script>evil</script>',
        ]);

        $manager = $this->manager();
        $unwritten = $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $unwritten, 'a skipped customizing entry must not itself count as an unwritten overlay');
        $this->assertSame('Jawohl', $this->lngModules()['common']['yes'], 'the valid customizing entry is applied');
        $this->assertSame(
            'Shipped harmless',
            $this->lngModules()['common']['bad'],
            'the invalid customizing entry is left out - the shipped value stays in effect'
        );
        $skipped = $manager->getSkippedInvalidMarkupEntries();
        $this->assertArrayHasKey('de', $skipped);
        $this->assertArrayHasKey('common#:#bad', $skipped['de']);
        $this->assertNotEmpty($skipped['de']['common#:#bad']);
        $this->assertArrayNotHasKey('common#:#yes', $skipped['de']);
    }

    /**
     * getSkippedInvalidMarkupEntries() reports only what the LAST insertLanguage...() call left out; a fixed or
     * removed customizing file must empty the report again.
     */
    public function testGetSkippedInvalidMarkupEntriesIsResetOnEveryInsertLanguageCall(): void
    {
        $this->expectManagerWarning();
        $this->writeLang('lang/ilias_de.lang', ['common#:#bad#:#Shipped harmless']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#bad#:#<script>evil</script>']);
        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');
        $this->assertNotEmpty($manager->getSkippedInvalidMarkupEntries()['de'] ?? []);

        // the customizing file is fixed (no more invalid markup) and the language is installed again
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['common#:#bad#:#Jetzt gültig']);
        $manager->insertLanguageForInstallation('de');

        $this->assertArrayNotHasKey('de', $manager->getSkippedInvalidMarkupEntries());
        $this->assertSame('Jetzt gültig', $this->lngModules()['common']['bad']);
    }

    /**
     * An overlay-only local change (e.g. lng_data lost) is taken over on install/update, with its overlay timestamp.
     */
    public function testAnOverlayOnlyLocalChangeIsTakenOverOnInstall(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z'],
            'added_locally' => ['value' => 'Eigene Variable', 'local_change' => '2025-06-06T06:06:06Z'],
            'orphan' => ['value' => 'Weder geshippt noch lokal', 'original' => 'Weder geshippt noch lokal'],
        ]);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => 'Servus', 'local_change' => '2025-05-05 05:05:05'], $this->lngData()['pilot|greeting']);
        $this->assertSame(
            ['value' => 'Eigene Variable', 'local_change' => '2025-06-06 06:06:06'],
            $this->lngData()['pilot|added_locally']
        );
        $this->assertSame(
            ['greeting' => 'Servus', 'added_locally' => 'Eigene Variable'],
            $this->lngModules()[self::MODULE]
        );
        $this->assertNull($this->overlayPo()->find(null, 'orphan'), 'neither shipped nor local: dropped');
        $this->assertSame(
            '2025-05-05T05:05:05Z',
            LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'greeting')),
            'the original timestamp of the local change survives'
        );
    }

    /**
     * An overlay local change with an unparsable timestamp is still a local change, recorded with the current time.
     */
    public function testAnOverlayOnlyLocalChangeWithAnUnparsableTimestampIsRecordedWithTheCurrentTime(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay([
            'greeting' => ['value' => 'Servus', 'original' => 'Hallo', 'local_change' => 'yesterday'],
        ]);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => 'Servus', 'local_change' => self::NOW], $this->lngData()['pilot|greeting']);
    }

    /**
     * An entry entering the overlay with this run keeps the time of its local change from lng_data.local_change (UTC).
     */
    public function testANewOverlayEntryCarriesTheLocalChangeDateOfTheDatabaseRow(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Servus']];
        $this->local_change_rows = [
            ['module' => self::MODULE, 'identifier' => 'greeting', 'local_change' => '2024-03-04 05:06:07'],
        ];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Servus', $this->overlayPo()->find(null, 'greeting')?->getTranslation());
        $this->assertSame(
            '2024-03-04T05:06:07Z',
            LocalChangeComments::getLocalChange($this->overlayPo()->find(null, 'greeting'))
        );
        $this->assertNotSame(
            self::NOW,
            LocalChangeComments::getLocalChangeAsDatabaseTimestamp($this->overlayPo()->find(null, 'greeting'))
        );
    }

    public function testALocalEntryWithoutShippedCounterpartIsKept(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['custom' => 'Eigene Variable']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['custom' => 'Eigene Variable', 'greeting' => 'Hallo'], $this->lngModules()[self::MODULE]);
        $custom = $this->overlayPo()->find(null, 'custom');
        $this->assertNull(LocalChangeComments::getOriginal($custom));
        $this->assertNotNull(LocalChangeComments::getLocalChange($custom));
    }

    /**
     * An overlay entry marked as merged holds the shipped value now: a new shipped value wins and the entry leaves
     * the overlay.
     */
    public function testANewShippedValueWinsOverAnOverlayEntryMarkedAsMerged(): void
    {
        $this->shipPo(['greeting' => 'Grüß Gott']);
        $this->seedOverlayMarkedAsMerged(['greeting' => 'Servus'], ['greeting']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => 'Grüß Gott', 'local_change' => null], $this->lngData()['pilot|greeting']);
        $this->assertSame('Grüß Gott', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'no overlay left');
    }

    public function testWithoutTheMarkTheLocalValueStaysALocalChange(): void
    {
        $this->shipPo(['greeting' => 'Grüß Gott']);
        $this->seedOverlayMarkedAsMerged(['greeting' => 'Servus'], []);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Servus', $this->lngData()['pilot|greeting']['value']);
        $this->assertNotNull($this->lngData()['pilot|greeting']['local_change']);
    }

    public function testAMarkedEntryEqualToTheShippedValueIsNoLocalChange(): void
    {
        $this->shipPo(['greeting' => 'Servus']);
        $this->seedOverlayMarkedAsMerged(['greeting' => 'Servus'], ['greeting']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => 'Servus', 'local_change' => null], $this->lngData()['pilot|greeting']);
    }

    /**
     * The mark of a plural message holds for all its forms.
     */
    public function testNewShippedFormsWinOverAPluralMessageMarkedAsMerged(): void
    {
        $this->shipPluralPo(['item' => ['Eintrag', 'Einträge']], self::TWO_FORMS);
        $this->writeLocalOverlay(['item [0]' => 'Servus-Eintrag', 'item [1]' => 'Servus-Einträge']);
        $overlay = $this->overlayPo();
        LocalChangeComments::setMergedIntoShipped($overlay->find(null, 'item') ?? $overlay->find(self::MODULE, 'item'), true);
        MigratedPoFixture::writePair($this->overlayBase(), $overlay);
        $this->shipPluralPo(['item' => ['Neu-Eintrag', 'Neu-Einträge']], self::TWO_FORMS);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => 'Neu-Einträge', 'local_change' => null], $this->lngData()['pilot|item']);
        $this->assertFileDoesNotExist($this->overlayBase() . '.po', 'no overlay left');
    }

    /**
     * With a multi-line shipped value the overlay "original" (O) is read back losslessly, so L equal to O counts as
     * "only S changed" and S wins; a flattened O would keep L as a false local change.
     */
    public function testAMultiLineLocalValueEqualToTheOriginalIsReplacedByTheNewShippedValue(): void
    {
        $this->shipPo(['greeting' => "Neu\nZeile 2"]);
        $this->seedOverlay(['greeting' => ['value' => "Alt\r\nZeile 2", 'original' => "Alt\r\nZeile 2"]]);
        $this->db_local_changes = [self::MODULE => ['greeting' => "Alt\r\nZeile 2"]];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame("Neu\nZeile 2", $this->lngModules()[self::MODULE]['greeting']);
        $this->assertSame(['value' => "Neu\nZeile 2", 'local_change' => null], $this->lngData()['pilot|greeting']);
        // S wins, so the legacy overlay entry is removed
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFalse($this->effective()['greeting']['local_change']);
    }

    /**
     * An unchanged multi-line shipped value is no local change after an update, in lng_data and overlay, also on a
     * second run.
     */
    public function testAnUnchangedMultiLineShippedValueStaysUnchangedAcrossUpdates(): void
    {
        $this->shipPo(['greeting' => "Zeile 1\nZeile 2 mit C:\\pfad"]);

        $this->manager()->insertLanguageForInstallation('de');
        $this->queries = [];
        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame(['value' => "Zeile 1\nZeile 2 mit C:\\pfad", 'local_change' => null], $this->lngData()['pilot|greeting']);
        // recognised as the shipped value: no overlay on either run
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
        $this->assertFalse($this->effective()['greeting']['local_change']);
    }

    /**
     * A corrupt overlay `.po` must not block an update: the module is left out (reported), its overlay stays and
     * nothing is taken from lng_data.
     */
    public function testACorruptOverlayLeavesItsModuleUnchangedOnUpdate(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo', 'farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => ['value' => 'Hallo', 'original' => 'Hallo']]);
        file_put_contents($this->overlayBase() . '.po', "msgid \"kaputt\n");
        $this->db_local_changes = [self::MODULE => ['farewell' => 'Pfiat di']];

        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');

        $this->assertArrayNotHasKey(self::MODULE, $this->lngModules());
        $this->assertArrayNotHasKey('pilot|farewell', $this->lngData());
        $this->assertSame("msgid \"kaputt\n", file_get_contents($this->overlayBase() . '.po'), 'the overlay stays as it is');
        $this->assertSame(['de' => [self::MODULE]], $manager->getSkippedUnreadableOverlays());
        $this->assertNotContains(self::MODULE, MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->root . '/client-data', 'de'));
        $this->assertStringContainsString('cannot be read: they are left unchanged', $this->loggedWarnings());
    }
}
