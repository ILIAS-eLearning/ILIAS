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

class WhitelistMimeTypePreProcessorTest extends TestCase
{
    public static function whitelistedMimeTypeProvider(): array
    {
        return [
            'exact mime type' => [['text/html', 'audio/ogg'], 'testfile.html', 'text/html'],
            'any kind of text' => [['text/*', '*/ogg'], 'testfile.html', 'text/html'],
            'any kind of ogg' => [['text/html', '*/ogg'], 'testfile.ogg', 'audio/ogg'],
        ];
    }

    #[Test]
    #[DataProvider('whitelistedMimeTypeProvider')]
    public function testProcessWithWhitelistedMimeTypeWhichShouldSucceed(
        array $whitelist,
        string $filename,
        string $mime_type
    ): void {
        $subject = new WhitelistMimeTypePreProcessor($whitelist);
        $metadata = new Metadata($filename, 4000, $mime_type);

        $result = $subject->process($this->createStub(FileStream::class), $metadata);

        $this->assertSame(ProcessingStatus::OK, $result->getCode());
        $this->assertSame('Entity comply with mime type whitelist.', $result->getMessage());
    }

    #[Test]
    public function testProcessWithNotWhitelistedMimeTypeWhichShouldGetRejected(): void
    {
        $subject = new WhitelistMimeTypePreProcessor(['text/html', '*/ogg']);
        $metadata = new Metadata('testfile.png', 4000, 'image/png');

        $result = $subject->process($this->createStub(FileStream::class), $metadata);

        $this->assertSame(ProcessingStatus::REJECTED, $result->getCode());
        $this->assertSame('The mime type image/png is not whitelisted.', $result->getMessage());
    }

    #[Test]
    public function testCreateSubjectWithAnyKindOfMimeTypeWhichShouldFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The mime type */* matches all mime types which renders the whole whitelist useless.');

        new WhitelistMimeTypePreProcessor(['audio/ogg', '*/*']);
    }
}
