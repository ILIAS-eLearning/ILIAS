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

use ILIAS\Language\ComponentTranslation\LocalChangeComments;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The lng_data local changes of a migrated module are taken over into its overlay once, recorded in
 * `<client data dir>/lang/taken-over.<lang>.json`; with an overlay (or the record) it is their only source. A module
 * whose overlay cannot be read is left out of the run completely.
 */
class LanguageInstallationManagerMigratedTakeOverTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    /**
     * @return list<string>
     */
    private function takenOver(): array
    {
        return MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->root . '/client-data', 'de');
    }

    private function takenOverFile(): string
    {
        return MigratedLanguageFilePaths::takenOverFile($this->root . '/client-data', 'de');
    }

    private function corruptOverlay(): void
    {
        file_put_contents($this->overlayBase() . '.po', "msgid \"kaputt\n");
    }

    public function testTheLngDataLocalChangesAreTakenOverOnceAndNeverReadAgain(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Eigen', $this->overlayPo()->find(null, 'greeting')->getTranslation());
        $this->assertSame([self::MODULE], $this->takenOver());

        $this->db_local_changes = [self::MODULE => ['greeting' => 'Danach in lng_data geändert']];
        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Eigen', $this->effective()['greeting']['value'], 'lng_data is no source any more');
    }

    /** Taken over means "from now on only the overlay": also if there was nothing to take over and so no overlay. */
    public function testAModuleRecordedWithoutAnOverlayNeverReadsLngDataAgain(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->manager()->insertLanguageForInstallation('de');
        $this->assertSame([self::MODULE], $this->takenOver());
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');

        $this->db_local_changes = [self::MODULE => ['greeting' => 'Später in lng_data']];
        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Hallo', $this->effective()['greeting']['value']);
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');
    }

    public function testAReadableOverlayWithoutTheRecordWinsOverLngData(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Overlay', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z']]);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Datenbank']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Overlay', $this->effective()['greeting']['value']);
        $this->assertSame([self::MODULE], $this->takenOver());
    }

    public function testWithoutAClientDataDirectoryLngDataIsUsedAsBeforeAndNothingIsRecorded(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];

        $this->manager(static fn(): ?string => null)->insertLanguageForInstallation('de');

        $this->assertSame('Eigen', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertFileDoesNotExist($this->takenOverFile());
        $this->assertDirectoryDoesNotExist($this->root . '/client-data/lang');
        $this->assertSame('', $this->loggedWarnings(), 'not even a failed attempt to record');
    }

    /** A file where the overlay directory must be: the sync fails, so the rows must be taken over again next time. */
    public function testAModuleWhoseOverlayCouldNotBeWrittenIsNotRecorded(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->shipSecondModule(['farewell' => 'Tschüss']);
        mkdir($this->root . '/client-data/lang', 0775, true);
        file_put_contents($this->root . '/client-data/lang/pilot', 'not a directory');
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];

        $unwritten = $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame([self::MODULE], $unwritten);
        $this->assertSame(['pilot2'], $this->takenOver());
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function unusableRecords(): array
    {
        return [
            'not JSON' => ['{kaputt'],
            'an object' => ['{"a":"pilot"}'],
            'only invalid entries' => ['["Bad Name", 5, "../x"]'],
        ];
    }

    #[DataProvider('unusableRecords')]
    public function testAnUnusableRecordCountsAsEmptyAndTheRowsAreTakenOverAgain(string $record): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        mkdir($this->root . '/client-data/lang', 0775, true);
        file_put_contents($this->takenOverFile(), $record);
        $this->assertSame([], $this->takenOver());
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Eigen', $this->effective()['greeting']['value']);
        $this->assertSame([self::MODULE], $this->takenOver());
    }

    public function testUninstallingTheLanguageForgetsTheRecordAndAReinstallTakesTheRowsOverAgain(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];
        $this->manager()->insertLanguageForInstallation('de');
        $this->assertFileExists($this->takenOverFile());

        $this->available_languages = ['de' => ['obj_id' => 3, 'status' => 'installed']];
        $this->manager()->installLanguages([]);

        $this->assertFileDoesNotExist($this->takenOverFile());
        $this->assertFileDoesNotExist($this->overlayBase() . '.po');

        $this->db_local_changes = [self::MODULE => ['greeting' => 'Wieder in lng_data']];
        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Wieder in lng_data', $this->effective()['greeting']['value']);
    }

    public function testAModuleOfNoMigrationIsStillReadFromLngDataAndNotRecorded(): void
    {
        $this->writeLang('lang/ilias_de.lang', ['common#:#yes#:#Ja']);
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = ['common' => ['yes' => 'Jawohl']];

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame('Jawohl', $this->lngModules()['common']['yes']);
        $this->assertSame([self::MODULE], $this->takenOver());
    }

    /**
     * @return array<string, array{0: string, 1: string}> the local change date of the overlay entry, the value served
     */
    public static function overlayDates(): array
    {
        return [
            'newer than the customizing file' => ['2030-01-01T00:00:00Z', 'Overlay'],
            'older than the customizing file' => ['2000-01-01T00:00:00Z', 'Customizing'],
            'without a usable date' => ['kein Datum', 'Overlay'],
        ];
    }

    /** The overlay is the only source, so its local change overrules the customizing file unless it is older; without a date it counts as newer. */
    #[DataProvider('overlayDates')]
    public function testALocalChangeOfTheOverlayOverrulesTheCustomizingFileUnlessItIsOlder(string $date, string $expected): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Overlay', 'original' => 'Hallo', 'local_change' => $date]]);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Customizing']);

        $this->manager()->insertLanguageForInstallation('de');

        $this->assertSame($expected, $this->effective()['greeting']['value']);
    }

    /** A module with a customizing entry left out for its markup is not recorded: it is reported again and recorded only once the entry is fixed. */
    public function testAModuleWithAMarkupRejectedCustomizingEntryIsNotRecordedUntilItIsFixed(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->shipSecondModule(['farewell' => 'Tschüss']);
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#<script>evil</script>']);
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['pilot2'], $this->takenOver());
        $this->assertArrayHasKey('pilot#:#greeting', $manager->getSkippedInvalidMarkupEntries()['de'] ?? []);

        $manager->insertLanguageForInstallation('de');

        $this->assertArrayHasKey('pilot#:#greeting', $manager->getSkippedInvalidMarkupEntries()['de'] ?? [], 'reported again');
        $this->assertSame(['pilot2'], $this->takenOver());

        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Jetzt gültig']);
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([self::MODULE, 'pilot2'], $this->takenOver());
    }

    /** An unreadable overlay: nothing of the module is written - no lng_data, no lng_modules, not its customizing entry. */
    public function testAModuleWithAnUnreadableOverlayIsLeftOutAndTheOthersAreInstalledNormally(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->shipSecondModule(['farewell' => 'Tschüss']);
        $this->seedOverlay(['greeting' => ['value' => 'Overlay', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z']]);
        $this->corruptOverlay();
        $this->writeLang('lang/customizing/ilias_de.lang.local', ['pilot#:#greeting#:#Customizing', 'pilot2#:#farewell#:#Pfiat di']);

        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['de' => [self::MODULE]], $manager->getSkippedUnreadableOverlays());
        $this->assertArrayNotHasKey(self::MODULE, $this->lngModules());
        $this->assertArrayNotHasKey('pilot|greeting', $this->lngData());
        $this->assertSame("msgid \"kaputt\n", file_get_contents($this->overlayBase() . '.po'));
        $this->assertStringContainsString('rollback copy in lng_data/lng_modules is incomplete', $this->loggedWarnings());
        $this->assertSame('Pfiat di', $this->lngModules()['pilot2']['farewell']);
        $this->assertSame(['pilot2'], $this->takenOver());
    }

    /** Repaired, the same manager installs the module normally and no longer reports it. */
    public function testAfterTheRepairTheNextRunInstallsTheModuleAndEmptiesTheSkipList(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Overlay', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z']]);
        $this->corruptOverlay();
        $manager = $this->manager();
        $manager->insertLanguageForInstallation('de');
        $this->assertSame(['de' => [self::MODULE]], $manager->getSkippedUnreadableOverlays());

        $this->seedOverlay(['greeting' => ['value' => 'Overlay', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z']]);
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getSkippedUnreadableOverlays());
        $this->assertSame('Overlay', $this->lngModules()[self::MODULE]['greeting']);
        $this->assertSame([self::MODULE], $this->takenOver());
    }
}
