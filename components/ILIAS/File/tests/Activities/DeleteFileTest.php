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

use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteFileTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;

    private RepositoryProvider&MockObject $repository;
    private DeleteFile $activity;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->activity = new DeleteFile($this->repository);
    }

    public function testDeletingMeansMovingToTheTrash(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayDelete')->willReturn(true);
        $this->repository->method('getFileEntry')->willReturn(
            new FileEntry(self::SOME_FILE, 99, 'notes.txt', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid')
        );

        $this->repository->expects($this->once())->method('moveToTrash')->with(self::SOME_FILE);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isOK());

        $deleted = $result->value();

        $this->assertInstanceOf(DeletedObject::class, $deleted);
        $this->assertSame('notes.txt', $deleted->title);
        $this->assertTrue($deleted->in_trash);
    }

    public function testWithoutTheDeletePermissionNothingIsDeleted(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayDelete')->willReturn(false);
        $this->repository->expects($this->never())->method('moveToTrash');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testOnlyFileObjectsCanBeDeletedHere(): void
    {
        $this->repository->expects($this->once())->method('lookupType')->willReturn('cat');
        $this->repository->expects($this->never())->method('moveToTrash');

        $this->assertTrue(
            $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE])->isError()
        );
    }

    public function testAnonymousRequestsDeleteNothing(): void
    {
        $this->repository->expects($this->never())->method('moveToTrash');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, ['ref_id' => self::SOME_FILE])->isError());
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
