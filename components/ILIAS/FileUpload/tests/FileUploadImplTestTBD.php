<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\FileUpload;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;

require_once('./vendor/composer/vendor/autoload.php');

use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\Processor\PreProcessorManager;
use ILIAS\HTTP\GlobalHttpState;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Class FileUploadImplTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class FileUploadImplTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FileUpload $subject
     */
    private FileUploadImpl $subject;
    /**
     * @var MockInterface | PreProcessorManager $prePorcessorManagerMock
     */
    private $prePorcessorManagerMock;
    /**
     * @var MockInterface | Filesystems $filesystemsMock
     */
    private $filesystemsMock;
    /**
     * @var MockInterface | GlobalHttpState $globalHttpStateMock
     */
    private $globalHttpStateMock;


    #[Test]

    public function testRegisterWhichShouldSucceed(): void
    {
        $processorMock = \Mockery::mock(PreProcessor::class);
        $this->prePorcessorManagerMock->shouldReceive('with')
            ->once()
            ->with($processorMock);

        $this->subject->register($processorMock);
    }

    #[Test]

    public function testRegisterWithProcessedFilesWhichShouldFail(): void
    {
        $processorMock = \Mockery::mock(PreProcessor::class);

        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
                ->once()
                ->andReturn([]);

        $this->expectException(IllegalStateException::class);
        $this->expectExceptionMessage('Can not register processor after the upload was processed.');

        $this->subject->process();
        $this->subject->register($processorMock);
    }

    #[Test]

    public function testProcessWhichShouldSucceed(): void
    {
        $processingResult = new ProcessingStatus(ProcessingStatus::OK, 'All green!');
        $uploadedFile = Mockery::mock(UploadedFileInterface::class);
        $uploadedFile
            ->shouldReceive('getClientFilename')
                ->once()
                ->andReturn('hello.txt')
                ->getMock()
            ->shouldReceive('getSize')
                ->once()
                ->andReturn(10)
                ->getMock()
            ->shouldReceive('getClientMediaType')
                ->once()
                ->andReturn('text/plain')
                ->getMock()
            ->shouldReceive('getError')
                ->once()
                ->andReturn(UPLOAD_ERR_OK)
                ->getMock()
            ->shouldReceive('getStream')
                ->twice()
                ->andReturn(Streams::ofString("Text file content."));

        $uploadedFiles = [
            $uploadedFile
        ];

        $this->prePorcessorManagerMock->shouldReceive('process')
            ->withAnyArgs()
            ->once()
            ->andReturn($processingResult);

        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
            ->once()
            ->andReturn($uploadedFiles);

        $this->subject->process();
    }

    #[Test]

    public function testProcessWithFailedUploadWhichShouldGetRejected(): void
    {
        $uploadedFile = Mockery::mock(UploadedFileInterface::class);
        $uploadedFile
            ->shouldReceive('getClientFilename')
            ->once()
            ->andReturn('hello.txt')
            ->getMock()
            ->shouldReceive('getSize')
            ->once()
            ->andReturn(10)
            ->getMock()
            ->shouldReceive('getClientMediaType')
            ->once()
            ->andReturn('text/plain')
            ->getMock()
            ->shouldReceive('getError')
            ->once()
            ->andReturn(UPLOAD_ERR_PARTIAL)
            ->getMock()
            ->shouldReceive('getStream')
            ->twice()
            ->andReturn(Streams::ofString("Text file content."));

        $uploadedFiles = [
            $uploadedFile
        ];

        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
            ->once()
            ->andReturn($uploadedFiles);

        $this->subject->process();

        $result = $this->subject->getResults()[0];
        $this->assertSame(ProcessingStatus::REJECTED, $result->getStatus()->getCode());
    }


    #[Test]

    public function testHasUploadsWithoutUploadedFiles(): void
    {
        // No File-Upload Element
        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
                                  ->once()
                                  ->andReturn([]);
        $this->assertFalse($this->subject->hasUploads());
    }

    #[Test]

    public function testHasUploadsWithSingleUploadedFile(): void
    {
        $uploadedFile = Mockery::mock(UploadedFileInterface::class);

        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
                                  ->once()
                                  ->andReturn([ $uploadedFile ]);

        $this->assertTrue($this->subject->hasUploads());
    }

    #[Test]

    public function testHasUploadsWithMultipleUploadedFile(): void
    {
        $files = [];
        for ($i = 0; $i < 10; $i++) {
            $files[] = Mockery::mock(UploadedFileInterface::class);
        }

        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
            ->once()
            ->andReturn($files);

        $this->assertTrue($this->subject->hasUploads());
    }


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->prePorcessorManagerMock = \Mockery::mock(PreProcessorManager::class);
        $this->filesystemsMock = \Mockery::mock(Filesystems::class);
        $this->globalHttpStateMock = \Mockery::mock(GlobalHttpState::class);

        $this->subject = new FileUploadImpl($this->prePorcessorManagerMock, $this->filesystemsMock, $this->globalHttpStateMock);
    }
}
