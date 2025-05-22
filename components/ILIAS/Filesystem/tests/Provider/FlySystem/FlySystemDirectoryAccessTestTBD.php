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

namespace ILIAS\Filesystem\Provider\FlySystem;

use Hamcrest\Util;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;

Util::registerGlobalFunctions();

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\MetadataType;
use ILIAS\Filesystem\Visibility;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\FilesystemOperator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use League\Flysystem\UnableToCreateDirectory;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\FileAttributes;
use League\Flysystem\DirectoryAttributes;

/**
 * @author                 Nicolas Schäfli <ns@studer-raimann.ch>
 * @author                 Fabian Schmid <fabian@sr.solutions>
 */
class FlySystemDirectoryAccessTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private FlySystemDirectoryAccess|MockInterface $subject;
    /**
     * @var FilesystemInterface | MockInterface $filesystemMock
     */
    private LegacyMockInterface $filesystemMock;
    private FlySystemFileAccess|MockInterface $fileAccessMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystemMock = Mockery::mock(FilesystemOperator::class);
        $this->fileAccessMock = Mockery::mock(FlySystemFileAccess::class);

        $this->subject = new FlySystemDirectoryAccess($this->filesystemMock, $this->fileAccessMock);
    }

    #[Test]

    public function testHasDirWhichShouldSucceed(): void
    {
        $path = '/path/to/dir';

        $this->filesystemMock
            ->shouldReceive('directoryExists')
            ->with($path)
            ->andReturn(true);

        $exists = $this->subject->hasDir($path);
        $this->assertTrue($exists);
    }

    #[Test]

    public function testHasDirWithFileTargetWhichShouldFail(): void
    {
        $path = '/path/to/file';

        $this->filesystemMock
            ->shouldReceive('directoryExists')
            ->with($path)
            ->andReturn(false);

        $exists = $this->subject->hasDir($path);
        $this->assertFalse($exists);
    }

    #[Test]

    public function testHasDirWithMissingDirWhichShouldSucceed(): void
    {
        $path = '/path/to/dir';

        $this->filesystemMock
            ->shouldReceive('directoryExists')
            ->with($path)
            ->andReturn(false);

        $exists = $this->subject->hasDir($path);
        $this->assertFalse($exists);
    }

    #[Test]

    public function testListContentsWhichShouldSucceed(): void
    {
        $path = '/path/to/dir';
        $file = ['type' => 'file', 'path' => $path];
        $dir = ['type' => 'dir', 'path' => $path];

        $content_list = [
            FileAttributes::fromArray($file),
            DirectoryAttributes::fromArray($dir)
        ];
        $contentListContainer = new DirectoryListing($content_list);



        $this->filesystemMock
            ->shouldReceive('directoryExists')
            ->once()
            ->with($path)
            ->andReturn(true)
            ->getMock()
            ->shouldReceive('listContents')
            ->once()
            ->withArgs([$path, false])
            ->andReturn($contentListContainer);

        /**
         * @var Metadata[] $content
         * @var Metadata[] $content
         */
        $content = $this->subject->listContents($path);

        $this->assertSame($content_list[0]['type'], $content[0]->getType());
        $this->assertSame($content_list[0]['path'], $content[0]->getPath());
        $this->assertTrue($content[0]->isFile());
        $this->assertFalse($content[0]->isDir());
        $this->assertSame($content_list[1]['type'], $content[1]->getType());
        $this->assertSame($content_list[1]['path'], $content[1]->getPath());
        $this->assertTrue($content[1]->isDir());
        $this->assertFalse($content[1]->isFile());
    }

    #[Test]

    public function testListContentsWithMissingRootDirectoryWhichShouldFail(): void
    {
        $path = '/path/to/dir';

        $this->filesystemMock
            ->shouldReceive('directoryExists')
            ->once()
            ->andReturn(false);

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage("Directory \"$path\" not found.");

        $this->subject->listContents($path);
    }

    #[Test]

    public function testListContentsWithInvalidMetadataWhichShouldFail(): void
    {
        $path = '/path/to/dir';
        $file = ['type' => 'file', 'path' => $path];
        $dir = ['type' => 'dir'];

        $contentList = [
            $file,
            $dir,
            $file
        ];

        $contentListContainer = new DirectoryListing($contentList);

        $this->filesystemMock
            ->shouldReceive('directoryExists')
            ->once()
            ->andReturn(false)
            ->shouldReceive('listContents')
            ->never()
            ->andReturn($contentListContainer)
            ->getMock()
            ->shouldReceive('getMetadata')
            ->never()
            ->andReturn($dir);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Directory \"$path\" not found.");

        $this->subject->listContents($path);
    }

    #[Test]

    public function testCreateDirWhichShouldSucceed(): void
    {
        $path = '/path/to/dir';
        $access = Visibility::PRIVATE_ACCESS;

        $this->filesystemMock
            ->shouldReceive('createDirectory')
            ->once()
            ->withArgs([$path, ['visibility' => $access]])
            ->andReturn(true);

        $this->subject->createDir($path, $access);
    }

    #[Test]

    public function testCreateDirWithGeneralErrorWhichShouldFail(): void
    {
        $path = '/path/to/dir';
        $access = Visibility::PRIVATE_ACCESS;

        $this->filesystemMock
            ->shouldReceive('createDirectory')
            ->once()
            ->withArgs([$path, ['visibility' => $access]])
            ->andThrow(UnableToCreateDirectory::class);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not create directory \"$path\"");

        $this->subject->createDir($path, $access);
    }

    #[Test]

    public function testCreateDirWithInvalidVisibilityWhichShouldFail(): void
    {
        $path = '/path/to/dir';
        $access = 'invalid';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid visibility expected public or private but got \"$access\".");

        $this->subject->createDir($path, $access);
    }

    #[Test]

    public function testCopyDirWhichShouldSucceed(): void
    {
        $srcPath = '/source/path/to/dir';
        $destPath = '/dest/path/to/dir';

        /**
         * @var Metadata[] $fileSourceList
         */
        $fileSourceList = [
            new Metadata("$srcPath/hello1", MetadataType::FILE),
            new Metadata("$srcPath/helloDir", MetadataType::DIRECTORY),
            new Metadata("$srcPath/hello2", MetadataType::FILE),
            new Metadata("$srcPath/hello/subhello1", MetadataType::FILE),
            new Metadata("$srcPath/helloDir2", MetadataType::DIRECTORY),
            new Metadata("$srcPath/hello3", MetadataType::FILE),
            new Metadata("$srcPath/hello/subhello2", MetadataType::FILE),
        ];

        $fileDestinationList = [];

        $subjectMock = Mockery::mock(
            FlySystemDirectoryAccess::class . '[listContents, hasDir]',
            [$this->filesystemMock, $this->fileAccessMock]
        );
        $subjectMock
            ->shouldReceive('listContents')
            ->withArgs([$srcPath, true])
            ->once()
            ->andReturn($fileSourceList)
            ->getMock()
            ->shouldReceive('listContents')
            ->withArgs([$destPath, true])
            ->once()
            ->andReturn($fileDestinationList);

        $subjectMock
            ->shouldReceive('hasDir')
            ->with($srcPath)
            ->once()
            ->andReturn(true);

        $this->fileAccessMock
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[0]->getPath(), "$destPath/hello1"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[2]->getPath(), "$destPath/hello2"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[3]->getPath(), "$destPath/hello/subhello1"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[5]->getPath(), "$destPath/hello3"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[6]->getPath(), "$destPath/hello/subhello2"])
            ->once()
            ->getMock();

        $subjectMock->copyDir($srcPath, $destPath);
    }

    #[Test]

    public function testCopyDirWithDestinationListContentErrorWhichShouldSucceed(): void
    {
        $srcPath = '/source/path/to/dir';
        $destPath = '/dest/path/to/dir';

        /**
         * @var Metadata[] $fileSourceList
         */
        $fileSourceList = [
            new Metadata("$srcPath/hello1", MetadataType::FILE),
            new Metadata("$srcPath/helloDir", MetadataType::DIRECTORY),
            new Metadata("$srcPath/hello2", MetadataType::FILE),
            new Metadata("$srcPath/hello/subhello1", MetadataType::FILE),
            new Metadata("$srcPath/helloDir2", MetadataType::DIRECTORY),
            new Metadata("$srcPath/hello3", MetadataType::FILE),
            new Metadata("$srcPath/hello/subhello2", MetadataType::FILE),
        ];

        $subjectMock = Mockery::mock(
            FlySystemDirectoryAccess::class . '[listContents, hasDir]',
            [$this->filesystemMock, $this->fileAccessMock]
        );
        $subjectMock
            ->shouldReceive('listContents')
            ->withArgs([$srcPath, true])
            ->once()
            ->andReturn($fileSourceList)
            ->getMock()
            ->shouldReceive('listContents')
            ->withArgs([$destPath, true])
            ->once()
            ->andThrow(UnableToRetrieveMetadata::class);

        $subjectMock
            ->shouldReceive('hasDir')
            ->with($srcPath)
            ->once()
            ->andReturn(true);

        $this->fileAccessMock
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[0]->getPath(), "$destPath/hello1"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[2]->getPath(), "$destPath/hello2"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[3]->getPath(), "$destPath/hello/subhello1"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[5]->getPath(), "$destPath/hello3"])
            ->once()
            ->getMock()
            ->shouldReceive('copy')
            ->withArgs([$fileSourceList[6]->getPath(), "$destPath/hello/subhello2"])
            ->once()
            ->getMock();

        $subjectMock->copyDir($srcPath, $destPath);
    }

    #[Test]

    public function testCopyDirWithFullDestinationDirWhichShouldFail(): void
    {
        $srcPath = '/source/path/to/dir';
        $destPath = '/dest/path/to/dir';

        /**
         * @var Metadata[] $fileDestinationList
         */
        $fileDestinationList = [
            new Metadata("$srcPath/hello1", MetadataType::FILE),
            new Metadata("$srcPath/helloDir", MetadataType::DIRECTORY),
            new Metadata("$srcPath/hello2", MetadataType::FILE),
            new Metadata("$srcPath/hello/subhello1", MetadataType::FILE),
            new Metadata("$srcPath/helloDir2", MetadataType::DIRECTORY),
            new Metadata("$srcPath/hello3", MetadataType::FILE),
            new Metadata("$srcPath/hello/subhello2", MetadataType::FILE),
        ];

        $subjectMock = Mockery::mock(
            FlySystemDirectoryAccess::class . '[listContents, hasDir]',
            [$this->filesystemMock, $this->fileAccessMock]
        );
        $subjectMock
            ->shouldReceive('listContents')
            ->withArgs([$destPath, true])
            ->once()
            ->andReturn($fileDestinationList);

        $subjectMock
            ->shouldReceive('hasDir')
            ->with($srcPath)
            ->once()
            ->andReturn(true);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Destination \"$destPath\" is not empty can not copy files.");

        $subjectMock->copyDir($srcPath, $destPath);
    }

    #[Test]

    public function testCopyDirWithMissingSourceDirWhichShouldFail(): void
    {
        $srcPath = '/source/path/to/dir';
        $destPath = '/dest/path/to/dir';

        $subjectMock = Mockery::mock(
            FlySystemDirectoryAccess::class . '[hasDir]',
            [$this->filesystemMock, $this->fileAccessMock]
        );
        $subjectMock
            ->shouldReceive('hasDir')
            ->with($srcPath)
            ->once()
            ->andReturn(false);

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage("Directory \"$srcPath\" not found");

        $subjectMock->copyDir($srcPath, $destPath);
    }

    #[Test]

    public function testDeleteDirWhichShouldSucceed(): void
    {
        $path = '/directory/which/should/be/removed';

        $this->filesystemMock
            ->shouldReceive('deleteDirectory')
            ->once()
            ->with($path)
            ->getMock()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(false);

        $this->subject->deleteDir($path);
    }

    #[Test]

    public function testDeleteDirWithRootViolationWhichShouldFail(): void
    {
        $path = '';

        $this->filesystemMock
            ->shouldReceive('deleteDirectory')
            ->once()
            ->with($path)
            ->andThrow(\Exception::class);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage('Could not delete directory "".');

        $this->subject->deleteDir($path);
    }

    #[Test]

    public function testDeleteDirWithGeneralErrorWhichShouldFail(): void
    {
        $path = '/directory/which/should/be/removed';

        $this->filesystemMock
            ->shouldReceive('deleteDirectory')
            ->once()
            ->with($path)
            ->andThrow(\Exception::class);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not delete directory \"$path\".");

        $this->subject->deleteDir($path);
    }
}
