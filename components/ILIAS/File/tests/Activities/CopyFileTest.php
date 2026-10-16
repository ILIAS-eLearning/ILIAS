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

class CopyFileTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;
    private const int SOME_TARGET = 7;
    private const int THE_COPY = 24;

    private RepositoryProvider&MockObject $repository;
    private bool $acting = true;
    private CopyFile $activity;

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
        $this->activity = new CopyFile($this->repository, new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))));
    }

    public function testCopyingNeedsCopyOnTheFileAndCreateAtTheTarget(): void
    {
        $this->givenAFileAndAContainer();
        $this->repository->expects($this->once())->method('mayCopy')
            ->with(self::SOME_USER, self::SOME_FILE, 'file')
            ->willReturn(true);
        $this->repository->expects($this->once())->method('mayCreate')
            ->with(self::SOME_USER, self::SOME_TARGET, 'file')
            ->willReturn(true);

        $this->repository->expects($this->once())->method('copyFile')
            ->with(self::SOME_FILE, self::SOME_TARGET)
            ->willReturn($this->copy());

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request());

        $this->assertTrue($result->isOK());
        $this->assertSame(self::THE_COPY, $result->value()->ref_id);
    }

    public function testWithoutCopyNothingIsCopied(): void
    {
        $this->givenAFileAndAContainer();
        $this->repository->method('mayCopy')->willReturn(false);
        $this->repository->method('mayCreate')->willReturn(true);
        $this->repository->expects($this->never())->method('copyFile');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testWithoutCreateAtTheTargetNothingIsCopied(): void
    {
        $this->givenAFileAndAContainer();
        $this->repository->method('mayCopy')->willReturn(true);
        $this->repository->method('mayCreate')->willReturn(false);
        $this->repository->expects($this->never())->method('copyFile');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testOnlyFilesAreCopied(): void
    {
        $this->repository->method('lookupType')->willReturn('crs');
        $this->repository->method('isContainer')->willReturn(true);
        $this->repository->expects($this->never())->method('copyFile');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testAFileCannotBeCopiedIntoSomethingThatHoldsNothing(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('isContainer')->willReturn(false);
        $this->repository->expects($this->never())->method('copyFile');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, $this->request())->isError());
    }

    public function testAnObjectCannotBeCopiedIntoItself(): void
    {
        $this->repository->expects($this->never())->method('copyFile');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'target_ref_id' => self::SOME_FILE,
        ]);

        $this->assertTrue($result->isError());
    }

    public function testAnonymousRequestsCopyNothing(): void
    {
        $this->repository->expects($this->never())->method('mayCopy');
        $this->repository->expects($this->never())->method('copyFile');

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

    private function copy(): FileEntry
    {
        return new FileEntry(self::THE_COPY, 100, 'notes.txt', '', 'notes.txt', 'text/plain', 12, 1, 'another-rid');
    }

    private function inputFactory(): InputFactory
    {
        return $this->createStub(InputFactory::class);
    }
}
