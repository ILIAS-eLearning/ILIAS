<?php

namespace ILIAS\Filesystem\Stream;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Util\PHPStreamFunctions;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class StreamTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class StreamTest extends TestCase
{

    /**
     * @var Mockery\MockInterface
     */
    public static $functions;

    private function createResource($content, $mode)
    {
        //call the root fopen function \ required!
        return \fopen("data://text/plain,$content", $mode);
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        self::$functions = Mockery::mock();
    }

    /**
     * @Test
     * @small
     */
    public function testDetachWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $detachedResource = $subject->detach();

        //check that the resource is valid.
        $this->assertTrue(is_resource($detachedResource));
        $this->assertSame($resource, $detachedResource);

        //Can't test the subject because psr-7 defines that the stream is in an unusable after the detach operation.
    }

    /**
     * @Test
     * @small
     */
    public function testDetachDoubleInvocationWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        //check that the detached resource is valid.
        $detachedResource = $subject->detach();
        $this->assertTrue(is_resource($detachedResource));

        //must be null because the stream was already detached.
        $detachedResource = $subject->detach();
        $this->assertNull($detachedResource);
    }

    /**
     * @Test
     * @small
     */
    public function testGetSizeWithStatsWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $correctSize = strlen($content);
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $size = $subject->getSize();
        $this->assertSame($correctSize, $size);
    }

    /**
     * @Test
     * @small
     */
    public function testGetSizeWithOptionsWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $correctSize = 900;
        $mode = 'r';
        $resource = $this->createResource($content, $mode);
        $options = new StreamOptions([], $correctSize);

        $subject = new Stream($resource, $options);

        $size = $subject->getSize();
        $this->assertSame($correctSize, $size);
    }

    /**
     * @Test
     * @small
     */
    public function testGetSizeWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $size = $subject->getSize();
        $this->assertNull($size);
    }

    /**
     * @Test
     * @small
     */
    public function testCloseWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $subject->close();
        $this->assertFalse(is_resource($resource));
    }

    /**
     * @Test
     * @small
     */
    public function testCloseWithDetachedStreamWhichShouldDoNothing()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $actualResource = $subject->detach();
        $subject->close();

        $this->assertTrue(is_resource($actualResource));
    }

    /**
     * @Test
     * @small
     */
    public function testTellWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $offset = 5;
        $resource = $this->createResource($content, $mode);
        fseek($resource, $offset);

        $subject = new Stream($resource);

        $actualPosition = $subject->tell();
        $this->assertSame($offset, $actualPosition);
    }

    /**
     * @Test
     * @small
     */
    public function testTellWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $subject->tell();
    }

    /**
     * @Test
     * @small
     */
    public function testTellWithFtellFailureWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        //load mock class
        $functionMock = Mockery::mock('alias:' . PHPStreamFunctions::class);
        $functionMock->shouldReceive('ftell')
            ->once()
            ->with($resource)
            ->andReturn(false);

        $functionMock->shouldReceive('fclose')
            ->once()
            ->with($resource);

        $subject = new Stream($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to determine stream position');

        $subject->tell();
    }

    /**
     * @Test
     * @small
     */
    public function testEofWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $offset = strlen($content); // end of stream
        $resource = $this->createResource($content, $mode);
        fseek($resource, $offset);  // seek to end of stream
        fgets($resource, 2); // we need to hit the end of the stream or eof returns false. (https://bugs.php.net/bug.php?id=35136)

        $subject = new Stream($resource);

        $endOfFileReached = $subject->eof();
        $this->assertTrue($endOfFileReached);
    }

    /**
     * @Test
     * @small
     */
    public function testEofWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $subject->eof();
    }


    /**
     * @Test
     * @small
     */
    public function testSeekWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $offset = 5;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $subject->seek($offset);
        $this->assertSame($offset, ftell($resource));
    }

    /**
     * @Test
     * @small
     */
    public function testSeekWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $offset = 5;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $subject->seek($offset);
    }

    /**
     * @Test
     * @small
     */
    public function testSeekWithNotSeekableStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $offset = 5;
        $resource = $this->createResource($content, $mode);

        $subjectMock = Mockery::mock(Stream::class . '[isSeekable]', [$resource]);

        $subjectMock
            ->shouldReceive('isSeekable')
            ->once()
            ->andReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is not seekable');

        $subjectMock->seek($offset);
    }

    /**
     * @Test
     * @small
     */
    public function testSeekWithFseekFailureWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $offset = 5;
        $whence = SEEK_SET;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        
        //load mock class
        $functionMock = Mockery::mock('alias:' . PHPStreamFunctions::class);
        $functionMock->shouldReceive('fseek')
            ->once()
            ->withArgs([$resource, $offset, $whence])
            ->andReturn(-1);

        $functionMock->shouldReceive('fclose')
            ->once()
            ->with($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Unable to seek to stream position \"$offset\" with whence \"$whence\"");

        $subject->seek($offset);
    }

    /**
     * @Test
     * @small
     */
    public function testReadWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $expectedResult = "awesome";
        $mode = 'r';
        $length = 7;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $text = $subject->read($length);
        $this->assertSame($expectedResult, $text);
    }

    /**
     * @Test
     * @small
     */
    public function testReadWithZeroLengthWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $expectedResult = "";
        $mode = 'r';
        $length = 0;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $text = $subject->read($length);
        $this->assertSame($expectedResult, $text);
    }

    /**
     * @Test
     * @small
     */
    public function testReadWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $length = 7;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $subject->read($length);
    }

    /**
     * @Test
     * @small
     */
    public function testReadWithNegativeLengthWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $length = -2;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Length parameter must not be negative');

        $subject->read($length);
    }

    /**
     * @Test
     * @small
     */
    public function testReadWithUnreadableStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'w';
        $length = 3;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not read from non-readable stream');

        $subject->read($length);
    }

    /**
     * @Test
     * @small
     */
    public function testReadWithFailingFreadCallWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $length = 3;
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        //load mock class
        $functionMock = Mockery::mock('alias:' . PHPStreamFunctions::class);

        $functionMock->shouldReceive('fread')
            ->once()
            ->withArgs([$resource, $length])
            ->andReturn(false);

        $functionMock->shouldReceive('fclose')
            ->once()
            ->with($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to read from stream');

        $subject->read($length);
    }

    /**
     * @Test
     * @small
     */
    public function testGetContentsWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $text = $subject->getContents();
        $this->assertSame($content, $text);
    }

    /**
     * @Test
     * @small
     */
    public function testGetContentsWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $subject->getContents();
    }

    /**
     * @Test
     * @small
     */
    public function testGetContentsWithFailingStreamGetContentsCallWhichShouldFail()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        //load mock class
        $functionMock = Mockery::mock('alias:' . PHPStreamFunctions::class);

        $functionMock->shouldReceive('stream_get_contents')
            ->once()
            ->with($resource)
            ->andReturn(false);

        $functionMock->shouldReceive('fclose')
            ->once()
            ->with($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to read stream contents');

        $subject->getContents();
    }

    /**
     * @Test
     * @small
     */
    public function testToStringWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $text = $subject->__toString();
        $this->assertSame($content, $text);
    }

    /**
     * @Test
     * @small
     *
     * to string must never fail
     */
    public function testToStringWithErrorWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $expectedResult = '';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = Mockery::mock(Stream::class . '[rewind]', [$resource]);

        $subject->shouldDeferMissing();
        $subject->shouldReceive('rewind')
            ->once()
            ->andThrow(\RuntimeException::class);

        $text = $subject->__toString();
        $this->assertSame($expectedResult, $text);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteWhichShouldSucceed()
    {
        $content = 'awesome content stream';
        $newContent = '!';
        $byteCount = strlen($newContent);
        $mode = 'r+';
        $resource = fopen('php://memory', $mode);
        PHPStreamFunctions::fwrite($resource, $content);

        $subject = new Stream($resource);
        $currentSize = $subject->getSize();

        $numberOfBytesWritten = $subject->write($newContent);
        $newSize = $subject->getSize();

        $this->assertSame($byteCount, $numberOfBytesWritten, 'The count of bytes passed to write must match the written bytes after the operation.');
        $this->assertGreaterThan($currentSize, $newSize, 'The new size must be grater than the old size because we wrote to the stream.');
    }

    /**
     * @Test
     * @small
     */
    public function testWriteWithDetachedStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $newContent = '!';
        $mode = 'w';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);
        $subject->detach();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stream is detached');

        $subject->write($newContent);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteWithReadOnlyStreamWhichShouldFail()
    {
        $content = 'awesome content stream';
        $newContent = '!';
        $mode = 'r';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not write to a non-writable stream');

        $subject->write($newContent);
    }

    /**
     * @Test
     * @small
     */
    public function testWriteWithFailingFwriteCallWhichShouldFail()
    {
        $content = 'awesome content stream';
        $newContent = '!';
        $mode = 'a+';
        $resource = $this->createResource($content, $mode);

        $subject = new Stream($resource);

        //load mock class
        $functionMock = Mockery::mock('alias:' . PHPStreamFunctions::class);

        $functionMock->shouldReceive('fwrite')
            ->once()
            ->withArgs([$resource, $newContent])
            ->andReturn(false);

        $functionMock->shouldReceive('fclose')
            ->once()
            ->with($resource);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to write to stream');

        $subject->write($newContent);
    }
}
