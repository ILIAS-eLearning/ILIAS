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

class MoveFileTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;
    private const int SOME_TARGET = 7;

    private RepositoryProvider&MockObject $repository;
    private MoveFile $activity;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->activity = new MoveFile($this->repository);
    }

    public function testMovingNeedsBothPermissions(): void
    {
        $this->givenAFileAndAContainer();
        $this->repository->method('mayDelete')->willReturn(true);
        $this->repository->method('mayCreate')->willReturn(true);

        $this->repository->expects($this->once())->method('moveObject')
            ->with(self::SOME_FILE, self::SOME_TARGET);
        $this->repository->method('getFileEntry')->willReturn($this->entry());

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isOK());
        $this->assertSame(self::SOME_FILE, $result->value()->ref_id);
    }

    public function testWithoutDeleteWhereItIsNothingMoves(): void
    {
        $this->givenAFileAndAContainer();
        $this->repository->method('mayDelete')->willReturn(false);
        $this->repository->method('mayCreate')->willReturn(true);
        $this->repository->expects($this->never())->method('moveObject');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testWithoutCreateWhereItGoesNothingMoves(): void
    {
        $this->givenAFileAndAContainer();
        $this->repository->method('mayDelete')->willReturn(true);
        $this->repository->method('mayCreate')->willReturn(false);
        $this->repository->expects($this->never())->method('moveObject');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testAFileCannotBeMovedIntoSomethingThatHoldsNothing(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('isContainer')->willReturn(false);
        $this->repository->expects($this->never())->method('moveObject');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testAnObjectCannotBeMovedIntoItself(): void
    {
        $this->repository->expects($this->never())->method('moveObject');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'target_ref_id' => self::SOME_FILE,
        ]);

        $this->assertTrue($result->isError());
    }

    public function testAnonymousRequestsMoveNothing(): void
    {
        $this->repository->expects($this->never())->method('moveObject');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), 0, $this->request())->isError());
    }

    /**
     * @return array<string, int>
     */
    private function request(): array
    {
        return ['ref_id' => self::SOME_FILE, 'target_ref_id' => self::SOME_TARGET];
    }

    private function givenAFileAndAContainer(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('isContainer')->willReturn(true);
    }

    private function entry(): FileEntry
    {
        return new FileEntry(self::SOME_FILE, 99, 'notes.txt', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid');
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
