<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with
 * the source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\InstallLanguage;
use ilSetupLanguage;

class InstallLanguageModesTest extends \ilLanguageBaseTestCase
{
    use RealFieldsUiFactory;
    use InstallLanguageTestFixture;

    public function testSingleNewLanguageIsInstalled(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ' de ',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['de'], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    /**
     * The Activity passes exactly the language key (no $create_missing_mo flag). Captures the raw arguments, as
     * ->with('de') would also accept an additional one.
     */
    public function testFullInstallPassesOnlyTheLanguageKey(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $calls = [];
        $setup_language->expects($this->once())
            ->method('insertLanguageForInstallation')
            ->willReturnCallback(static function () use (&$calls): array {
                $calls[] = func_get_args();
                return [];
            });

        $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([['de']], $calls);
    }

    /**
     * A non-empty list from insertLanguageForInstallation() (migrated modules whose overlay could not be written)
     * lands in "overlay_write_failed_language_keys"; the language still counts as installed.
     */
    public function testFullInstallListsOnlyTheLanguagesWhoseOverlayCouldNotBeWritten(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->exactly(2))->method('insertLanguageForInstallation')->willReturnMap([
            ['de', ['tos', 'pilot']],
            ['fr', []],
        ]);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de,fr',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['de', 'fr'], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['overlay_write_failed_language_keys']);
    }

    public function testInstallLocalModeListsTheLanguageWhoseOverlayCouldNotBeWritten(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            [
                'de' => ['obj_id' => 1, 'status' => 'installed'],
                'en' => ['obj_id' => 2, 'status' => 'installed'],
            ],
            ['de', 'en'],
            ['de', 'en']
        );
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->exactly(2))->method('insertLanguageForApplyingLocalChanges')->willReturnMap([
            ['de', []],
            ['en', ['pilot']],
        ]);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en'],
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame(['de', 'en'], $result['installed_with_local_language_keys']);
        $this->assertSame(['en'], $result['overlay_write_failed_language_keys']);
    }

    public function testNothingIsListedWhenEveryOverlayWasWrittenOrNothingWasInstalled(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            [],
            ['de']
        );
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('fr')->willReturn([]);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'fr'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['fr'], $result['installed_language_keys']);
        $this->assertSame([], $result['overlay_write_failed_language_keys']);
    }

    public function testMultipleLanguagesSeparatesNotInstalledFromAlreadyInstalledUnderInstallMode(): void
    {
        // mode "install": the already installed 'de' is left completely alone, only 'fr' is installed
        $setup_language = $this->createSetupLanguageMock(
            [
                'de' => ['obj_id' => 1, 'status' => 'installed'],
            ],
            [],
            ['de']
        );
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('fr')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('fr');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('fr');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => [' de, fr ', 'de'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['fr'], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    public function testNewLanguageWithCustomFileIsReportedAsInstalledWithLocalFile(): void
    {
        // classified by "has a customizing/local file", not "newly installed"
        $setup_language = $this->createSetupLanguageMock(
            [],
            ['de'],
            []
        );
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation')->with('de');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation')->with('de');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /**
     * Mode "install" must not re-install or re-apply the local file of an already installed language.
     */
    public function testInstallModeIsNoOpForAlreadyInstalledLanguageEvenWithACustomFile(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['de'],
            ['de']
        );
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    public function testInstallLocalModeAppliesCustomFileToAlreadyInstalledLanguage(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['de'],
            ['de']
        );
        $setup_language->expects($this->once())->method('checkLanguageForInstallation')->with('de')->willReturn(true);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->once())
            ->method('insertLanguageForApplyingLocalChanges')
            ->with('de');
        // object_data bookkeeping is delegated to ilSetupLanguage::registerInstalledLanguage(), see ilSetupLanguageTest
        $setup_language->expects($this->once())
            ->method('registerInstalledLanguage')
            ->with(
                'de',
                ['de' => ['obj_id' => 1, 'status' => 'installed']],
                ['de']
            );

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    public function testInstallLocalModePassesOnlyTheLanguageKey(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['de'],
            ['de']
        );
        $setup_language->method('checkLanguageForInstallation')->willReturn(true);
        $calls = [];
        $setup_language->expects($this->once())
            ->method('insertLanguageForApplyingLocalChanges')
            ->willReturnCallback(static function () use (&$calls): array {
                $calls[] = func_get_args();
                return [];
            });

        $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([['de']], $calls);
    }

    /**
     * "install_local" on an already installed language without a local file runs its write methods, but the result
     * must say "already installed", since nothing observable changed.
     */
    public function testInstallLocalModeOnAlreadyInstalledLanguageWithoutCustomFileIsReportedAsAlreadyInstalled(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            [], // no customizing/local file exists for 'de'
            ['de']
        );
        $setup_language->expects($this->once())->method('checkLanguageForInstallation')->with('de')->willReturn(true);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->once())
            ->method('insertLanguageForApplyingLocalChanges')
            ->with('de');
        $setup_language->expects($this->once())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /**
     * "install_local" on a language that is not installed at all is a complete no-op.
     */
    public function testInstallLocalModeOnNotInstalledLanguageIsCompleteNoOp(): void
    {
        $setup_language = $this->createSetupLanguageMock([], [], []);
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame(['de'], $result['not_installed_language_keys']);
    }

    /**
     * If every requested language is a no-op, perform() must not read the available/local languages or scan for
     * invalid local files.
     */
    public function testAllRequestedLanguagesBeingNoOpsSkipsLanguageAndFileLookupsEntirely(): void
    {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->method('getInstalledLanguages')->willReturn(['de', 'en']);
        $setup_language->expects($this->never())->method('getAvailableLanguagesForInstallation');
        $setup_language->expects($this->never())->method('getLocalLanguages');
        $setup_language->expects($this->never())->method('getInvalidLocalLanguageFiles');
        $setup_language->expects($this->never())->method('checkLanguageForInstallation');
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->never())->method('registerInstalledLanguage');

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame(['de', 'en'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }

    /**
     * Languages in every state at once land in their own bucket; only affected ones trigger a write (mode "install").
     */
    public function testBulkRequestUnderInstallModePartitionsEachKeyIntoTheCorrectBucket(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            ['de' => ['obj_id' => 1, 'status' => 'installed']],
            ['fr'], // only 'fr' has a pending customizing/local file
            ['de']
        );
        $setup_language->expects($this->exactly(2))
            ->method('checkLanguageForInstallation')
            ->willReturnMap([
                ['en', true],
                ['fr', true],
            ]);
        $setup_language->expects($this->exactly(2))->method('flushLanguageForInstallation');
        $setup_language->expects($this->exactly(2))->method('insertLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForApplyingLocalChanges');
        $setup_language->expects($this->exactly(2))->method('registerInstalledLanguage');
        $setup_language->expects($this->once())
            ->method('getInvalidLocalLanguageFiles')
            ->with(['en', 'fr']);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en', 'fr'],
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['en'], $result['installed_language_keys']);
        $this->assertSame(['fr'], $result['installed_with_local_language_keys']);
        $this->assertSame(['de'], $result['already_installed_language_keys']);
        $this->assertSame([], $result['not_installed_language_keys']);
    }

    /**
     * Mode "install_local": only insertLanguageForApplyingLocalChanges, only for already installed keys.
     */
    public function testBulkRequestUnderInstallLocalModePartitionsEachKeyIntoTheCorrectBucket(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            [
                'de' => ['obj_id' => 1, 'status' => 'installed'],
                'en' => ['obj_id' => 2, 'status' => 'installed'],
            ],
            ['de'], // only 'de' has a pending customizing/local file
            ['de', 'en']
        );
        $setup_language->expects($this->exactly(2))->method('checkLanguageForInstallation')->willReturn(true);
        $setup_language->expects($this->never())->method('flushLanguageForInstallation');
        $setup_language->expects($this->never())->method('insertLanguageForInstallation');
        $setup_language->expects($this->exactly(2))
            ->method('insertLanguageForApplyingLocalChanges')
            ->with($this->logicalOr('de', 'en'));
        $setup_language->expects($this->exactly(2))->method('registerInstalledLanguage');
        $setup_language->expects($this->once())
            ->method('getInvalidLocalLanguageFiles')
            ->with(['de', 'en']);

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => ['de', 'en', 'fr'],
            'mode' => InstallLanguage::MODE_INSTALL_LOCAL,
        ]);

        $this->assertSame([], $result['installed_language_keys']);
        $this->assertSame(['de'], $result['installed_with_local_language_keys']);
        $this->assertSame(['en'], $result['already_installed_language_keys']);
        $this->assertSame(['fr'], $result['not_installed_language_keys']);
    }

    public function testInstallingNewLanguageDoesNotUpdateAlreadyInstalledLanguages(): void
    {
        $setup_language = $this->createSetupLanguageMock(
            [
                'en' => ['obj_id' => 1, 'status' => 'installed'],
            ],
            [],
            ['en']
        );
        $setup_language->expects($this->once())
            ->method('checkLanguageForInstallation')
            ->with('de')
            ->willReturn(true);
        $setup_language->expects($this->once())->method('flushLanguageForInstallation');
        $setup_language->expects($this->once())->method('insertLanguageForInstallation');
        $setup_language->expects($this->once())
            ->method('registerInstalledLanguage')
            ->with(
                'de',
                ['en' => ['obj_id' => 1, 'status' => 'installed']],
                []
            );

        $result = $this->createActivity($setup_language)->perform([
            'language_keys' => 'de',
            'mode' => InstallLanguage::MODE_INSTALL,
        ]);

        $this->assertSame(['de'], $result['installed_language_keys']);
        $this->assertSame([], $result['installed_with_local_language_keys']);
        $this->assertSame([], $result['already_installed_language_keys']);
        $this->assertSame([], $result['invalid_local_language_files']);
    }
}
