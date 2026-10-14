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

declare(strict_types=1);

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PreProcessorManagerImplTest extends TestCase
{
    private PreProcessorManagerImpl $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PreProcessorManagerImpl(new PreProcessorCollectionImpl([]));
    }

    #[Test]
    public function testProcessValidFileWhichShouldSucceed(): void
    {
        $response = new ProcessingStatus(ProcessingStatus::OK, 'All green!');
        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $processor = $this->createMock(PreProcessor::class);
        $processor->expects($this->exactly(3))
                  ->method('process')
                  ->willReturn($response);

        $stream = $this->createMock(FileStream::class);
        $stream->expects($this->exactly(3))
               ->method('rewind')
               ->with();

        $this->subject->with($processor);
        $this->subject->with($processor);
        $this->subject->with($processor);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('All green!', $result->getMessage());
    }

    #[Test]
    public function testProcessWithProcessorsFromCollectionWhichShouldSucceed(): void
    {
        $response = new ProcessingStatus(ProcessingStatus::OK, 'All green!');
        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $processor = $this->createMock(PreProcessor::class);
        $processor->expects($this->exactly(2))
                  ->method('process')
                  ->willReturn($response);

        $stream = $this->createMock(FileStream::class);
        $stream->expects($this->exactly(2))
               ->method('rewind');

        $subject = new PreProcessorManagerImpl(new PreProcessorCollectionImpl([$processor]));
        $subject->with($processor);

        $result = $subject->process($stream, $metadata);

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('All green!', $result->getMessage());
    }

    #[Test]
    public function testProcessWithoutProcessorsWhichShouldSucceed(): void
    {
        $expected_response = new ProcessingStatus(ProcessingStatus::OK, 'No processors were registered.');
        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $stream = $this->createMock(FileStream::class);
        $stream->expects($this->never())
               ->method('rewind');

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame($expected_response->getCode(), $result->getCode());
        $this->assertSame($expected_response->getMessage(), $result->getMessage());
    }

    #[Test]
    public function testProcessInvalidFileWhichShouldGetRejected(): void
    {
        $response_good = new ProcessingStatus(ProcessingStatus::OK, 'All green!');
        $response_bad = new ProcessingStatus(ProcessingStatus::REJECTED, 'Fail all red!');

        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $processor = $this->createMock(PreProcessor::class);
        $processor->expects($this->exactly(2))
                  ->method('process')
                  ->willReturnOnConsecutiveCalls($response_good, $response_bad, $response_good);

        $stream = $this->createMock(FileStream::class);
        $stream->expects($this->exactly(2))
               ->method('rewind');

        $this->subject->with($processor);
        $this->subject->with($processor);
        $this->subject->with($processor);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame($response_bad->getCode(), $result->getCode());
        $this->assertSame($response_bad->getMessage(), $result->getMessage());
    }

    #[Test]
    public function testProcessValidFileWithFailingProcessorWhichShouldGetRejected(): void
    {
        $response_good = new ProcessingStatus(ProcessingStatus::OK, 'All green!');

        $metadata = new Metadata('test.txt', 4500, 'text/plain');

        $invocations = 0;
        $processor = $this->createMock(PreProcessor::class);
        $processor->expects($this->exactly(3))
                  ->method('process')
                  ->willReturnCallback(
                      function () use (&$invocations, $response_good): ProcessingStatus {
                          if (++$invocations === 3) {
                              throw new \RuntimeException('Bad stuff happened!');
                          }
                          return $response_good;
                      }
                  );

        $stream = $this->createMock(FileStream::class);
        $stream->expects($this->exactly(3))
               ->method('rewind');

        $this->subject->with($processor);
        $this->subject->with($processor);
        $this->subject->with($processor);

        $result = $this->subject->process($stream, $metadata);

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('Processor failed with exception message "Bad stuff happened!"', $result->getMessage());
    }
}
