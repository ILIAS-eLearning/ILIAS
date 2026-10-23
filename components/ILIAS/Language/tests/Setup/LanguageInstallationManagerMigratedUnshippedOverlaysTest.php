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

use ILIAS\Language\ComponentTranslation\MigratedLanguageFilePaths;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

/** getOverlaysOfUnshippedModules() and getUnrecordedTakenOverModules(): reports only, nothing is removed. */
class LanguageInstallationManagerMigratedUnshippedOverlaysTest extends LanguageInstallationManagerMigratedModulesTestCase
{
    private function clientDataDir(): string
    {
        return $this->root . '/client-data';
    }

    private function seedGhostOverlay(string $module = 'ghost', string $lang_key = 'de'): void
    {
        $directory = MigratedLanguageFilePaths::overlayDirectory($this->clientDataDir(), $module, $lang_key);
        mkdir($directory, 0775, true);
        file_put_contents($directory . '/' . $module . '_' . $lang_key . '.po', 'x');
    }

    public function testAnOverlayOfAModuleThatIsNotShippedIsReportedAndKept(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedGhostOverlay();
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['de' => ['ghost']], $manager->getOverlaysOfUnshippedModules());
        $this->assertFileExists($this->clientDataDir() . '/lang/ghost/de/ghost_de.po');
    }

    public function testAnEntryInTheRecordOfAModuleThatIsNotShippedIsReportedAndKept(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->clientDataDir(), 'de', ['ghost']);
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['de' => ['ghost']], $manager->getOverlaysOfUnshippedModules());
        $this->assertSame(['ghost', self::MODULE], MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->clientDataDir(), 'de'));
    }

    /** Record and overlay of the same module are one entry; shipped modules with overlay and record are no findings. */
    public function testShippedModulesAreNotReportedAndAModuleInRecordAndOverlayIsReportedOnce(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedOverlay(['greeting' => ['value' => 'Overlay', 'original' => 'Hallo', 'local_change' => '2025-05-05T05:05:05Z']]);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->clientDataDir(), 'de', [self::MODULE, 'zghost', 'aghost']);
        $this->seedGhostOverlay('zghost');
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['de' => ['aghost', 'zghost']], $manager->getOverlaysOfUnshippedModules());
    }

    /** Per language and per call: a module shipped for another language only is reported for the language without it. */
    public function testTheReportIsPerLanguageAndRebuiltByEveryCall(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedGhostOverlay();
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');
        $this->assertSame(['de' => ['ghost']], $manager->getOverlaysOfUnshippedModules());

        MigratedPoFixture::removeDirectory($this->clientDataDir() . '/lang/ghost');
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getOverlaysOfUnshippedModules());
    }

    public function testAMigratedModuleWithoutAShippedPoForTheLanguageIsReportedForThatLanguage(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedGhostOverlay(self::MODULE, 'fr');
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');
        $this->assertSame([], $manager->getOverlaysOfUnshippedModules());

        $manager->insertLanguageForInstallation('fr');
        $this->assertSame(['fr' => [self::MODULE]], $manager->getOverlaysOfUnshippedModules());
    }

    public function testWithoutAClientDataDirectoryNothingIsReported(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedGhostOverlay();
        $manager = $this->manager(static fn(): ?string => null);

        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getOverlaysOfUnshippedModules());
        $this->assertSame([], $manager->getUnrecordedTakenOverModules());
    }

    public function testNothingIsReportedForARegularRun(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getOverlaysOfUnshippedModules());
        $this->assertSame([], $manager->getUnrecordedTakenOverModules());
    }

    public function testAModuleWhoseTakeOverCouldNotBeRecordedIsReportedUntilItIsRecorded(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];
        $record = MigratedLanguageFilePaths::takenOverFile($this->clientDataDir(), 'de');
        mkdir($record, 0775, true);
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');

        $this->assertSame(['de' => [self::MODULE]], $manager->getUnrecordedTakenOverModules());

        rmdir($record);
        $manager->insertLanguageForInstallation('de');

        $this->assertSame([], $manager->getUnrecordedTakenOverModules());
        $this->assertSame([self::MODULE], MigratedLanguageFileSync::modulesWithLocalChangesTakenOver($this->clientDataDir(), 'de'));
    }

    /** Already recorded modules and modules without lng_data local changes are not reported. */
    public function testOnlyModulesWithLocalChangesNotYetInTheRecordAreReportedAsUnrecorded(): void
    {
        $this->expectManagerWarning();
        $this->shipPo(['greeting' => 'Hallo']);
        $this->shipSecondModule(['farewell' => 'Tschüss']);
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->clientDataDir(), 'de', [self::MODULE]);
        mkdir($this->clientDataDir() . '/lang/pilot2/de', 0775, true);
        $record = MigratedLanguageFilePaths::takenOverFile($this->clientDataDir(), 'de');
        $contents = (string) file_get_contents($record);
        $this->db_local_changes = [self::MODULE => ['greeting' => 'Eigen']];
        chmod($this->clientDataDir() . '/lang', 0555);
        $manager = $this->manager();
        try {
            if (is_writable($this->clientDataDir() . '/lang')) {
                $this->markTestSkipped('permissions are not enforced (root)');
            }
            $manager->insertLanguageForInstallation('de');
            $without_changes = $manager->getUnrecordedTakenOverModules();

            $this->db_local_changes[self::MODULE . '2'] = ['farewell' => 'Eigen'];
            $manager->insertLanguageForInstallation('de');
        } finally {
            chmod($this->clientDataDir() . '/lang', 0775);
        }

        $this->assertSame([], $without_changes);
        $this->assertSame(['de' => ['pilot2']], $manager->getUnrecordedTakenOverModules());
        $this->assertSame($contents, (string) file_get_contents($record));
    }

    /** The report of the last call stays: setup runs installation and apply-local-changes on one manager. */
    public function testTheReportSurvivesTheInstallationFollowedByApplyingLocalChanges(): void
    {
        $this->shipPo(['greeting' => 'Hallo']);
        $this->seedGhostOverlay();
        $manager = $this->manager();

        $manager->insertLanguageForInstallation('de');
        $this->assertSame(['de' => ['ghost']], $manager->getOverlaysOfUnshippedModules());

        $manager->insertLanguageForApplyingLocalChanges('de');
        $this->assertSame(['de' => ['ghost']], $manager->getOverlaysOfUnshippedModules());
    }
}
