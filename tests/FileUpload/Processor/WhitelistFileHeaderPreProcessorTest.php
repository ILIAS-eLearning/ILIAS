<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistFileHeaderPreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class WhitelistFileHeaderPreProcessorTest extends TestCase
{

    /**
     * @Test
     * @small
     */
    public function testProcessWhichShouldSucceed()
    {
        $fileHeaderStart = hex2bin('FFD8FF'); //jpg header start
        $trailer = hex2bin('FFD9'); //jpg trailer
        $subject = new WhitelistFileHeaderPreProcessor($fileHeaderStart);
        $stream = Streams::ofString("$fileHeaderStart bla bla bla $trailer");
        $stream->rewind();

        $result = $subject->process($stream, new Metadata('hello.jpg', $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('File header complies with whitelist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithHeaderMismatchWhichShouldGetRejected()
    {
        $fileHeaderWhitelist = hex2bin('FFD8FF'); //jpg header start
        $fileHeaderStart = hex2bin('FFD8FB'); //jpg header start
        $trailer = hex2bin('FFD9'); //jpg trailer
        $subject = new WhitelistFileHeaderPreProcessor($fileHeaderWhitelist);
        $stream = Streams::ofString("$fileHeaderStart bla bla bla $trailer");
        $stream->rewind();

        $result = $subject->process($stream, new Metadata('hello.jpg', $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('File header don\'t complies with whitelist.', $result->getMessage());
    }
}
