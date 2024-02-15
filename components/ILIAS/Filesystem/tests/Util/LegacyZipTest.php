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

namespace ILIAS\Filesystem\Util;

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\Filesystem\Util\Archive\LegacyArchives;
use ILIAS\Filesystem\Util\Archive\Unzip;
use ILIAS\Filesystem\Util\Archive\UnzipOptions;
use PHPUnit\Framework\TestCase;
use ILIAS\Filesystem\Util\Archive\Zip;
use ILIAS\Filesystem\Util\Archive\ZipOptions;

/**
 * @author                      Fabian Schmid <fabian@sr.solutions>
 *
 * @runTestsInSeparateProcesses // This is required for the test to work since we define some constants in the test
 * @preserveGlobalState         disabled
 * @backupGlobals               disabled
 * @backupStaticAttributes      disabled
 */
class LegacyZipTest extends TestCase
{
    private const DIR_TO_ZIP = __DIR__ . '/dir_zip/testing_001';
    public const ZIPPED_ZIP = 'zipped.zip';
    protected string $zips_dir = __DIR__ . '/zips/';
    protected string $unzips_dir = __DIR__ . '/unzips/';
    private string $extracting_dir = '';

    private string $zip_output_path = '';

    protected function setUp(): void
    {
        if (file_exists($this->unzips_dir . self::ZIPPED_ZIP)) {
            unlink($this->unzips_dir . self::ZIPPED_ZIP);
        }
        if (!defined('CLIENT_WEB_DIR')) {
            define('CLIENT_WEB_DIR', __DIR__);
        }
        if (!defined('ILIAS_WEB_DIR')) {
            define('ILIAS_WEB_DIR', __DIR__);
        }
        if (!defined('CLIENT_DATA_DIR')) {
            define('CLIENT_DATA_DIR', __DIR__);
        }
        if (!defined('ILIAS_ABSOLUTE_PATH')) {
            define('ILIAS_ABSOLUTE_PATH', __DIR__);
        }
        if (!defined('CLIENT_ID')) {
            define('CLIENT_ID', 'client_id');
        }
    }

    protected function tearDown(): void
    {
        if (!empty($this->extracting_dir) && file_exists($this->extracting_dir)) {
            $this->recurseRmdir($this->extracting_dir);
        }
        if (!empty($this->zip_output_path) && file_exists($this->zip_output_path)) {
            unlink($this->zip_output_path);
        }
    }

    public function testZipAndUnzipWithTop(): void
    {
        $legacy = new LegacyArchives();
        $directory_to_zip = self::DIR_TO_ZIP;
        $this->zip_output_path = $zip_output_path = $this->zips_dir . self::ZIPPED_ZIP;

        $legacy->zip(
            $directory_to_zip,
            $zip_output_path,
            true
        );

        $this->assertTrue(file_exists($zip_output_path));

        // unzip
        $this->extracting_dir = $extracting_dir = $this->unzips_dir . 'extracted';
        $legacy->unzip(
            $zip_output_path,
            $extracting_dir,
            true,
            false,
            false
        );

        $this->assertEquals(
            $this->pathToArray($directory_to_zip),
            $this->pathToArray($extracting_dir . '/' . basename($directory_to_zip))
        );

        // remove zip file
        unlink($zip_output_path);
        $this->assertFalse(file_exists($zip_output_path));

        // remove extracted dir
        $this->recurseRmdir($extracting_dir);
    }

    private function pathToArray(string $path): array
    {
        $ignore = ['.', '..', '.DS_Store'];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        return array_map(
            static function ($file) use ($path): string {
                $real_path = $file->getRealPath();

                return str_replace($path, '', $real_path);
            },
            array_values(
                array_filter(
                    iterator_to_array($files),
                    static function (\SplFileInfo $file) use ($ignore): bool {
                        return !in_array($file->getFilename(), $ignore, true);
                    }
                )
            )
        );
    }

    private function recurseRmdir(string $path_to_directory): bool
    {
        $files = array_diff(scandir($path_to_directory), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$path_to_directory/$file") && !is_link("$path_to_directory/$file")) ? $this->recurseRmdir(
                "$path_to_directory/$file"
            ) : unlink("$path_to_directory/$file");
        }
        return rmdir($path_to_directory);
    }

}
