<?php

namespace ILIAS\Filesystem\Provider\FlySystem;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Exception\DirectoryNotFoundException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\MetadataType;
use ILIAS\Filesystem\Visibility;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\RootViolationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class FlySystemDirectoryAccessTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FlySystemDirectoryAccessTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FlySystemDirectoryAccess | MockInterface $subject
     */
    private $subject;
    /**
     * @var FilesystemInterface | MockInterface $filesystemMock
     */
    private $filesystemMock;
    /**
     * @var FlySystemFileAccess | MockInterface $fileAccessMock
     */
    private $fileAccessMock;
    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->filesystemMock = Mockery::mock(FilesystemInterface::class);
        $this->fileAccessMock = Mockery::mock(FlySystemFileAccess::class);

        $this->subject = new FlySystemDirectoryAccess($this->filesystemMock, $this->fileAccessMock);
    }

    /**
     * @Test
     * @small
     */
    public function testHasDirWhichShouldSucceed()
    {
        $path = '/path/to/dir';
        $metadata = [
            'type'      => 'dir',
            'path'      => $path,
            'timestamp' => 10000000
        ];

        $this->filesystemMock
            ->shouldReceive('has')
                ->with($path)
                ->andReturn(true)
            ->getMock()
            ->shouldReceive('getMetadata')
                ->once()
                ->andReturn($metadata);

        $exists = $this->subject->hasDir($path);
        $this->assertTrue($exists);
    }

    /**
     * @Test
     * @small
     */
    public function testHasDirWithFileTargetWhichShouldFail()
    {
        $path = '/path/to/file';
        $metadata = [
            'type'      => 'file',
            'path'      => $path,
            'timestamp' => 10000000
        ];

        $this->filesystemMock
            ->shouldReceive('has')
                ->with($path)
                ->andReturn(true)
            ->getMock()
            ->shouldReceive('getMetadata')
                ->once()
                ->andReturn($metadata);

        $exists = $this->subject->hasDir($path);
        $this->assertFalse($exists);
    }

    /**
     * @Test
     * @small
     */
    public function testHasDirWithoutTypeInformationWhichShouldFail()
    {
        $path = '/path/to/file';
        $metadata = [
            'path'      => $path,
            'timestamp' => 10000000
        ];

        $this->filesystemMock
            ->shouldReceive('has')
                ->with($path)
                ->andReturn(true)
            ->getMock()
            ->shouldReceive('getMetadata')
                ->once()
                ->andReturn($metadata);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not evaluate path type: \"$path\"");

        $exists = $this->subject->hasDir($path);
        $this->assertFalse($exists);
    }

    /**
     * @Test
     * @small
     */
    public function testHasDirWithMissingDirWhichShouldSucceed()
    {
        $path = '/path/to/dir';

        $this->filesystemMock
            ->shouldReceive('has')
            ->with($path)
            ->andReturn(false);

        $exists = $this->subject->hasDir($path);
        $this->assertFalse($exists);
    }

    /**
     * @Test
     * @small
     */
    public function testListContentsWhichShouldSucceed()
    {
        $path = '/path/to/dir';
        $file = [ 'type' => 'file', 'path' => $path ];
        $dir = [ 'type' => 'dir', 'path' => $path ];

        $contentList = [
            $file,
            $dir
        ];

        $this->filesystemMock
            ->shouldReceive('listContents')
            ->once()
            ->withArgs([$path, boolValue()])
            ->andReturn($contentList)
            ->getMock()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true)
            ->getMock()
            ->shouldReceive('getMetadata')
            ->once()
            ->with($path)
            ->andReturn($dir);

        /**
         * @var Metadata[] $content
         */
        $content = $this->subject->listContents($path);

        $this->assertSame($contentList[0]['type'], $content[0]->getType());
        $this->assertSame($contentList[0]['path'], $content[0]->getPath());
        $this->assertTrue($content[0]->isFile());
        $this->assertFalse($content[0]->isDir());
        $this->assertSame($contentList[1]['type'], $content[1]->getType());
        $this->assertSame($contentList[1]['path'], $content[1]->getPath());
        $this->assertTrue($content[1]->isDir());
        $this->assertFalse($content[1]->isFile());
    }

    /**
     * @Test
     * @small
     */
    public function testListContentsWithMissingRootDirectoryWhichShouldFail()
    {
        $path = '/path/to/dir';

        $this->filesystemMock
            ->shouldReceive('has')
            ->once()
            ->andReturn(false);

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage("Directory \"$path\" not found.");

        $this->subject->listContents($path);
    }

    /**
     * @Test
     * @small
     */
    public function testListContentsWithInvalidMetadataWhichShouldFail()
    {
        $path = '/path/to/dir';
        $file = [ 'type' => 'file', 'path' => $path ];
        $dir = [ 'type' => 'dir'];

        $contentList = [
            $file,
            $dir,
            $file
        ];

        $this->filesystemMock
            ->shouldReceive('listContents')
            ->once()
            ->andReturn($contentList)
            ->getMock()
            ->shouldReceive('has')
            ->once()
            ->andReturn(true)
            ->getMock()
            ->shouldReceive('getMetadata')
            ->once()
            ->andReturn($dir);
        
        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Invalid metadata received for path \"$path\"");

        $this->subject->listContents($path);
    }

    /**
     * @Test
     * @small
     */
    public function testCreateDirWhichShouldSucceed()
    {
        $path = '/path/to/dir';
        $access = Visibility::PRIVATE_ACCESS;

        $this->filesystemMock
            ->shouldReceive('createDir')
            ->once()
            ->withArgs([$path, ['visibility' => $access]])
            ->andReturn(true);

        $this->subject->createDir($path, $access);
    }

    /**
     * @Test
     * @small
     */
    public function testCreateDirWithGeneralErrorWhichShouldFail()
    {
        $path = '/path/to/dir';
        $access = Visibility::PRIVATE_ACCESS;

        $this->filesystemMock
            ->shouldReceive('createDir')
            ->once()
            ->withArgs([$path, ['visibility' => $access]])
            ->andReturn(false);
        
        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not create directory \"$path\"");

        $this->subject->createDir($path, $access);
    }

    /**
     * @Test
     * @small
     */
    public function testCreateDirWithInvalidVisibilityWhichShouldFail()
    {
        $path = '/path/to/dir';
        $access = 'invalid';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid visibility expected public or private but got \"$access\".");

        $this->subject->createDir($path, $access);
    }

    /**
     * @Test
     * @small
     */
    public function testCopyDirWhichShouldSucceed()
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

        $subjectMock = Mockery::mock(FlySystemDirectoryAccess::class . '[listContents, hasDir]', [$this->filesystemMock, $this->fileAccessMock]);
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

    /**
     * @Test
     * @small
     */
    public function testCopyDirWithDestinationListContentErrorWhichShouldSucceed()
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

        $subjectMock = Mockery::mock(FlySystemDirectoryAccess::class . '[listContents, hasDir]', [$this->filesystemMock, $this->fileAccessMock]);
        $subjectMock
            ->shouldReceive('listContents')
                ->withArgs([$srcPath, true])
                ->once()
                ->andReturn($fileSourceList)
                ->getMock()
            ->shouldReceive('listContents')
                ->withArgs([$destPath, true])
                ->once()
                ->andThrow(DirectoryNotFoundException::class);

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

    /**
     * @Test
     * @small
     */
    public function testCopyDirWithFullDestinationDirWhichShouldFail()
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

        $subjectMock = Mockery::mock(FlySystemDirectoryAccess::class . '[listContents, hasDir]', [$this->filesystemMock, $this->fileAccessMock]);
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

    /**
     * @Test
     * @small
     */
    public function testCopyDirWithMissingSourceDirWhichShouldFail()
    {
        $srcPath = '/source/path/to/dir';
        $destPath = '/dest/path/to/dir';

        $subjectMock = Mockery::mock(FlySystemDirectoryAccess::class . '[hasDir]', [$this->filesystemMock, $this->fileAccessMock]);
        $subjectMock
            ->shouldReceive('hasDir')
            ->with($srcPath)
            ->once()
            ->andReturn(false);

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage("Directory \"$srcPath\" not found");

        $subjectMock->copyDir($srcPath, $destPath);
    }

    /**
     * @Test
     * @small
     */
    public function testDeleteDirWhichShouldSucceed()
    {
        $path = '/directory/which/should/be/removed';

        $this->filesystemMock
            ->shouldReceive('deleteDir')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->subject->deleteDir($path);
    }

    /**
     * @Test
     * @small
     */
    public function testDeleteDirWithRootViolationWhichShouldFail()
    {
        $path = '';

        $this->filesystemMock
            ->shouldReceive('deleteDir')
            ->once()
            ->with($path)
            ->andThrow(RootViolationException::class);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage('The filesystem root must not be deleted.');

        $this->subject->deleteDir($path);
    }

    /**
     * @Test
     * @small
     */
    public function testDeleteDirWithGeneralErrorWhichShouldFail()
    {
        $path = '/directory/which/should/be/removed';

        $this->filesystemMock
            ->shouldReceive('deleteDir')
            ->once()
            ->with($path)
            ->andReturn(false);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not delete directory \"$path\".");

        $this->subject->deleteDir($path);
    }
}
