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

namespace ILIAS\ResourceStorage\Resource;

use PHPUnit\Framework\TestCase;
use ZipArchive;

/**
 * Mantis 48134: appending the contents of a ZIP to a container used to add one
 * file per addStreamToContainer() call, which rewrites the whole container
 * archive every single time. These tests cover the batch variant, which writes
 * the archive exactly once.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class AddDirectoryToZipTest extends TestCase
{
    private string $zip_file;
    private string $source_directory;
    private ZipArchive $zip;

    protected function setUp(): void
    {
        parent::setUp();
        $this->zip_file = tempnam(sys_get_temp_dir(), 'irss_dir_test_');
        $this->zip = new ZipArchive();
        $this->zip->open($this->zip_file, ZipArchive::OVERWRITE);

        $this->source_directory = $this->zip_file . '_source';
        mkdir($this->source_directory . '/docs/nested', 0777, true);
        file_put_contents($this->source_directory . '/root.txt', 'root');
        file_put_contents($this->source_directory . '/docs/one.txt', 'one');
        file_put_contents($this->source_directory . '/docs/nested/two.txt', 'two');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->zip->filename !== '') {
            @$this->zip->close();
        }
        @unlink($this->zip_file);
        $this->removeDirectory($this->source_directory);
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }
        @rmdir($directory);
    }

    private function invoke(string $source_directory, string $path_inside_container): int
    {
        $method = new \ReflectionMethod(ResourceBuilder::class, 'addDirectoryToZIP');
        $builder = (new \ReflectionClass(ResourceBuilder::class))->newInstanceWithoutConstructor();

        return $method->invoke($builder, $this->zip, $source_directory, $path_inside_container);
    }

    /**
     * @return string[]
     */
    private function fileEntries(): array
    {
        $verify = new ZipArchive();
        $verify->open($this->zip_file);
        $names = [];
        for ($i = 0; $i < $verify->numFiles; $i++) {
            $name = $verify->getNameIndex($i);
            if (!str_ends_with($name, '/')) {
                $names[] = $name;
            }
        }
        $verify->close();
        sort($names);

        return $names;
    }

    public function testAllFilesAreAddedKeepingTheDirectoryStructure(): void
    {
        $added = $this->invoke($this->source_directory, '');
        $this->zip->close();

        $this->assertSame(3, $added);
        $this->assertSame(
            ['docs/nested/two.txt', 'docs/one.txt', 'root.txt'],
            $this->fileEntries()
        );
    }

    public function testEntriesArePrefixedWithTheCurrentLevel(): void
    {
        $added = $this->invoke($this->source_directory, 'target');
        $this->zip->close();

        $this->assertSame(3, $added);
        $this->assertSame(
            ['target/docs/nested/two.txt', 'target/docs/one.txt', 'target/root.txt'],
            $this->fileEntries()
        );
    }

    public function testATrailingSeparatorOnTheSourceDirectoryDoesNotAffectTheEntries(): void
    {
        $this->invoke($this->source_directory . DIRECTORY_SEPARATOR, '');
        $this->zip->close();

        $this->assertSame(
            ['docs/nested/two.txt', 'docs/one.txt', 'root.txt'],
            $this->fileEntries()
        );
    }

    public function testNoEntryStartsWithASlash(): void
    {
        $this->invoke($this->source_directory, '/');
        $this->zip->close();

        $entries = $this->fileEntries();
        $this->assertNotEmpty($entries);
        foreach ($entries as $entry) {
            $this->assertStringStartsNotWith('/', $entry, "ZIP entry must not start with '/': {$entry}");
        }
    }

    public function testExistingEntriesAreKept(): void
    {
        $this->zip->addFromString('existing.txt', 'already in the container');

        $this->invoke($this->source_directory, '');
        $this->zip->close();

        $this->assertContains('existing.txt', $this->fileEntries());
    }

    public function testAnEmptyDirectoryAddsNothing(): void
    {
        $empty_directory = $this->source_directory . '/empty';
        mkdir($empty_directory);

        $this->assertSame(0, $this->invoke($empty_directory, ''));
    }
}
