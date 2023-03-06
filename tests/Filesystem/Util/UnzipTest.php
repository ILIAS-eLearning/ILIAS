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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 *
 * @runTestsInSeparateProcesses // This is required for the test to work since we define some constants in the test
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class UnzipTest extends TestCase
{
    protected string $zips_dir = __DIR__ . '/zips/';
    protected string $unzips_dir = __DIR__ . '/unzips/';

    protected function tearDown(): void
    {
        if (file_exists($this->unzips_dir)) {
            rmdir($this->unzips_dir);
        }
    }


    /**
     * @dataProvider getZips
     * @param mixed[] $expected_directories
     * @param mixed[] $expected_files
     */
    public function testUnzip(
        string $zip,
        bool $has_multiple_root_entries,
        int $expected_amount_directories,
        array $expected_directories,
        int $expected_amount_files,
        array $expected_files
    ): void {
        $this->assertStringContainsString('.zip', $zip);
        $zip_path = $this->zips_dir . $zip;
        $this->assertFileExists($zip_path);

        $stream = Streams::ofResource(fopen($zip_path, 'rb'));
        $options = new UnzipOptions();
        $unzip = new Unzip($options, $stream);

        $this->assertFalse($unzip->hasZipReadingError());
        $this->assertEquals($has_multiple_root_entries, $unzip->hasMultipleRootEntriesInZip());
        $this->assertEquals($expected_amount_directories, $unzip->getAmountOfDirectories());
        $this->assertEquals($expected_directories, iterator_to_array($unzip->getDirectories()));
        $this->assertEquals($expected_amount_files, $unzip->getAmountOfFiles());
        $this->assertEquals($expected_files, iterator_to_array($unzip->getFiles()));
    }

    public function testWrongZip(): void
    {
        $stream = Streams::ofResource(fopen(__FILE__, 'rb'));
        $options = new UnzipOptions();
        $unzip = new Unzip($options, $stream);
        $this->assertTrue($unzip->hasZipReadingError());
        $this->assertFalse($unzip->hasMultipleRootEntriesInZip());
        $this->assertEquals(0, iterator_count($unzip->getFiles()));
        $this->assertEquals(0, iterator_count($unzip->getDirectories()));
        $this->assertEquals(0, iterator_count($unzip->getPaths()));
        $this->assertEquals([], iterator_to_array($unzip->getDirectories()));
        $this->assertEquals([], iterator_to_array($unzip->getFiles()));
    }

    /**
     * @dataProvider getZips
     * @param mixed[] $expected_directories
     * @param mixed[] $expected_files
     */
    public function testLegacyUnzip(
        string $zip,
        bool $has_multiple_root_entries,
        int $expected_amount_directories,
        array $expected_directories,
        int $expected_amount_files,
        array $expected_files
    ): void {
        $legacy = new LegacyArchives();

        $this->assertStringContainsString('.zip', $zip);
        $zip_path = $this->zips_dir . $zip;
        $this->assertFileExists($zip_path);

        $temp_unzip_path =$this->unzips_dir . uniqid('unzip', true);

        $return = $legacy->unzip(
            $zip_path,
            $temp_unzip_path
        );

        $this->assertTrue($return);

        $unzipped_files = $this->directoryToArray($temp_unzip_path);
        $expected_paths = array_merge($expected_directories, $expected_files);
        sort($expected_paths);
        $this->assertEquals($expected_paths, $unzipped_files);
        $this->assertTrue($this->recurseRmdir($temp_unzip_path));
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

    /**
     * @return string[]|string[][]
     */
    private function directoryToArray(string $path_to_directory): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path_to_directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        $paths = [];
        foreach ($iterator as $item) {
            $relative_path = str_replace($path_to_directory . '/', '', $item->getPathname());
            $paths[] = $item->isDir() ? $relative_path . '/' : $relative_path;
        }

        sort($paths);

        return $paths;
    }


    // PROVIDERS

    public function getZips(): array
    {
        return [
            ['1_folder_mac.zip', false, 10, $this->directories_one, 15, $this->files_one],
            ['1_folder_win.zip', false, 10, $this->directories_one, 15, $this->files_one],
            ['3_folders_mac.zip', true, 9, $this->directories_three, 12, $this->files_three],
            ['3_folders_win.zip', true, 9, $this->directories_three, 12, $this->files_three],
            ['1_folder_1_file_mac.zip', true, 3, $this->directories_mixed, 5, $this->files_mixed]
        ];
    }


    protected array $files_mixed = [
        0 => '03_Test.pdf',
        1 => 'Ordner A/01_Test.pdf',
        2 => 'Ordner A/02_Test.pdf',
        3 => 'Ordner A/Ordner A_2/07_Test.pdf',
        4 => 'Ordner A/Ordner A_2/08_Test.pdf'
    ];

    protected array $directories_mixed = [
        0 => 'Ordner A/',
        1 => 'Ordner A/Ordner A_1/',
        2 => 'Ordner A/Ordner A_2/'
    ];

    protected array $directories_one = [
        0 => 'Ordner 0/',
        1 => 'Ordner 0/Ordner A/',
        2 => 'Ordner 0/Ordner A/Ordner A_1/',
        3 => 'Ordner 0/Ordner A/Ordner A_2/',
        4 => 'Ordner 0/Ordner B/',
        5 => 'Ordner 0/Ordner B/Ordner B_1/',
        6 => 'Ordner 0/Ordner B/Ordner B_2/',
        7 => 'Ordner 0/Ordner C/',
        8 => 'Ordner 0/Ordner C/Ordner C_1/',
        9 => 'Ordner 0/Ordner C/Ordner C_2/'
    ];
    protected array $directories_three = [
        0 => 'Ordner A/',
        1 => 'Ordner A/Ordner A_1/',
        2 => 'Ordner A/Ordner A_2/',
        3 => 'Ordner B/',
        4 => 'Ordner B/Ordner B_1/',
        5 => 'Ordner B/Ordner B_2/',
        6 => 'Ordner C/',
        7 => 'Ordner C/Ordner C_1/',
        8 => 'Ordner C/Ordner C_2/'
    ];

    protected array $files_one = [
        0 => 'Ordner 0/13_Test.pdf',
        1 => 'Ordner 0/14_Test.pdf',
        2 => 'Ordner 0/15_Test.pdf',
        3 => 'Ordner 0/Ordner A/01_Test.pdf',
        4 => 'Ordner 0/Ordner A/02_Test.pdf',
        5 => 'Ordner 0/Ordner A/Ordner A_2/07_Test.pdf',
        6 => 'Ordner 0/Ordner A/Ordner A_2/08_Test.pdf',
        7 => 'Ordner 0/Ordner B/03_Test.pdf',
        8 => 'Ordner 0/Ordner B/04_Test.pdf',
        9 => 'Ordner 0/Ordner B/Ordner B_2/09_Test.pdf',
        10 => 'Ordner 0/Ordner B/Ordner B_2/10_Test.pdf',
        11 => 'Ordner 0/Ordner C/05_Test.pdf',
        12 => 'Ordner 0/Ordner C/06_Test.pdf',
        13 => 'Ordner 0/Ordner C/Ordner C_2/11_Test.pdf',
        14 => 'Ordner 0/Ordner C/Ordner C_2/12_Test.pdf'
    ];

    protected array $files_three = [
        0 => 'Ordner A/01_Test.pdf',
        1 => 'Ordner A/02_Test.pdf',
        2 => 'Ordner A/Ordner A_2/07_Test.pdf',
        3 => 'Ordner A/Ordner A_2/08_Test.pdf',
        4 => 'Ordner B/03_Test.pdf',
        5 => 'Ordner B/04_Test.pdf',
        6 => 'Ordner B/Ordner B_2/09_Test.pdf',
        7 => 'Ordner B/Ordner B_2/10_Test.pdf',
        8 => 'Ordner C/05_Test.pdf',
        9 => 'Ordner C/06_Test.pdf',
        10 => 'Ordner C/Ordner C_2/11_Test.pdf',
        11 => 'Ordner C/Ordner C_2/12_Test.pdf',
    ];
}
