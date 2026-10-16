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

class UpdateFileMetadataTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_FILE = 23;

    private RepositoryProvider&MockObject $repository;
    private bool $acting = true;
    private UpdateFileMetadata $activity;

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
        $this->activity = new UpdateFileMetadata($this->repository, new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))));
    }

    public function testChangingNeedsWrite(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->expects($this->once())->method('mayWrite')
            ->with(self::SOME_USER, self::SOME_FILE, 'file')
            ->willReturn(true);
        $this->repository->expects($this->once())->method('updateFileMetadata')
            ->with(self::SOME_FILE, 'New title', 'New description')
            ->willReturn($this->entry());

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'title' => ' New title ',
            'description' => 'New description',
        ]);

        $this->assertTrue($result->isOK());
    }

    /**
     * The fields of the input description strip tags, so the raw parameters
     * must not get around that.
     */
    public function testTagsAreStrippedLikeTheFieldsDo(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayWrite')->willReturn(true);
        $this->repository->expects($this->once())->method('updateFileMetadata')
            ->with(self::SOME_FILE, 'New title', 'New description')
            ->willReturn($this->entry());

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'title' => '<b>New title</b>',
            'description' => 'New <img src=x onerror=alert(1)>description',
        ]);

        $this->assertTrue($result->isOK());
    }

    /**
     * A field that is not sent is not touched - null tells the repository so.
     */
    public function testFieldsThatAreNotSentStayAsTheyAre(): void
    {
        $this->givenAWritableFile();
        $this->repository->expects($this->once())->method('updateFileMetadata')
            ->with(self::SOME_FILE, null, 'Only this')
            ->willReturn($this->entry());

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'description' => 'Only this',
        ]);

        $this->assertTrue($result->isOK());
    }

    public function testAnEmptyTitleIsRefused(): void
    {
        $this->givenAWritableFile();
        $this->repository->expects($this->never())->method('updateFileMetadata');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'title' => '   ',
        ]);

        $this->assertTrue($result->isError());
    }

    public function testWithoutWriteNothingChanges(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayWrite')->willReturn(false);
        $this->repository->expects($this->never())->method('updateFileMetadata');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'title' => 'New title',
        ]);

        $this->assertTrue($result->isError());
    }

    public function testOnlyFilesAreChanged(): void
    {
        $this->repository->method('lookupType')->willReturn('crs');
        $this->repository->expects($this->never())->method('mayWrite');
        $this->repository->expects($this->never())->method('updateFileMetadata');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'ref_id' => self::SOME_FILE,
            'title' => 'New title',
        ]);

        $this->assertTrue($result->isError());
    }

    public function testAnonymousRequestsChangeNothing(): void
    {
        $this->repository->expects($this->never())->method('mayWrite');
        $this->repository->expects($this->never())->method('updateFileMetadata');

        $result = $this->activity->maybePerformAs($this->inputFactory(), 0, [
            'ref_id' => self::SOME_FILE,
            'title' => 'New title',
        ]);

        $this->assertTrue($result->isError());
    }

    private function givenAWritableFile(): void
    {
        $this->repository->method('lookupType')->willReturn('file');
        $this->repository->method('mayWrite')->willReturn(true);
    }

    private function entry(): FileEntry
    {
        return new FileEntry(self::SOME_FILE, 99, 'New title', '', 'notes.txt', 'text/plain', 12, 1, 'a-rid');
    }

    private function inputFactory(): InputFactory
    {
        return $this->createStub(InputFactory::class);
    }
}
