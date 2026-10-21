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
use PHPUnit\Framework\Attributes\DataProvider;

/** findModulesWithOverlay(): only `current` or a `.po` in `lang/<module>/<lang>/` counts, nothing else below `lang`. */
class MigratedLanguageFileSyncFindModulesWithOverlayTest extends MigratedLanguageFileSyncTestCase
{
    private function touch(string $relative): void
    {
        $file = $this->client_data_dir . '/lang/' . $relative;
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }
        file_put_contents($file, 'x');
    }

    /**
     * @return array<string, array{0: string, 1: bool}> file below `lang/zmod/de/`, whether it makes an overlay
     */
    public static function overlayMarkers(): array
    {
        return [
            'current' => ['current', true],
            'po' => ['zmod_de.po', true],
            'only the lock' => ['zmod_de.lock', false],
            'only the mo' => ['zmod_de.mo', false],
        ];
    }

    #[DataProvider('overlayMarkers')]
    public function testOnlyCurrentOrAPoMakesAnOverlay(string $file, bool $is_overlay): void
    {
        $this->touch('zmod/de/' . $file);

        $this->assertSame($is_overlay ? ['zmod'] : [], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'de'));
    }

    public function testAModuleIsFoundPerLanguageAndTheResultIsSorted(): void
    {
        $this->touch('zmod/de/current');
        $this->touch('amod/de/amod_de.po');
        $this->touch('fmod/fr/current');

        $this->assertSame(['amod', 'zmod'], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'de'));
        $this->assertSame(['fmod'], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'fr'));
    }

    /** The lock directory and the record sit next to the module directories and are no modules. */
    public function testLocksAndTheTakenOverRecordAreNoModules(): void
    {
        MigratedLanguageFileSync::markLocalChangesTakenOver($this->client_data_dir, 'de', ['zmod']);
        $this->touch('.locks/de/current');
        $this->touch('.locks/de/x.po');

        $this->assertFileExists(MigratedLanguageFilePaths::takenOverFile($this->client_data_dir, 'de'));
        $this->assertSame([], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'de'));
    }

    public function testSymbolicLinksAreNotFollowed(): void
    {
        $outside = sys_get_temp_dir() . '/ilias_find_overlay_outside_' . bin2hex(random_bytes(4));
        mkdir($outside . '/de', 0775, true);
        file_put_contents($outside . '/de/current', 'x');
        try {
            mkdir($this->client_data_dir . '/lang/linkedmod', 0775, true);
            symlink($outside . '/de', $this->client_data_dir . '/lang/linkedmod/de');
            symlink($outside, $this->client_data_dir . '/lang/linkedmodule');
            mkdir($this->client_data_dir . '/lang/linkedcurrent/de', 0775, true);
            symlink($outside . '/missing', $this->client_data_dir . '/lang/linkedcurrent/de/current');

            $this->assertSame(['linkedcurrent'], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'de'));
        } finally {
            MigratedPoFixture::removeDirectory($outside);
        }
    }

    public function testNoClientDataDirectoryAnInvalidLanguageKeyOrNoLangDirectoryFindNothing(): void
    {
        $this->touch('zmod/de/current');

        $this->assertSame([], MigratedLanguageFileSync::findModulesWithOverlay(null, 'de'));
        $this->assertSame([], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, '../de'));
        $this->assertSame([], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir . '/missing', 'de'));
    }

    /** An unreadable module directory counts as overlay, an unreadable overlay root finds nothing. */
    public function testUnreadableDirectoriesCountAsOverlayOrFindNothing(): void
    {
        $this->touch('amod/de/current');
        mkdir($this->client_data_dir . '/lang/zmod/de', 0775, true);
        chmod($this->client_data_dir . '/lang/zmod/de', 0000);
        try {
            if (is_readable($this->client_data_dir . '/lang/zmod/de')) {
                $this->markTestSkipped('permissions are not enforced (root)');
            }
            $this->assertSame(['amod', 'zmod'], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'de'));

            chmod($this->client_data_dir . '/lang', 0000);
            $this->assertSame([], MigratedLanguageFileSync::findModulesWithOverlay($this->client_data_dir, 'de'));
        } finally {
            chmod($this->client_data_dir . '/lang', 0775);
            chmod($this->client_data_dir . '/lang/zmod/de', 0775);
        }
    }
}
