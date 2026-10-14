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

namespace ILIAS\FileUpload;

use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Exception\IllegalStateException;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\Processor\PreProcessorManager;
use ILIAS\HTTP\GlobalHttpState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 */
class FileUploadImplTest extends TestCase
{
    /**
     * @param UploadedFileInterface[] $uploaded_files
     */
    private function createHttpStateWithUploadedFiles(array $uploaded_files): GlobalHttpState
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
                ->method('getUploadedFiles')
                ->willReturn($uploaded_files);

        $http_state = $this->createStub(GlobalHttpState::class);
        $http_state->method('request')->willReturn($request);

        return $http_state;
    }

    private function createSubject(
        PreProcessorManager $pre_processor_manager,
        GlobalHttpState $http_state
    ): FileUploadImpl {
        return new FileUploadImpl(
            $pre_processor_manager,
            $this->createStub(Filesystems::class),
            $http_state
        );
    }

    private function createUploadedFile(int $error): UploadedFileInterface
    {
        $uploaded_file = $this->createMock(UploadedFileInterface::class);
        $uploaded_file->expects($this->once())
                      ->method('getClientFilename')
                      ->willReturn('hello.txt');
        $uploaded_file->expects($this->once())
                      ->method('getSize')
                      ->willReturn(10);
        $uploaded_file->expects($this->once())
                      ->method('getClientMediaType')
                      ->willReturn('text/plain');
        $uploaded_file->expects($this->once())
                      ->method('getError')
                      ->willReturn($error);
        $uploaded_file->expects($this->exactly(2))
                      ->method('getStream')
                      ->willReturn(Streams::ofString('Text file content.'));

        return $uploaded_file;
    }

    #[Test]
    public function testRegisterWhichShouldSucceed(): void
    {
        $processor = $this->createStub(PreProcessor::class);

        $pre_processor_manager = $this->createMock(PreProcessorManager::class);
        $pre_processor_manager->expects($this->once())
                              ->method('with')
                              ->with($processor);

        $subject = $this->createSubject($pre_processor_manager, $this->createStub(GlobalHttpState::class));
        $subject->register($processor);
    }

    #[Test]
    public function testRegisterWithProcessedFilesWhichShouldFail(): void
    {
        $processor = $this->createStub(PreProcessor::class);

        $pre_processor_manager = $this->createMock(PreProcessorManager::class);
        $pre_processor_manager->expects($this->never())
                              ->method('with');

        $subject = $this->createSubject($pre_processor_manager, $this->createHttpStateWithUploadedFiles([]));

        $this->expectException(IllegalStateException::class);
        $this->expectExceptionMessage('Can not register processor after the upload was processed.');

        $subject->process();
        $subject->register($processor);
    }

    #[Test]
    public function testProcessWhichShouldSucceed(): void
    {
        $processing_result = new ProcessingStatus(ProcessingStatus::OK, 'All green!');

        $pre_processor_manager = $this->createMock(PreProcessorManager::class);
        $pre_processor_manager->expects($this->once())
                              ->method('process')
                              ->willReturn($processing_result);

        $subject = $this->createSubject(
            $pre_processor_manager,
            $this->createHttpStateWithUploadedFiles([$this->createUploadedFile(UPLOAD_ERR_OK)])
        );

        $subject->process();

        $this->assertTrue($subject->hasBeenProcessed());
        $results = $subject->getResults();
        $this->assertCount(1, $results);
        $result = reset($results);
        $this->assertSame($processing_result, $result->getStatus());
        $this->assertSame('hello.txt', $result->getName());
        $this->assertSame(10, $result->getSize());
        $this->assertSame('text/plain', $result->getMimeType());
    }

    #[Test]
    public function testProcessWithFailedUploadWhichShouldGetRejected(): void
    {
        $pre_processor_manager = $this->createMock(PreProcessorManager::class);
        $pre_processor_manager->expects($this->never())
                              ->method('process');

        $subject = $this->createSubject(
            $pre_processor_manager,
            $this->createHttpStateWithUploadedFiles([$this->createUploadedFile(UPLOAD_ERR_PARTIAL)])
        );

        $subject->process();

        $result = $subject->getResults()[0];
        $this->assertSame(ProcessingStatus::REJECTED, $result->getStatus()->getCode());
    }

    #[Test]
    public function testHasUploadsWithoutUploadedFiles(): void
    {
        // No File-Upload Element
        $subject = $this->createSubject(
            $this->createStub(PreProcessorManager::class),
            $this->createHttpStateWithUploadedFiles([])
        );

        $this->assertFalse($subject->hasUploads());
    }

    #[Test]
    public function testHasUploadsWithSingleUploadedFile(): void
    {
        $subject = $this->createSubject(
            $this->createStub(PreProcessorManager::class),
            $this->createHttpStateWithUploadedFiles([$this->createStub(UploadedFileInterface::class)])
        );

        $this->assertTrue($subject->hasUploads());
    }

    #[Test]
    public function testHasUploadsWithMultipleUploadedFile(): void
    {
        $files = [];
        for ($i = 0; $i < 10; $i++) {
            $files[] = $this->createStub(UploadedFileInterface::class);
        }

        $subject = $this->createSubject(
            $this->createStub(PreProcessorManager::class),
            $this->createHttpStateWithUploadedFiles($files)
        );

        $this->assertTrue($subject->hasUploads());
    }
}
