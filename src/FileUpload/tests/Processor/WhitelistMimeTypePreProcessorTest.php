<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

/**
 * Class WhitelistMimeTypePreProcessorTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class WhitelistMimeTypePreProcessorTest extends TestCase
{
    use MockeryPHPUnitIntegration;


    /**
     * @Test
     * @small
     */
    public function testProcessWithWhitelistedMimeTypeWhichShouldSucceed()
    {
        $whitelist = ['text/html', 'audio/ogg'];
        $subject = new WhitelistMimeTypePreProcessor($whitelist);
        $metadata = new Metadata('testfile.html', 4000, $whitelist[0]);

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Entity comply with mime type whitelist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithWhitelistedAnyKindOfTextMimeTypeWhichShouldSucceed()
    {
        $whitelist = ['text/*', '*/ogg'];
        $subject = new WhitelistMimeTypePreProcessor($whitelist);
        $metadata = new Metadata('testfile.html', 4000, 'text/html');

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Entity comply with mime type whitelist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithWhitelistedAnyKindOfOggMimeTypeWhichShouldSucceed()
    {
        $whitelist = ['text/html', '*/ogg'];
        $subject = new WhitelistMimeTypePreProcessor($whitelist);
        $metadata = new Metadata('testfile.ogg', 4000, 'audio/ogg');

        $result = $subject->process(Mockery::mock(FileStream::class), $metadata);
        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Entity comply with mime type whitelist.', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testCreateSubjectWithAnyKindOfMimeTypeWhichShouldFail()
    {
        $whitelist = ['audio/ogg', '*/*'];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mime type */* matches all mime types which renders the whole whitelist useless.');

        $subject = new WhitelistMimeTypePreProcessor($whitelist);
    }
}
