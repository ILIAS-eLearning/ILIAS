<?php

namespace ILIAS\FileUpload\Processor;

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class PreProcessorManagerImplTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState    disabled
 * @backupGlobals          disabled
 * @backupStaticAttributes disabled
 */
class PreProcessorManagerImplTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var PreProcessorManager $subject
     */
    private $subject;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->subject = new PreProcessorManagerImpl();
    }

    /**
     * @Test
     * @small
     */
    public function testProcessValidFileWhichShouldSucceed()
    {
        $response = new ProcessingStatus(ProcessingStatus::OK, 'All green!');
        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $processor = Mockery::mock(PreProcessor::class);
        $processor->shouldReceive('process')
            ->withAnyArgs()
            ->times(3)
            ->andReturn($response);

        $stream = Mockery::mock(FileStream::class);
        $stream->shouldReceive('rewind')
            ->withNoArgs()
            ->times(3);

        $this->subject->with($processor);
        $this->subject->with($processor);
        $this->subject->with($processor);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('All green!', $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessWithoutProcessorsWhichShouldSucceed()
    {
        $expectedResponse = new ProcessingStatus(ProcessingStatus::OK, 'No processors were registered.');
        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $stream = Mockery::mock(FileStream::class);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame($expectedResponse->getCode(), $result->getCode());
        $this->assertSame($expectedResponse->getMessage(), $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessInvalidFileWhichShouldGetRejected()
    {
        $responseGood = new ProcessingStatus(ProcessingStatus::OK, 'All green!');
        $responseBad = new ProcessingStatus(ProcessingStatus::REJECTED, 'Fail all red!');

        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $processor = Mockery::mock(PreProcessor::class);
        $processor->shouldReceive('process')
            ->withAnyArgs()
            ->times(2)
            ->andReturnValues([$responseGood, $responseBad, $responseGood]);

        $stream = Mockery::mock(FileStream::class);
        $stream->shouldReceive('rewind')
            ->withNoArgs()
            ->times(2);

        $this->subject->with($processor);
        $this->subject->with($processor);
        $this->subject->with($processor);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame($responseBad->getCode(), $result->getCode());
        $this->assertSame($responseBad->getMessage(), $result->getMessage());
    }

    /**
     * @Test
     * @small
     */
    public function testProcessValidFileWithFailingProcessorWhichShouldGetRejected()
    {
        $responseGood = new ProcessingStatus(ProcessingStatus::OK, 'All green!');

        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $processor = Mockery::mock(PreProcessor::class);
        $processor->shouldReceive('process')
            ->withAnyArgs()
            ->times(2)
            ->andReturn($responseGood);

        $processor->shouldReceive('process')
            ->withAnyArgs()
            ->once()
            ->andThrow(\RuntimeException::class, 'Bad stuff happened!');

        $stream = Mockery::mock(FileStream::class);
        $stream->shouldReceive('rewind')
            ->withNoArgs()
            ->times(3);

        $this->subject->with($processor);
        $this->subject->with($processor);
        $this->subject->with($processor);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Processor failed with exception message "Bad stuff happened!"', $result->getMessage());
    }
}
