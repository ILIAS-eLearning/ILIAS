<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class BlacklistMimeTypePreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class BlacklistMimeTypePreProcessorTest extends TestCase
{

    /**
     * @Test
     * @small
     */
    public function testProcessWithBlacklistedMimeTypeWhichShouldGetRejected()
    {
        $blacklist = ['text/html', 'audio/ogg'];
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata('testfile.html', 4000, $blacklist[0]);

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $metadata->getMimeType() . ' is blacklisted.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithBlacklistedAnyKindOfTextMimeTypeWhichGetRejected()
    {
        $blacklist = ['text/*', '*/ogg'];
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata('testfile.html', 4000, 'text/html');

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $metadata->getMimeType() . ' is blacklisted.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithBlacklistedAnyKindOfOggMimeTypeWhichGetRejected()
    {
        $blacklist = ['text/html', '*/ogg'];
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata('testfile.ogg', 4000, 'audio/ogg');

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $metadata->getMimeType() . ' is blacklisted.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testCreateSubjectWithAnyKindOfMimeTypeWhichShouldFail()
    {
        $blacklist = ['audio/ogg', '*/*'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mime type */* matches all mime types which would black all files.');

        $subject = new BlacklistMimeTypePreProcessor($blacklist);
    }
}
