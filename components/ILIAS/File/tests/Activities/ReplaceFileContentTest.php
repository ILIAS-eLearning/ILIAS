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

use ILIAS\Component\Activities\ActivityType;
use ILIAS\FileUpload\Activities\FileContent;
use ILIAS\FileUpload\Activities\FileParameter;
use ILIAS\FileUpload\Activities\TempFileStore;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReplaceFileContentTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;
    private const string SOME_HANDLE = '0123456789abcdef/notes.txt';

    private FileParameter&MockObject $file;
    private TempFileStore $files;
    private RepositoryProvider&MockObject $repository;
    private ReplaceFileContent $activity;

    protected function setUp(): void
    {
        $this->file = $this->createMock(FileParameter::class);
        // only the tests watching the release of the content need a mock
        $this->files = $this->createStub(TempFileStore::class);
        $this->repository = $this->createMock(RepositoryProvider::class);

        $this->activity = new ReplaceFileContent(
            $this->file,
            $this->repository,
        );
    }

    public function testReplacingIsACommand(): void
    {
        $this->expectNothingToHappen();

        $this->assertSame(ActivityType::Command, $this->activity->getType());
    }

    public function testTheHistoryIsKeptUnlessTheCallerSaysOtherwise(): void
    {
        $this->givenTheUserMayWrite();

        // the content is not left behind
        $this->givenTheFileIsRead($this->watchingTheRelease());

        $this->repository->expects($this->once())->method('replaceFileContent')
            ->with(
                self::SOME_FILE,
                $this->isInstanceOf(FileContent::class),
                true
            )
            ->willReturn($this->entry(2));

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isOK());
        $this->assertSame(2, $result->value()->version);
    }

    public function testTheHistoryCanBeDropped(): void
    {
        $this->givenTheUserMayWrite();
        $this->givenTheFileIsRead();

        $this->repository->expects($this->once())->method('replaceFileContent')
            ->with($this->anything(), $this->anything(), false)
            ->willReturn($this->entry(3));

        $result = $this->activity->maybePerformAs(
            $this->inputFactory(),
            self::SOME_USER,
            [...$this->request(), 'keep_previous_version' => '0']
        );

        $this->assertTrue($result->isOK());
    }

    public function testWithoutTheWritePermissionNothingIsReadAtAll(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayWrite')->willReturn(false);
        $this->expectNothingToHappen();

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testSomethingThatIsNoFileObjectIsRejected(): void
    {
        $this->repository->expects($this->once())->method('lookupType')->willReturn('cat');
        $this->expectNothingToHappen();

        $this->assertTrue(
            $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError()
        );
    }

    public function testAnonymousRequestsChangeNothing(): void
    {
        $this->expectNothingToHappen();

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, $this->request())->isError());
    }

    public function testAFileObjectIsRequired(): void
    {
        $this->expectNothingToHappen();

        $result = $this->activity->maybePerformAs(
            $this->inputFactory(),
            self::SOME_USER,
            ['filename' => 'x.txt', 'content' => 'eA==']
        );

        $this->assertTrue($result->isError());
    }

    public function testTheContentIsReleasedEvenWhenStoringFails(): void
    {
        $this->givenTheUserMayWrite();
        $this->givenTheFileIsRead($this->watchingTheRelease());

        $this->repository->expects($this->once())->method('replaceFileContent')
            ->willThrowException(new \RuntimeException('storage is on fire'));

        $this->assertTrue(
            $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError()
        );
    }

    /**
     * @return array<string, string|int>
     */
    private function request(): array
    {
        return [
            'ref_id' => self::SOME_FILE,
            'filename' => 'notes.txt',
            'content' => base64_encode('new content'),
        ];
    }

    private function entry(int $version): FileEntry
    {
        return new FileEntry(
            self::SOME_FILE,
            99,
            'notes.txt',
            '',
            'notes.txt',
            'text/plain',
            11,
            $version,
            'a-rid'
        );
    }

    private function expectNothingToHappen(): void
    {
        $this->file->expects($this->never())->method('read');
        $this->repository->expects($this->never())->method('replaceFileContent');
    }

    private function givenTheUserMayWrite(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayWrite')->willReturn(true);
    }

    private function givenTheFileIsRead(?TempFileStore $files = null): void
    {
        $this->file
            ->expects($this->once())
            ->method('read')
            ->willReturn(
                new FileContent($files ?? $this->files, self::SOME_HANDLE, 'notes.txt', 'text/plain', 11)
            );
    }

    /**
     * A store that insists on getting its file back.
     */
    private function watchingTheRelease(): TempFileStore&MockObject
    {
        $files = $this->createMock(TempFileStore::class);
        $files->method('has')->willReturn(true);
        $files->expects($this->once())->method('delete')->with(self::SOME_HANDLE);

        return $files;
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
