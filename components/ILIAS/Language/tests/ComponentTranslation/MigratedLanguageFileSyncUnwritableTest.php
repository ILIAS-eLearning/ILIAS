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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * MigratedLanguageFileSync::findUnwritableOverlayDirectories().
 */
class MigratedLanguageFileSyncUnwritableTest extends MigratedLanguageFileSyncTestCase
{
    /** Root-proof: a regular file in place of the overlay directory - no user can create it, so it is reported. */
    public function testAFileInPlaceOfTheOverlayDirectoryIsReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        mkdir(dirname($this->overlayDirectory()), 0775, true);
        file_put_contents($this->overlayDirectory(), 'not a directory');

        $this->assertSame([$this->overlayDirectory()], $this->findUnwritable());
    }

    /** A file further up (where "<client data dir>/lang" is created) blocks the path just as well. */
    public function testAFileInPlaceOfAParentOfTheOverlayDirectoryIsReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        file_put_contents($this->client_data_dir . '/lang', 'not a directory');

        $this->assertSame([$this->overlayDirectory()], $this->findUnwritable());
    }

    /**
     * A dangling symlink does not "exist" for file_exists(), but mkdir() cannot create anything in its place (overlay
     * directory or a parent).
     */
    #[DataProvider('danglingSymlinkPositions')]
    public function testADanglingSymlinkInTheOverlayPathIsReported(\Closure $link_path): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        $link = $link_path($this);
        if (!is_dir(dirname($link))) {
            mkdir(dirname($link), 0775, true);
        }
        symlink($this->client_data_dir . '/does-not-exist', $link);

        $this->assertSame([$this->overlayDirectory()], $this->findUnwritable());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function danglingSymlinkPositions(): array
    {
        return [
            'the overlay directory' => [static fn(self $test): string => $test->overlayDirectory()],
            'a parent ("lang")' => [static fn(self $test): string => $test->client_data_dir . '/lang'],
        ];
    }

    /** A symlink to an existing writable directory is fine (e.g. a mounted data volume). */
    public function testASymlinkToAWritableDirectoryIsNotReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        mkdir($this->client_data_dir . '/elsewhere');
        symlink($this->client_data_dir . '/elsewhere', $this->client_data_dir . '/lang');

        $this->assertSame([], $this->findUnwritable());
    }

    /** A missing directory is fine if the closest existing parent is writable: sync() creates it. */
    public function testAMissingButCreatableOverlayDirectoryIsNotReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang');
        $this->assertSame([], $this->findUnwritable());
        $this->assertDirectoryDoesNotExist($this->client_data_dir . '/lang', 'the check itself creates nothing');
    }

    public function testAMissingClientDataDirWithACreatableParentIsNotReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);

        $this->assertSame([], $this->findUnwritable(['de'], $this->client_data_dir . '/not-yet-created'));
    }

    public function testAnExistingWritableOverlayDirectoryIsNotReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        mkdir($this->overlayDirectory(), 0775, true);

        $this->assertSame([], $this->findUnwritable());
    }

    public function testWithoutClientDataDirNothingIsReported(): void
    {
        $this->seedShipped(['greeting' => 'Hallo']);
        file_put_contents($this->client_data_dir . '/lang', 'not a directory');

        $this->assertSame([], $this->findUnwritable(['de'], null));
    }

    /**
     * Only modules migrated for a given language count: without a shipped `.po` no overlay is written, so a blocked
     * directory is irrelevant (as for modules without contributed directory and the main directory).
     */
    public function testAModuleThatIsNotMigratedForTheLanguageIsIgnored(): void
    {
        $this->seedShipped(['greeting' => 'Hello'], 'en');
        file_put_contents($this->client_data_dir . '/lang', 'not a directory');

        $this->assertSame([], $this->findUnwritable(['de']));
        $this->assertSame([$this->client_data_dir . '/lang/' . self::MODULE . '/en'], $this->findUnwritable(['de', 'en']));
    }

    /**
     * Each module has an overlay directory per language (`lang/<module>/<lang>`); each blocked one is reported once.
     */
    public function testEachUnwritableOverlayDirectoryIsReportedOnceAcrossLanguagesAndModules(): void
    {
        $second = MigratedPoFixture::directory('stwo', 'components/ILIAS/Language/tests/ComponentTranslation/' . basename($this->fixture_directory) . '/sub/');
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $this->directory, $second);
        $this->seedShipped(['greeting' => 'Hallo'], 'de');
        $this->seedShipped(['greeting' => 'Hello'], 'en');
        MigratedPoFixture::writePo(
            $this->fixture_directory . '/sub/stwo_de.po',
            MigratedPoFixture::catalog('stwo', ['x' => 'y'])
        );
        file_put_contents($this->client_data_dir . '/lang', 'not a directory');

        $this->assertSame(
            [
                $this->client_data_dir . '/lang/' . self::MODULE . '/de',
                $this->client_data_dir . '/lang/stwo/de',
                $this->client_data_dir . '/lang/' . self::MODULE . '/en',
            ],
            $this->findUnwritable(['de', 'en', 'de'], 'default', $manager)
        );
    }
}
