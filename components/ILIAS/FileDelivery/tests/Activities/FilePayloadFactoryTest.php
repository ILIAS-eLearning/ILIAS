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

namespace ILIAS\FileDelivery\Activities;

use ILIAS\Filesystem\Stream\FileStream;
use PHPUnit\Framework\TestCase;

class FilePayloadFactoryTest extends TestCase
{
    public function testAStreamIsReadFromItsStartAndEncoded(): void
    {
        $stream = $this->createMock(FileStream::class);
        $stream->method('isSeekable')->willReturn(true);
        $stream->expects($this->once())->method('rewind');
        $stream->method('getContents')->willReturn('some content');

        $payload = (new FilePayloadFactory())->fromStream('notes.txt', 'text/plain', $stream);

        $this->assertSame('notes.txt', $payload->filename);
        $this->assertSame('text/plain', $payload->mime_type);
        $this->assertSame(12, $payload->size);
        $this->assertSame(base64_encode('some content'), $payload->content);
    }

    /**
     * An archive delivers hundreds of files in one request - none of their
     * streams may stay open until its end.
     */
    public function testTheStreamIsClosedOnceItIsRead(): void
    {
        $stream = $this->createMock(FileStream::class);
        $stream->method('getContents')->willReturn('x');
        $stream->expects($this->once())->method('close');

        (new FilePayloadFactory())->fromStream('notes.txt', 'text/plain', $stream);
    }

    public function testTheStreamIsClosedEvenIfItCannotBeRead(): void
    {
        $stream = $this->createMock(FileStream::class);
        $stream->method('getContents')->willThrowException(new \RuntimeException('gone'));
        $stream->expects($this->once())->method('close');

        $this->expectException(\RuntimeException::class);

        (new FilePayloadFactory())->fromStream('notes.txt', 'text/plain', $stream);
    }
}
