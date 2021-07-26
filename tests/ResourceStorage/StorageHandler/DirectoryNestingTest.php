<?php

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\Filesystem\Filesystem;
use ILIAS\ResourceStorage\AbstractBaseTest;
use ILIAS\ResourceStorage\StorageHandler\StorageHandler;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\MaxNestingFileSystemStorageHandler;
use ILIAS\ResourceStorage\StorageHandler\FileSystemBased\FileSystemStorageHandler;

/**
 * Class DirectoryNestingTest
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class DirectoryNestingTest extends AbstractBaseTest
{
    private const NESTING_256 = 256; // 16^2
    private const NESTING_4096 = 4096; // 16^3
    private const NESTING_65536 = 65536; // 16^4
    private const NESTING_4294967296 = 4294967296; // 16^8
    private const NESTING_281474976710656 = 281474976710656; // 16^12

    private const MAX_NESTING = self::NESTING_65536;
    private const MIN_NESTING = self::NESTING_256;
    private const COMBINATIONS = 16; // 0-9a-f

    /**
     * @var Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $file_system_mock;

    protected function setUp() : void
    {
        parent::setUp();
        $this->file_system_mock = $this->createMock(Filesystem::class);
    }

    private function withImplementation(StorageHandler $h, int $min, int $max) : void
    {
        $id_generator = $h->getIdentificationGenerator();

        for ($x = 0; $x < 100; $x++) {
            $random_id = $id_generator->getUniqueResourceIdentification();
            $path = $h->getContainerPathWithoutBase($random_id);
            $path_elements = explode("/", $h->getContainerPathWithoutBase($random_id));
            $first_element = end($path_elements);
            $last_path_element = end($path_elements);
            foreach ($path_elements as $path_element) {
                $characters = strlen($path_element);
                $possible_combinations = self::COMBINATIONS ** $characters;
                if ($path_element !== $last_path_element) {
                    $this->assertLessThanOrEqual($max, $possible_combinations);
                    $this->assertGreaterThan($min, $possible_combinations);
                }
            }
        }
    }

    public function testMaxNestingV1() : void
    {
        $storage_handler = new FileSystemStorageHandler($this->file_system_mock, 2);
        $this->withImplementation($storage_handler, self::NESTING_256, self::NESTING_281474976710656);
    }

    public function testMaxNestingV2() : void
    {
        $storage_handler = new MaxNestingFileSystemStorageHandler($this->file_system_mock, 2);
        $this->withImplementation($storage_handler, self::NESTING_4096 - 1, self::NESTING_4096);
    }

}

