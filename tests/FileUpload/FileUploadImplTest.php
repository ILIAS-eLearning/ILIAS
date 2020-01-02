<?php

namespace ILIAS\FileUpload;

require_once('./libs/composer/vendor/autoload.php');

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
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class FileUploadImplTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var FileUpload $subject
     */
    private $subject;
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


    /**
     * @Test
     * @small
     */
    public function testRegisterWhichShouldSucceed()
    {
        $processorMock = \Mockery::mock(PreProcessor::class);
        $this->prePorcessorManagerMock->shouldReceive('with')
            ->once()
            ->with($processorMock);

        $this->subject->register($processorMock);
    }

    /**
     * @Test
     * @small
     */
    public function testRegisterWithProcessedFilesWhichShouldFail()
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

    /**
     * @Test
     * @small
     */
    public function testProcessWhichShouldSucceed()
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

    /**
     * @Test
     * @small
     */
    public function testProcessWithFailedUploadWhichShouldGetRejected()
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


    /**
     * @test
     * @small
     */
    public function testHasUploadsWithoutUploadedFiles()
    {
        // No File-Upload Element
        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
                                  ->once()
                                  ->andReturn([]);
        $this->assertFalse($this->subject->hasUploads());
    }

    /**
     * @test
     * @small
     */
    public function testHasUploadsWithSingleUploadedFile()
    {
        $uploadedFile = Mockery::mock(UploadedFileInterface::class);

        $this->globalHttpStateMock->shouldReceive('request->getUploadedFiles')
                                  ->once()
                                  ->andReturn([ $uploadedFile ]);

        $this->assertTrue($this->subject->hasUploads());
    }

    /**
     * @test
     * @small
     */
    public function testHasUploadsWithMultipleUploadedFile()
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
    protected function setUp()
    {
        parent::setUp();

        $this->prePorcessorManagerMock = \Mockery::mock(PreProcessorManager::class);
        $this->filesystemsMock = \Mockery::mock(Filesystems::class);
        $this->globalHttpStateMock = \Mockery::mock(GlobalHttpState::class);

        $this->subject = new FileUploadImpl($this->prePorcessorManagerMock, $this->filesystemsMock, $this->globalHttpStateMock);
    }
}
