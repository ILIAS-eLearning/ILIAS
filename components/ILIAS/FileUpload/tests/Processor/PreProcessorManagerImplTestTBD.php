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

namespace ILIAS\FileUpload\Processor;

use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\BackupStaticProperties;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Small;

require_once('./vendor/composer/vendor/autoload.php');

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Class PreProcessorManagerImplTest
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
#[BackupGlobals(false)]
#[BackupStaticProperties(false)]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
class PreProcessorManagerImplTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var PreProcessorManager $subject
     */
    private PreProcessorManagerImpl $subject;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PreProcessorManagerImpl();
    }

    #[Test]

    public function testProcessValidFileWhichShouldSucceed(): void
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

    #[Test]

    public function testProcessWithoutProcessorsWhichShouldSucceed(): void
    {
        $expectedResponse = new ProcessingStatus(ProcessingStatus::OK, 'No processors were registered.');
        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $stream = Mockery::mock(FileStream::class);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame($expectedResponse->getCode(), $result->getCode());
        $this->assertSame($expectedResponse->getMessage(), $result->getMessage());
    }

    #[Test]

    public function testProcessInvalidFileWhichShouldGetRejected(): void
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

    #[Test]

    public function testProcessValidFileWithFailingProcessorWhichShouldGetRejected(): void
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
