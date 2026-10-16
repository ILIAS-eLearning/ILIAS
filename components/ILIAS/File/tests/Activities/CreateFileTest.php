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

use ILIAS\FileUpload\Activities\Parameter;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Component\Activities\ActivityType;
use ILIAS\FileUpload\Activities\FileContent;
use ILIAS\FileUpload\Activities\FileParameter;
use ILIAS\FileUpload\Activities\TempFileStore;
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateFileTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_CONTAINER = 7;
    private const string SOME_HANDLE = '0123456789abcdef/notes.txt';

    private FileParameter&MockObject $file;
    private TempFileStore $files;
    private RepositoryProvider&MockObject $repository;
    private bool $acting = true;
    private CreateFile $activity;

    protected function setUp(): void
    {
        // the activities refuse every user as long as they cannot tell the
        // anonymous one, see AccessCheck
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $this->file = $this->createMock(FileParameter::class);
        // only the tests watching the release of the content need a mock
        $this->files = $this->createStub(TempFileStore::class);
        $this->repository = $this->createMock(RepositoryProvider::class);
        // over a webservice the user asked about is the user of the session
        $this->repository->method('isActingUser')->willReturnCallback(fn(): bool => $this->acting);

        $this->activity = new CreateFile(
            $this->file,
            $this->repository,
            new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))),
        );
    }

    public function testCreatingAFileIsACommand(): void
    {
        $this->expectNothingToHappen();

        $this->assertSame(ActivityType::Command, $this->activity->getType());
    }

    public function testTheWholeObjectIsCreatedInOneGo(): void
    {
        $this->givenTheUserMayCreateFiles();

        // the content is not left behind
        $this->givenTheFileIsRead($this->watchingTheRelease());

        $this->repository
            ->expects($this->once())
            ->method('createFile')
            ->with(
                self::SOME_CONTAINER,
                null,
                null,
                $this->isInstanceOf(FileContent::class)
            )
            ->willReturn(new FileEntry(23, 42, 'notes.txt', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid'));

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isOK());

        $created = $result->value();

        $this->assertInstanceOf(FileEntry::class, $created);
        $this->assertSame(23, $created->ref_id);
        $this->assertSame(42, $created->obj_id);
        $this->assertSame('a-rid', $created->rid);
        $this->assertSame(1, $created->version);
    }

    public function testTagsAreStrippedLikeTheFieldsDo(): void
    {
        $this->givenTheUserMayCreateFiles();
        $this->givenTheFileIsRead();

        $this->repository->expects($this->once())->method('createFile')
            ->with(self::SOME_CONTAINER, 'A title', 'A description', $this->isInstanceOf(FileContent::class))
            ->willReturn(new FileEntry(23, 42, 'A title', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid'));

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            ...$this->request(),
            'title' => '<i>A title</i>',
            'description' => 'A <img src=x onerror=alert(1)>description',
        ]);

        $this->assertTrue($result->isOK());
    }

    public function testTheContentIsReleasedEvenWhenCreatingFails(): void
    {
        $this->givenTheUserMayCreateFiles();
        $this->givenTheFileIsRead($this->watchingTheRelease());

        $this->repository
            ->expects($this->once())
            ->method('createFile')
            ->willThrowException(new \RuntimeException('storage is on fire'));

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isError());
    }

    /**
     * Nothing of the request reaches the installation before the user is known
     * to be allowed to put something there.
     */
    public function testAFileCannotBeCreatedOutsideOfAContainer(): void
    {
        $this->repository->method('isContainer')->willReturn(false);
        $this->expectNothingToHappen();

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testNothingIsCreatedForSomebodyElseThanTheUserOfTheSession(): void
    {
        $this->acting = false;
        $this->repository->method('isContainer')->willReturn(true);
        $this->repository->method('mayCreate')->willReturn(true);
        $this->expectNothingToHappen();

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isError());
        $this->assertSame(403, $result->error()->getCode());
    }

    public function testAnonymousRequestsCreateNothing(): void
    {
        $this->expectNothingToHappen();

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, $this->request())->isError());
    }

    public function testATargetIsRequired(): void
    {
        $this->expectNothingToHappen();

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ]);

        $this->assertTrue($result->isError());
    }

    public function testAnUnusableFileStopsTheActivity(): void
    {
        $this->givenTheUserMayCreateFiles();

        $this->file->expects($this->once())->method('read')
            ->willThrowException(new \InvalidArgumentException('The content is not valid base64.', 400));

        $this->repository->expects($this->never())->method('createFile');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            ...$this->request(),
            'content' => 'this is not base64 %%%',
        ]);

        $this->assertTrue($result->isError());
    }

    /**
     * @return array<string, string|int>
     */
    private function request(): array
    {
        return [
            'parent_ref_id' => self::SOME_CONTAINER,
            'filename' => 'notes.txt',
            'content' => base64_encode('some content'),
        ];
    }

    private function expectNothingToHappen(): void
    {
        $this->file->expects($this->never())->method('read');
        $this->repository->expects($this->never())->method('createFile');
    }

    private function givenTheUserMayCreateFiles(): void
    {
        $this->repository->method('isContainer')->willReturn(true);
        $this->repository->method('mayCreate')->willReturn(true);
    }

    private function givenTheFileIsRead(?TempFileStore $files = null): void
    {
        $this->file
            ->expects($this->once())
            ->method('read')
            ->willReturn(
                new FileContent($files ?? $this->files, self::SOME_HANDLE, 'notes.txt', 'text/plain', 12)
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
