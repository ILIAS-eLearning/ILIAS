<?php
declare(strict_types=1);

use ILIAS\Filesystem;
use ILIAS\Filesystem\Finder\Finder;
use ILIAS\Filesystem\MetadataType;
use PHPUnit\Framework\TestCase;

class FinderTest extends TestCase
{
	/**
	 * @throws ReflectionException
	 */
	public function testFinderWillFindNoFilesOrFoldersInAnEmptyDirectory()
	{
		$fileSystem = $this->getMockBuilder(Filesystem\Filesystem::class)->getMock();

		$fileSystem
			->expects($this->any())
			->method('listContents')
			->willReturn([]);

		$finder = (new Finder($fileSystem))->in(['/']);

		$this->assertEmpty(iterator_count($finder));
	}

	/**
	 * @throws ReflectionException
	 */
	public function testFinderWillFindFilesAndFoldersInFlatStructure()
	{
		$fileSystem = $this->getMockBuilder(Filesystem\Filesystem::class)->getMock();

		$metadata = [
			new Filesystem\DTO\Metadata('file_1.txt', MetadataType::FILE),
			new Filesystem\DTO\Metadata('file_2.mp3', MetadataType::FILE),
			new Filesystem\DTO\Metadata('dir_1', MetadataType::DIRECTORY),
		];

		$fileSystem
			->expects($this->atLeast(1))
			->method('listContents')
			->will($this->returnCallback(function ($path) use ($metadata) {
				if ('/' === $path) {
					return $metadata;
				}

				return [];
			}));

		$finder = (new Finder($fileSystem))->in(['/']);

		$this->assertCount(count($metadata), $finder);
		$this->assertCount(1, $finder->directories());
		$this->assertCount(2, $finder->files());
	}

	/**
	 * @throws ReflectionException
	 */
	public function testFinderWillFindFilesAndFoldersInNestedStructure()
	{
		$fileSystem = $this->getMockBuilder(Filesystem\Filesystem::class)->getMock();

		$rootMetadata = [
			new Filesystem\DTO\Metadata('file_1.txt', MetadataType::FILE),
			new Filesystem\DTO\Metadata('file_2.mp3', MetadataType::FILE),
			new Filesystem\DTO\Metadata('dir_1', MetadataType::DIRECTORY),
		];

		$level1Metadata = [
			new Filesystem\DTO\Metadata('dir_1/file_3.log', MetadataType::FILE),
			new Filesystem\DTO\Metadata('dir_1/file_4.php', MetadataType::FILE),
			new Filesystem\DTO\Metadata('dir_1/dir_1_1', MetadataType::DIRECTORY),
			new Filesystem\DTO\Metadata('dir_1/dir_1_2', MetadataType::DIRECTORY),
		];

		$level11Metadata = [
			new Filesystem\DTO\Metadata('dir_1/dir_1_1/file_5.cpp', MetadataType::FILE),
		];

		$level12Metadata = [
			new Filesystem\DTO\Metadata('dir_1/dir_1_2/file_6.py', MetadataType::FILE),
			new Filesystem\DTO\Metadata('dir_1/dir_1_2/file_7.cpp', MetadataType::FILE),
			new Filesystem\DTO\Metadata('dir_1/dir_1_2/dir_1_2_1', MetadataType::DIRECTORY),
		];

		$fileSystem
			->expects($this->atLeast(1))
			->method('listContents')
			->will($this->returnCallback(function ($path) use ($rootMetadata, $level1Metadata, $level11Metadata, $level12Metadata) {
				if ('/' === $path) {
					return $rootMetadata;
				} elseif ('dir_1' === $path) {
					return $level1Metadata;
				} elseif ('dir_1/dir_1_1' === $path) {
					return $level11Metadata;
				} elseif ('dir_1/dir_1_2' === $path) {
					return $level12Metadata;
				}

				return [];
			}));

		$finder = (new Finder($fileSystem))->in(['/']);

		$this->assertCount(count($rootMetadata) + count($level1Metadata) + count($level11Metadata) + count($level12Metadata), $finder);
		$this->assertCount(4, $finder->directories());
		$this->assertCount(7, $finder->files());
	}
}