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

namespace ILIAS\FileUpload\Activities;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessorManager;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\GlobalUploadLimit;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Input\Field\Textarea;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class FileParameterTest extends TestCase
{
    private const string SOME_HANDLE = '0123456789abcdef/notes.txt';

    private TempFileStore&MockObject $files;
    private PreProcessorManager&Stub $processors;
    private FileParameter $parameter;

    protected function setUp(): void
    {
        $this->files = $this->createMock(TempFileStore::class);
        $this->processors = $this->createStub(PreProcessorManager::class);
        $this->processors->method('process')->willReturnCallback(
            static fn(): ProcessingStatus => new ProcessingStatus(ProcessingStatus::OK, 'fine')
        );
        $this->parameter = new FileParameter($this->files, $this->processors, $this->strictly());
    }

    public function testTheFileIsWhatTheStoreMadeOfIt(): void
    {
        $this->givenTheStoreAccepts('notes.txt', 'text/plain', 12);

        $file = $this->parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ]);

        $this->assertSame('notes.txt', $file->filename);
        $this->assertSame('text/plain', $file->mime_type);
        $this->assertSame(12, $file->size);
    }

    public function testTheContentIsDecodedBeforeItIsStored(): void
    {
        $this->files->expects($this->once())->method('store')
            ->with('notes.txt', 'some content')
            ->willReturn(self::SOME_HANDLE);

        $this->parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ]);
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function unusableRequests(): array
    {
        return [
            'no file name' => [['content' => 'eA==']],
            'empty file name' => [['filename' => '  ', 'content' => 'eA==']],
            'no content' => [['filename' => 'notes.txt']],
            'empty content' => [['filename' => 'notes.txt', 'content' => '']],
            'no string' => [['filename' => 'notes.txt', 'content' => ['eA==']]],
            'not base64' => [['filename' => 'notes.txt', 'content' => 'not base64 %%%']],
        ];
    }

    /**
     * @param array<string, mixed> $raw_parameters
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('unusableRequests')]
    public function testNothingIsStoredForAnUnusableRequest(array $raw_parameters): void
    {
        $this->files->expects($this->never())->method('store');
        $this->expectException(\InvalidArgumentException::class);

        $this->parameter->read($raw_parameters);
    }

    public function testAFileLargerThanAllowedNeverReachesTheStore(): void
    {
        $parameter = $this->parameter->withMaxFileSize(10);

        $this->files->expects($this->never())->method('store');
        $this->expectException(\InvalidArgumentException::class);

        $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode(str_repeat('x', 11)),
        ]);
    }

    public function testAMuchTooLargeFileIsRefusedBeforeItIsEvenDecoded(): void
    {
        $parameter = $this->parameter->withMaxFileSize(10);

        $this->files->expects($this->never())->method('store');
        $this->expectException(\InvalidArgumentException::class);

        $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode(str_repeat('x', 4096)),
        ]);
    }

    public function testAFileOfAnUnacceptedTypeIsReleasedAgain(): void
    {
        $parameter = $this->parameter->withAcceptedMimeTypes(['application/pdf']);

        $this->givenTheStoreAccepts('notes.txt', 'text/plain', 12);
        $this->files->method('has')->willReturn(true);
        $this->files->expects($this->once())->method('delete')->with(self::SOME_HANDLE);

        $this->expectException(\InvalidArgumentException::class);

        $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ]);
    }

    public function testAnAcceptedTypeIsKept(): void
    {
        $parameter = $this->parameter->withAcceptedMimeTypes(['TEXT/PLAIN']);

        $this->givenTheStoreAccepts('notes.txt', 'text/plain', 12);
        $this->files->expects($this->never())->method('delete');

        $file = $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ]);

        $this->assertSame('text/plain', $file->mime_type);
    }

    public function testAFileThePreprocessorsRejectIsReleasedAgain(): void
    {
        $processors = $this->createMock(PreProcessorManager::class);
        $processors->expects($this->once())->method('process')
            ->willReturn(new ProcessingStatus(
                ProcessingStatus::REJECTED,
                'Processor failed with exception message "/var/iliasdata/ilias/default/temp/x not found"'
            ));
        $parameter = new FileParameter($this->files, $processors, $this->strictly());

        $this->givenTheStoreAccepts('notes.txt', 'text/plain', 12);
        $this->files->method('has')->willReturn(true);
        $this->files->expects($this->once())->method('delete')->with(self::SOME_HANDLE);

        try {
            $parameter->read([
                'filename' => 'notes.txt',
                'content' => base64_encode('some content'),
            ]);
            $this->fail('A rejected file must not be read.');
        } catch (\InvalidArgumentException $e) {
            // the reason stays inside, as in the GUI
            $this->assertSame('The file has been rejected.', $e->getMessage());
            $this->assertSame(400, $e->getCode());
        }
    }

    public function testTheStreamThePreprocessorsReadIsClosedAgain(): void
    {
        $stream = $this->createMock(FileStream::class);
        $stream->expects($this->once())->method('close');

        $this->givenTheStoreAccepts('notes.txt', 'text/plain', 12);
        $this->files->method('readStream')->willReturn($stream);

        $parameter = new FileParameter($this->files, $this->processors, $this->strictly());
        $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ]);
    }

    public function testTheUploadLimitOfTheInstallationApplies(): void
    {
        $limit = $this->createStub(GlobalUploadLimit::class);
        $limit->method('getGlobalUploadLimitInBytes')->willReturn(10);
        $parameter = new FileParameter($this->files, $this->processors, $this->strictly(), $limit);

        $this->files->expects($this->never())->method('store');
        $this->expectException(\InvalidArgumentException::class);

        $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode(str_repeat('x', 11)),
        ]);
    }

    public function testTheSmallerOfBothLimitsApplies(): void
    {
        $limit = $this->createStub(GlobalUploadLimit::class);
        $limit->method('getGlobalUploadLimitInBytes')->willReturn(1024);
        $parameter = (new FileParameter($this->files, $this->processors, $this->strictly(), $limit))->withMaxFileSize(10);

        $this->files->expects($this->never())->method('store');
        $this->expectException(\InvalidArgumentException::class);

        $parameter->read([
            'filename' => 'notes.txt',
            'content' => base64_encode(str_repeat('x', 11)),
        ]);
    }

    public function testTheLimitsAreThoseOfTheLastCall(): void
    {
        $parameter = $this->parameter->withMaxFileSize(1024)->withAcceptedMimeTypes(['application/pdf']);

        $this->files->expects($this->never())->method('store');

        $this->assertSame(1024, $parameter->getMaxFileSize());
        $this->assertSame(['application/pdf'], $parameter->getAcceptedMimeTypes());

        // the parameter it was made from is untouched
        $this->assertSame(0, $this->parameter->getMaxFileSize());
        $this->assertSame([], $this->parameter->getAcceptedMimeTypes());
    }

    public function testTheDescribedFieldsCarryTheNamesOfTheParameters(): void
    {
        $this->files->expects($this->never())->method('store');

        $fields = $this->parameter
            ->withNames('attachment', 'attachment_name')
            ->describe($this->fieldFactory());

        $this->assertSame(['attachment_name', 'attachment'], array_keys($fields));
    }

    private function givenTheStoreAccepts(string $filename, string $mime_type, int $size): void
    {
        $this->files->expects($this->once())->method('store')->willReturn(self::SOME_HANDLE);
        $this->files->method('getFilename')->willReturn($filename);
        $this->files->method('getMimeType')->willReturn($mime_type);
        $this->files->method('getSize')->willReturn($size);
    }

    private function fieldFactory(): FieldFactory
    {
        $text = $this->createStub(Text::class);
        $text->method('withRequired')->willReturn($text);
        $text->method('withDedicatedName')->willReturn($text);

        $textarea = $this->createStub(Textarea::class);
        $textarea->method('withRequired')->willReturn($textarea);
        $textarea->method('withDedicatedName')->willReturn($textarea);

        $factory = $this->createStub(FieldFactory::class);
        $factory->method('text')->willReturn($text);
        $factory->method('textarea')->willReturn($textarea);

        return $factory;
    }

    private function strictly(): Parameter
    {
        return new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class)));
    }
}
