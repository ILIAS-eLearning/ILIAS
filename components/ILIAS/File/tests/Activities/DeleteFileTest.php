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
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteFileTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;

    private RepositoryProvider&MockObject $repository;
    private bool $acting = true;
    private DeleteFile $activity;

    protected function setUp(): void
    {
        // the activities refuse every user as long as they cannot tell the
        // anonymous one, see AccessCheck
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $this->repository = $this->createMock(RepositoryProvider::class);
        // over a webservice the user asked about is the user of the session
        $this->repository->method('isActingUser')->willReturnCallback(fn(): bool => $this->acting);
        $this->activity = new DeleteFile($this->repository, new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))));
    }

    public function testWithATrashDeletingMeansMovingThere(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayDelete')->willReturn(true);
        $this->repository->method('getFileEntry')->willReturn(
            new FileEntry(self::SOME_FILE, 99, 'notes.txt', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid')
        );

        $this->repository->expects($this->once())->method('delete')->with(self::SOME_FILE)->willReturn(true);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isOK());

        $deleted = $result->value();

        $this->assertInstanceOf(DeletedObject::class, $deleted);
        $this->assertSame('notes.txt', $deleted->title);
        $this->assertTrue($deleted->in_trash);
    }

    /**
     * Without a trash the GUI deletes for good, and so does this - but it says
     * so, a client must not believe it can be undone.
     */
    public function testWithoutATrashTheAnswerSaysItIsGone(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayDelete')->willReturn(true);
        $this->repository->method('getFileEntry')->willReturn(
            new FileEntry(self::SOME_FILE, 99, 'notes.txt', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid')
        );
        $this->repository->expects($this->once())->method('delete')->with(self::SOME_FILE)->willReturn(false);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isOK());
        $this->assertFalse($result->value()->in_trash);
    }

    public function testWithoutTheDeletePermissionNothingIsDeleted(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayDelete')->willReturn(false);
        $this->repository->expects($this->never())->method('delete');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testOnlyFileObjectsCanBeDeletedHere(): void
    {
        $this->repository->expects($this->once())->method('lookupType')->willReturn('cat');
        $this->repository->expects($this->never())->method('delete');

        $this->assertTrue(
            $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE])->isError()
        );
    }

    /**
     * The deletion acts as the user of the session; asked for somebody else,
     * the activity would check one user and act as another.
     */
    public function testNothingIsDeletedForSomebodyElseThanTheUserOfTheSession(): void
    {
        $this->acting = false;
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayDelete')->willReturn(true);
        $this->repository->expects($this->never())->method('delete');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, ['ref_id' => self::SOME_FILE]);

        $this->assertTrue($result->isError());
        $this->assertSame(403, $result->error()->getCode());
    }

    public function testAnonymousRequestsDeleteNothing(): void
    {
        $this->repository->expects($this->never())->method('delete');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, ['ref_id' => self::SOME_FILE])->isError());
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
