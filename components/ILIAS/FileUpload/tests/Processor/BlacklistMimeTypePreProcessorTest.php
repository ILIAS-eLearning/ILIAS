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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BlacklistMimeTypePreProcessorTest extends TestCase
{
    public static function blacklistedMimeTypeProvider(): array
    {
        return [
            'exact mime type' => [['text/html', 'audio/ogg'], 'testfile.html', 'text/html'],
            'any kind of text' => [['text/*', '*/ogg'], 'testfile.html', 'text/html'],
            'any kind of ogg' => [['text/html', '*/ogg'], 'testfile.ogg', 'audio/ogg'],
        ];
    }

    #[Test]
    #[DataProvider('blacklistedMimeTypeProvider')]
    public function testProcessWithBlacklistedMimeTypeWhichShouldGetRejected(
        array $blacklist,
        string $filename,
        string $mime_type
    ): void {
        $subject = new BlacklistMimeTypePreProcessor($blacklist);
        $metadata = new Metadata($filename, 4000, $mime_type);

        $result = $subject->process($this->createStub(FileStream::class), $metadata);

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type ' . $mime_type . ' is blacklisted.', $result->getMessage());
    }

    #[Test]
    public function testProcessWithNotBlacklistedMimeTypeWhichShouldSucceed(): void
    {
        $subject = new BlacklistMimeTypePreProcessor(['text/html', '*/ogg']);
        $metadata = new Metadata('testfile.png', 4000, 'image/png');

        $result = $subject->process($this->createStub(FileStream::class), $metadata);

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Entity comply with mime type blacklist.', $result->getMessage());
    }

    #[Test]
    public function testCreateSubjectWithAnyKindOfMimeTypeWhichShouldFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mime type */* matches all mime types which would black all files.');

        new BlacklistMimeTypePreProcessor(['audio/ogg', '*/*']);
    }
}
