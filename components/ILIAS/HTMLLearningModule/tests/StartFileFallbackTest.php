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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class StartFileFallbackTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/htlm_start_file_' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->dir);
    }

    /**
     * @param string[] $files
     */
    private function archive(array $files): ZipArchive
    {
        $path = $this->dir . '/lm.zip';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($files as $file) {
            $zip->addFromString($file, 'content');
        }
        $zip->close();

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::RDONLY);
        return $zip;
    }

    public static function archiveProvider(): array
    {
        return [
            'stored start file wins' => [['index.html', 'sub/start.html'], 'sub/start.html', 'sub/start.html'],
            'empty start file falls back to index.html' => [['index.html', 'page.html'], '', 'index.html'],
            'empty start file falls back to index.htm' => [['index.htm', 'page.html'], '', 'index.htm'],
            'index.html is preferred over index.htm' => [['index.htm', 'index.html'], '', 'index.html'],
            'stored start file missing falls back' => [['index.html'], 'gone.html', 'index.html'],
            'nested index is not used' => [['sub/index.html'], '', ''],
            'nothing found' => [['page.html'], '', ''],
        ];
    }

    /**
     * @param string[] $files
     */
    #[DataProvider('archiveProvider')]
    public function testLocateStartFileInArchive(array $files, string $stored, string $expected): void
    {
        $zip = $this->archive($files);
        $this->assertSame($expected, ilObjFileBasedLMAccess::locateStartFileInArchive($zip, $stored));
        $zip->close();
    }

    public static function directoryProvider(): array
    {
        return [
            'stored start file is kept' => [['index.html'], 'start.html', 'start.html'],
            'index.html' => [['index.html', 'index.htm'], '', 'index.html'],
            'index.htm' => [['index.htm'], '', 'index.htm'],
            'nothing found' => [['page.html'], '', ''],
        ];
    }

    /**
     * @param string[] $files
     */
    #[DataProvider('directoryProvider')]
    public function testMigrationDeterminesStartFile(array $files, string $stored, string $expected): void
    {
        foreach ($files as $file) {
            touch($this->dir . '/' . $file);
        }
        $migration = new class () extends ilHTLMMigration {
            public function determine(string $lm_path, string $start_file): string
            {
                return $this->determineStartFile($lm_path, $start_file);
            }
        };
        $this->assertSame($expected, $migration->determine($this->dir, $stored));
    }
}
