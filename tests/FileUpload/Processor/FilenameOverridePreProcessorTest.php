<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class FilenameOverridePreProcessorTest
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FilenameOverridePreProcessorTest extends TestCase
{

    /**
     * @Test
     * @small
     */
    public function testProcessWhichShouldSucceed()
    {
        $filename = 'renamed.ogg';

        $subject = new FilenameSanitizerPreProcessor($filename);
        $stream = Streams::ofString('Awesome stuff');
        $result = $subject->process($stream, new Metadata($filename, $stream->getSize(), 'audio/ogg'));

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Filename changed', $result->getMessage());
    }
}
