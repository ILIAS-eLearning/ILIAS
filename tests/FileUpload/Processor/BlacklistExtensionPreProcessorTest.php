<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class BlacklistExtensionPreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class BlacklistExtensionPreProcessorTest extends TestCase
{

    /**
     * @Test
     * @small
     */
    public function testProcessWhichShouldSucceed()
    {
        $extensions = ['jpg', 'svg'];
        $filename = 'hello.ogg';

        $subject = new BlacklistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'audio/ogg'));

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Extension is not blacklisted.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithBlacklistedEmptyExtensionWhichShouldGetRejected()
    {
        $extensions = ['jpg', ''];
        $filename = 'hello';

        $subject = new BlacklistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'text/plain'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Extension is blacklisted.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithBlacklistedExtensionWhichShouldGetRejected()
    {
        $extensions = ['jpg', 'exe'];
        $filename = 'hello.jpg';

        $subject = new BlacklistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'image/jpg'));

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Extension is blacklisted.', $result->getMessage());
    }
}
