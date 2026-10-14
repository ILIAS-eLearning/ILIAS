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

namespace ILIAS\File\Activities;

use ILIAS\FileDelivery\Activities\FilePayload;
use ILIAS\FileDelivery\Activities\FilePayloadFactory;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class DownloadContainerAsZipTest extends TestCase
{
    private const array SOME_CONTAINER = ['parent_ref_id' => 1, 'recursive' => false, 'usr_id' => 42];

    private ListFiles&Stub $list_files;
    private DeliverFileObject&Stub $deliver_file;
    private DownloadContainerAsZip $activity;

    protected function setUp(): void
    {
        $this->list_files = $this->createStub(ListFiles::class);
        $this->deliver_file = $this->createStub(DeliverFileObject::class);

        $this->activity = new DownloadContainerAsZip(
            $this->list_files,
            $this->deliver_file,
            new FilePayloadFactory(),
        );
    }

    /**
     * One file that cannot be read must not cost the user the archive: it is
     * left out, named in the answer, and the reason tells nothing about the
     * storage.
     */
    public function testAFileThatCannotBeReadIsSkippedAndListed(): void
    {
        $this->list_files->method('perform')->willReturn([
            $this->entry(10, 'readable.txt'),
            $this->entry(11, 'broken.txt'),
        ]);
        $this->deliver_file->method('perform')->willReturnCallback(
            static fn(array $parameters): FilePayload => match ($parameters['ref_id']) {
                10 => new FilePayload('readable.txt', 'text/plain', 4, base64_encode('fine')),
                11 => throw new \RuntimeException('File "storage/fsv2/some/path/data" not found.'),
            }
        );

        $archive = $this->activity->perform(self::SOME_CONTAINER);

        $this->assertSame(['readable.txt' => 'fine'], $this->unpack($archive));
        $this->assertCount(1, $archive->skipped);
        $this->assertSame(11, $archive->skipped[0]->ref_id);
        $this->assertSame('broken.txt', $archive->skipped[0]->title);
        $this->assertStringNotContainsString('storage', $archive->skipped[0]->reason);
    }

    public function testACompleteArchiveSkipsNothing(): void
    {
        $this->list_files->method('perform')->willReturn([$this->entry(10, 'a.txt'), $this->entry(12, 'a.txt')]);
        $this->deliver_file->method('perform')->willReturn(
            new FilePayload('a.txt', 'text/plain', 1, base64_encode('x'))
        );

        $archive = $this->activity->perform(self::SOME_CONTAINER);

        $this->assertSame(['a.txt' => 'x', 'a (2).txt' => 'x'], $this->unpack($archive));
        $this->assertSame([], $archive->skipped);
        $this->assertSame('application/zip', $archive->mime_type);
    }

    /**
     * If nothing can be packed the answer is still a valid, empty archive.
     */
    public function testWhenNothingCanBeReadTheArchiveIsEmptyButValid(): void
    {
        $this->list_files->method('perform')->willReturn([$this->entry(11, 'broken.txt')]);
        $this->deliver_file->method('perform')->willThrowException(new \OutOfBoundsException('no content'));

        $archive = $this->activity->perform(self::SOME_CONTAINER);

        $this->assertSame([], $this->unpack($archive));
        $this->assertCount(1, $archive->skipped);
    }

    public function testTooMuchContentIsRefusedBeforeAnythingIsRead(): void
    {
        $this->list_files->method('perform')->willReturn([
            new FileEntry(10, 10, 'huge.bin', '', 'huge.bin', 'application/octet-stream', 60 * 1024 * 1024, 1, 'rid-10'),
            new FileEntry(11, 11, 'huger.bin', '', 'huger.bin', 'application/octet-stream', 60 * 1024 * 1024, 1, 'rid-11'),
        ]);
        $deliver_file = $this->createMock(DeliverFileObject::class);
        $deliver_file->expects($this->never())->method('perform');

        $activity = new DownloadContainerAsZip($this->list_files, $deliver_file, new FilePayloadFactory());

        $this->expectException(\DomainException::class);
        $this->expectExceptionCode(422);

        $activity->perform(self::SOME_CONTAINER);
    }

    private function entry(int $ref_id, string $title): FileEntry
    {
        return new FileEntry($ref_id, $ref_id, $title, '', $title, 'text/plain', 1, 1, 'rid-' . $ref_id);
    }

    /**
     * @return array<string, string>
     */
    private function unpack(ContainerArchive $archive): array
    {
        $path = (string) tempnam(sys_get_temp_dir(), 'zip_test_');

        try {
            file_put_contents($path, base64_decode($archive->content, true));
            $zip = new \ZipArchive();
            $this->assertTrue($zip->open($path));

            $files = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                $files[$name] = (string) $zip->getFromIndex($i);
            }
            $zip->close();

            return $files;
        } finally {
            @unlink($path);
        }
    }
}
