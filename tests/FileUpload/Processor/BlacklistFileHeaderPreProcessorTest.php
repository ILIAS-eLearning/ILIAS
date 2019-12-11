<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class BlacklistFileHeaderPreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class BlacklistFileHeaderPreProcessorTest extends TestCase
{

    /**
     * @Test
     * @small
     */
    public function testProcessWhichShouldSucceed()
    {
        $fileHeaderBlacklist = hex2bin('FFD8FF'); //jpg header start
        $fileHeaderStart = hex2bin('FFD8FB'); //jpg header start
        $trailer = hex2bin('FFD9'); //jpg trailer
        $subject = new BlacklistFileHeaderPreProcessor($fileHeaderBlacklist);
        $stream = Streams::ofString("$fileHeaderStart bla bla bla $trailer");
        $stream->rewind();

        $result = $subject->process($stream, new Metadata('hello.jpg', $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('File header does not match blacklist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithHeaderMismatchWhichShouldGetRejected()
    {
        $fileHeaderStart = hex2bin('FFD8FF'); //jpg header start
        $trailer = hex2bin('FFD9'); //jpg trailer
        $subject = new BlacklistFileHeaderPreProcessor($fileHeaderStart);
        $stream = Streams::ofString("$fileHeaderStart bla bla bla $trailer");
        $stream->rewind();

        $result = $subject->process($stream, new Metadata('hello.jpg', $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('File header matches blacklist.', $result->getMessage());
    }
}
