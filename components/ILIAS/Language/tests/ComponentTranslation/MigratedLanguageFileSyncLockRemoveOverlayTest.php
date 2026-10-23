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

use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

/**
 * RemoveOverlay() together with the lock file: removal, nesting and recreation by a following sync.
 */
class MigratedLanguageFileSyncLockRemoveOverlayTest extends MigratedLanguageFileSyncLockTestCase
{
    /**
     * A lock file left over from an interrupted run is still removed when the .po/.mo pair is already
     * gone.
     */
    public function testRemoveOverlayRunsAndRemovesTheLockFileWhenOnlyTheLockFileIsLeft(): void
    {
        $overlay_base = MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, 'de');
        mkdir(dirname($overlay_base), 0775, true);
        file_put_contents(MigratedPoFixture::overlayLock($overlay_base), '');

        MigratedLanguageFileSync::removeOverlay($this->manager, 'de', self::MODULE, $this->client_data_dir);

        $this->assertFileDoesNotExist(MigratedPoFixture::overlayLock($overlay_base));
    }

    /**
     * A nested removeOverlay() under an already held lock removes the overlay but neither deletes nor
     * replaces the lock file (same inode throughout).
     */
    public function testANestedRemoveOverlayInsideWithOverlayLockDoesNotDeleteTheLockFileOrRetry(): void
    {
        $overlay_base = MigratedPoFixture::overlayBase($this->client_data_dir, $this->directory, 'de');
        mkdir(dirname($overlay_base), 0775, true);
        file_put_contents($overlay_base . '.po', 'PO');
        MigratedPoFixture::writeOverlayRevision($overlay_base, MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Servus']));
        file_put_contents(MigratedPoFixture::overlayLock($overlay_base), '');
        clearstatcache(true, MigratedPoFixture::overlayLock($overlay_base));
        $lock_inode_before = lstat(MigratedPoFixture::overlayLock($overlay_base))['ino'];

        // Run in a child process with a hard timeout: without the re-entrancy guard this scenario is
        // the very same same-process self-deadlock testANestedWithOverlayLockForTheSameModuleReturns...
        // above guards against - the nested removeOverlay() would block forever on the very lock its
        // own enclosing withOverlayLock() call already holds.
        $result = $this->runPhpWithTimeout($this->nestedRemoveOverlayChildCode(), 5.0);

        $this->assertTrue($result['finished'], 'a nested removeOverlay() must return instead of hanging: ' . $result['output']);
        $this->assertSame(0, $result['exit_code'], $result['output']);

        clearstatcache(true, $overlay_base . '.po');
        clearstatcache(true, MigratedPoFixture::overlayMo($overlay_base));
        clearstatcache(true, MigratedPoFixture::overlayLock($overlay_base));
        $this->assertFileDoesNotExist($overlay_base . '.po', 'the nested removeOverlay() must still actually remove the overlay files');
        $this->assertFileDoesNotExist(MigratedPoFixture::overlayMo($overlay_base));
        $this->assertStringContainsString(
            'AFTER_NESTED:' . $lock_inode_before . "\n",
            $result['output'],
            'a removeOverlay() nested inside an already-held lock must NOT delete or replace the lock file - the outer caller still relies on it'
        );
        $this->assertFileExists(MigratedPoFixture::overlayLock($overlay_base));
        $this->assertSame(
            $lock_inode_before,
            lstat(MigratedPoFixture::overlayLock($overlay_base))['ino'],
            'the lock file must stay the exact same one throughout - no retry/replace cycle for the nested call'
        );
    }

    private function nestedRemoveOverlayChildCode(): string
    {
        $template = <<<'PHP'
            use ILIAS\Language\ComponentTranslation\CustomizingLanguageFileDirectory;
            use ILIAS\Language\ComponentTranslation\LanguageFileDirectory;
            use ILIAS\Language\ComponentTranslation\LanguageFileDirectoryManager;
            use ILIAS\Language\ComponentTranslation\MigratedLanguageFileSync;

            $root = __ROOT__;
            require_once $root . '/vendor/composer/vendor/autoload.php';

            $directory = new class implements LanguageFileDirectory {
                public function getPrefix(): string
                {
                    return 'stest';
                }
                public function getPath(): string
                {
                    return 'x/';
                }
                public function getSuffix(): string
                {
                    return '';
                }
                public function isLocal(): bool
                {
                    return false;
                }
            };
            $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);
            $client_data_dir = __CLIENT_DATA_DIR__;

            MigratedLanguageFileSync::withOverlayLock(
                $manager,
                'de',
                'stest',
                $client_data_dir,
                function () use ($manager, $client_data_dir): void {
                    MigratedLanguageFileSync::removeOverlay($manager, 'de', 'stest', $client_data_dir);
                    clearstatcache();
                    $lock = @lstat($client_data_dir . '/lang/stest/de/lock');
                    echo 'AFTER_NESTED:' . ($lock === false ? 'missing' : $lock['ino']) . "\n";
                }
            );
            echo "done\n";
            PHP;

        return str_replace(
            ['__ROOT__', '__CLIENT_DATA_DIR__'],
            [var_export(ILIAS_ABSOLUTE_PATH, true), var_export($this->client_data_dir, true)],
            $template
        );
    }

    /**
     * A sync after removeOverlay() (reinstall) creates a fresh .lock.
     */
    public function testASyncAfterRemoveOverlayCreatesANewLockFile(): void
    {
        $relative_path = 'components/ILIAS/Language/tests/ComponentTranslation/tmp-lock-shipped-' . bin2hex(random_bytes(4)) . '/';
        MigratedPoFixture::writeShippedPo(
            $relative_path,
            self::MODULE,
            'de',
            MigratedPoFixture::catalog(self::MODULE, ['greeting' => 'Hallo'])
        );
        $directory = MigratedPoFixture::directory(self::MODULE, $relative_path);
        $manager = new LanguageFileDirectoryManager(new CustomizingLanguageFileDirectory(), $directory);
        $overlay_base = MigratedPoFixture::overlayBase($this->client_data_dir, $directory, 'de');

        try {
            // a local change: a shipped value writes no overlay, and without an overlay no lock file is kept
            MigratedLanguageFileSync::sync($manager, ILIAS_ABSOLUTE_PATH, 'de', self::MODULE, ['greeting' => 'Moin'], $this->client_data_dir);
            $this->assertFileExists(MigratedPoFixture::overlayLock($overlay_base), 'sync() must leave a .lock file behind for later callers to serialize on');

            MigratedLanguageFileSync::removeOverlay($manager, 'de', self::MODULE, $this->client_data_dir);
            $this->assertFileDoesNotExist(MigratedPoFixture::overlayLock($overlay_base), 'removeOverlay() (uninstall) must have removed it');

            MigratedLanguageFileSync::sync($manager, ILIAS_ABSOLUTE_PATH, 'de', self::MODULE, ['greeting' => 'Servus'], $this->client_data_dir);
            $this->assertFileExists(MigratedPoFixture::overlayLock($overlay_base), 'a reinstall/resync must create a fresh .lock file');
        } finally {
            MigratedPoFixture::removeShippedDirectory($relative_path);
        }
    }
}
