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

namespace ILIAS\FileUpload\Activities;

use ILIAS\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TempFileStoreTest extends TestCase
{
    private TempFileStore $store;

    protected function setUp(): void
    {
        // the store only talks to the filesystem when it reads or writes,
        // the tests that do so create their own mock
        $this->store = new TempFileStore($this->createStub(Filesystem::class));
    }

    public function testAHandleIsAFolderOfItsOwnPlusTheFileName(): void
    {
        $handle = $this->store->newHandle('lecture notes.pdf');

        $this->assertMatchesRegularExpression('/^[a-f0-9]{16}\/lecture_notes.pdf$/', $handle);
        $this->assertSame('lecture_notes.pdf', $this->store->getFilename($handle));
        $this->assertSame(TempFileStore::DIRECTORY . '/' . $handle, $this->store->pathOf($handle));
    }

    public function testEveryHandleGetsItsOwnFolder(): void
    {
        $this->assertNotSame(
            $this->store->newHandle('file.txt'),
            $this->store->newHandle('file.txt')
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public static function badHandles(): array
    {
        return [
            'empty' => [''],
            'no folder' => ['file.txt'],
            'traversal' => ['../../../etc/passwd'],
            'traversal in file name' => ['0123456789abcdef/../../ilias.ini.php'],
            'foreign folder' => ['someone_elses_folder/file.txt'],
        ];
    }

    #[DataProvider('badHandles')]
    public function testHandlesThatCouldEscapeTheStoreAreRejected(string $handle): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->store->pathOf($handle);
    }

    public function testStoringPutsTheContentUnderTheHandle(): void
    {
        $filesystem = $this->createMock(Filesystem::class);
        $store = new TempFileStore($filesystem);

        $filesystem
            ->expects($this->once())
            ->method('put')
            ->with(
                $this->matchesRegularExpression(
                    '/^' . TempFileStore::DIRECTORY . '\/[a-f0-9]{16}\/notes.txt$/'
                ),
                'some content'
            );

        $handle = $store->store('notes.txt', 'some content');

        $this->assertSame('notes.txt', $store->getFilename($handle));
    }

    public function testDeletingRemovesTheFileAndItsFolder(): void
    {
        $handle = '0123456789abcdef/notes.txt';

        $filesystem = $this->createMock(Filesystem::class);

        $filesystem
            ->expects($this->once())
            ->method('delete')
            ->with(TempFileStore::DIRECTORY . '/' . $handle);

        $filesystem
            ->expects($this->once())
            ->method('deleteDir')
            ->with(TempFileStore::DIRECTORY . '/0123456789abcdef');

        (new TempFileStore($filesystem))->delete($handle);
    }

    public function testAnUnusableFileNameStillGivesAUsableHandle(): void
    {
        $handle = $this->store->newHandle('...');

        $this->assertSame('file', $this->store->getFilename($handle));
        $this->assertIsString($this->store->pathOf($handle));
    }
}
