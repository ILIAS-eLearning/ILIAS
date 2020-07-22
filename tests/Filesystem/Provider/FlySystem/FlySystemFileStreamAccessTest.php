<?php

namespace Filesystem\Provider\FlySystem;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFileStreamAccess;
use ILIAS\Filesystem\Stream\Streams;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class FlySystemFileStreamAccessTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FlySystemFileStreamAccessTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FilesystemInterface | MockInterface
     */
    private $filesystemMock;
    /**
     * @var FlySystemFileStreamAccess
     */
    private $subject;


    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->filesystemMock = \Mockery::mock(FilesystemInterface::class);
        $this->subject = new FlySystemFileStreamAccess($this->filesystemMock);
    }

    /**
     * @Test
     * @small
     */
    public function testReadStreamWhichShouldSucceed()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = fopen('data://text/plain,' . $fileContent, $fileContent, 'r');

        $this->filesystemMock->shouldReceive('readStream')
            ->once()
            ->with($path)
            ->andReturn($stream);

        $wrappedStream = $this->subject->readStream($path);

        $this->assertSame($fileContent, $wrappedStream->getContents());
    }

    /**
     * @Test
     * @small
     */
    public function testReadStreamWithMissingFileWhichShouldFail()
    {
        $path = '/path/to/your/file';

        $this->filesystemMock->shouldReceive('readStream')
            ->once()
            ->with($path)
            ->andThrow(FileNotFoundException::class);

        $this->expectException(\ILIAS\Filesystem\Exception\FileNotFoundException::class);
        $this->expectExceptionMessage("File \"$path\" not found.");

        $this->subject->readStream($path);
    }

    /**
     * @Test
     * @small
     */
    public function testReadStreamWithGeneralFailureWhichShouldFail()
    {
        $path = '/path/to/your/file';

        $this->filesystemMock->shouldReceive('readStream')
            ->once()
            ->with($path)
            ->andReturn(false);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not open stream for file \"$path\"");

        $this->subject->readStream($path);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteStreamWhichShouldSucceed()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('writeStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andReturn(true);

        $this->subject->writeStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteStreamWithDetachedStreamWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);
        $stream->detach();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given stream must not be detached.');

        $this->subject->writeStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteStreamWithExistingFileWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('writeStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andThrow(FileExistsException::class);
        
        $this->expectException(FileAlreadyExistsException::class);
        $this->expectExceptionMessage("File \"$path\" already exists.");

        $this->subject->writeStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteStreamWithFailingAdapterWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('writeStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andReturn(false);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not write stream to file \"$path\"");

        $this->subject->writeStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testPutStreamWhichShouldSucceed()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('putStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andReturn(true);

        $this->subject->putStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testPutStreamWithGeneralFailureWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('putStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andReturn(false);

        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not put stream content into \"$path\"");

        $this->subject->putStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testPutStreamWithDetachedStreamWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);
        $stream->detach();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given stream must not be detached.');

        $this->subject->putStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testUpdateStreamWhichShouldSucceed()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('updateStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andReturn(true);

        $this->subject->updateStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testUpdateStreamWithDetachedStreamWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);
        $stream->detach();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given stream must not be detached.');

        $this->subject->updateStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testUpdateStreamWithGeneralFailureWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('updateStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andReturn(false);
        
        $this->expectException(IOException::class);
        $this->expectExceptionMessage("Could not update file \"$path\"");

        $this->subject->updateStream($path, $stream);
    }

    /**
     * @Test
     * @small
     */
    public function testUpdateStreamWithMissingFileWhichShouldFail()
    {
        $path = '/path/to/your/file';
        $fileContent = 'Awesome file content';
        $stream = Streams::ofString($fileContent);

        $this->filesystemMock->shouldReceive('updateStream')
            ->once()
            ->withArgs([$path, resourceValue()])
            ->andThrow(FileNotFoundException::class);

        $this->expectException(\ILIAS\Filesystem\Exception\FileNotFoundException::class);
        $this->expectExceptionMessage("File \"$path\" not found.");

        $this->subject->updateStream($path, $stream);
    }
}
