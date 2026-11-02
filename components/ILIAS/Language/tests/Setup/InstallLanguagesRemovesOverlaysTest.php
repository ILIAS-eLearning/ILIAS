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
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MainLanguageFileDirectory;
use ILIAS\Language\Setup\InstalledLanguageRepository;
use ILIAS\Language\Setup\LanguageInstallationManager;
use ILIAS\Language\Setup\NullTranslationCache;
use PHPUnit\Framework\TestCase;

/**
 * installLanguages() must remove the PO/MO overlay of a migrated module for every previously known language that is
 * no longer among the requested $lang_keys (LanguageInstallationManager::removeOverlays()); otherwise the compiled
 * overlay stays on disk and keeps being served. Uses a LanguageFileDirectory in a throwaway temp directory, like
 * LanguageInstallationManagerMigratedModulesTest.
 */
class InstallLanguagesRemovesOverlaysTest extends TestCase
{
    use RestoresProcessState;

    private const string MODULE = 'pilot';

    private string $root;
    private LanguageFileDirectory $directory;

    protected function setUp(): void
    {
        // never the build of the installation, see MigratedPoFixture::resetRuntime()
        MigratedPoFixture::resetRuntime();
        $this->root = sys_get_temp_dir() . '/ilias_lim_overlay_test_' . bin2hex(random_bytes(6));
        mkdir($this->root . '/lang/customizing', 0775, true);
        mkdir($this->root . '/client-data', 0775, true);
        $this->directory = MigratedPoFixture::directory(self::MODULE, 'components/pilot/lang/');
    }

    protected function tearDown(): void
    {
        MigratedPoFixture::removeDirectory($this->root);
    }

    private function overlayBase(string $lang_key): string
    {
        return $this->root . '/client-data/lang/pilot/' . $lang_key . '/pilot_' . $lang_key;
    }

    private function seedOverlay(string $lang_key): void
    {
        MigratedPoFixture::writePair(
            $this->overlayBase($lang_key),
            MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo'])
        );
    }

    private function createDatabaseStub(): ilDBInterface
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value): string => "'" . (string) $value . "'"
        );
        $db->method('now')->willReturn('NOW()');
        $db->method('manipulate')->willReturn(1);

        return $db;
    }

    private function createManager(InstalledLanguageRepository $repository): LanguageInstallationManager
    {
        return new LanguageInstallationManager(
            $this->createDatabaseStub(),
            new LanguageFileDirectoryManager(
                new CustomizingLanguageFileDirectory(),
                new MainLanguageFileDirectory(),
                $this->directory
            ),
            $this->root,
            $repository,
            null,
            new NullTranslationCache(),
            fn(): string => $this->root . '/client-data'
        );
    }

    /**
     * The "no longer requested" branch must call removeOverlays(), and for the dropped key.
     */
    public function testDeselectingAPreviouslyInstalledLanguageRemovesItsOverlayFiles(): void
    {
        $this->seedOverlay('de');
        $this->assertFileExists($this->overlayBase('de') . '.po');
        $this->assertFileExists(MigratedPoFixture::overlayMo($this->overlayBase('de')));

        $repository = $this->createStub(InstalledLanguageRepository::class);
        $repository->method('getAvailableLanguages')->willReturn([
            'de' => ['obj_id' => 3, 'status' => 'installed'],
        ]);
        $repository->method('getLocalLanguages')->willReturn([]);

        $manager = $this->createManager($repository);

        // 'de' is no longer requested: uninstalled, including its overlay
        $result = $manager->installLanguages([]);

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($this->overlayBase('de') . '.po');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($this->overlayBase('de')));
    }

    /**
     * A language that is still requested keeps its overlay: removeOverlays() runs only for the "no longer requested" branch.
     */
    public function testStillRequestedLanguageKeepsItsOverlayFiles(): void
    {
        $this->seedOverlay('de');

        $repository = $this->createStub(InstalledLanguageRepository::class);
        $repository->method('checkLanguage')->willReturn(true);
        $repository->method('getAvailableLanguages')->willReturn([
            'de' => ['obj_id' => 3, 'status' => 'installed'],
        ]);
        $repository->method('getLocalLanguages')->willReturn([]);
        $repository->method('getLocalChanges')->willReturn([]);

        $manager = $this->createManager($repository);

        $result = $manager->installLanguages(['de']);

        $this->assertTrue($result);
        $this->assertFileExists($this->overlayBase('de') . '.po');
        $this->assertFileExists(MigratedPoFixture::overlayMo($this->overlayBase('de')));
    }
}
