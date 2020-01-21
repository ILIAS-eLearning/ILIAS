<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistExtensionPreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class WhitelistExtensionPreProcessorTest extends TestCase
{

    /**
     * @Test
     * @small
     */
    public function testProcessWhichShouldSucceed()
    {
        $extensions = ['jpg', 'svg'];
        $filename = 'hello.svg';
        $ext = 'svg';

        $subject = new WhitelistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'image/svg'));
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Extension complies with whitelist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithoutExtensionWhichShouldSucceed()
    {
        $extensions = ['jpg', ''];
        $filename = 'hello';

        $subject = new WhitelistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'image/svg'));
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Extension complies with whitelist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithoutWhitelistedExtensionWhichShouldGetRejected()
    {
        $extensions = ['jpg', 'exe'];
        $filename = 'hello.svg';

        $subject = new WhitelistExtensionPreProcessor($extensions);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'image/svg'));
        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Extension don\'t complies with whitelist.', $result->getMessage());
    }
}
