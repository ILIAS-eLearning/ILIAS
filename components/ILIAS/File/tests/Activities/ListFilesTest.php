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
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListFilesTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_CONTAINER = 7;

    private RepositoryProvider&MockObject $repository;
    private ListContainerContent&MockObject $list_content;
    private ListFiles $activity;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->list_content = $this->createMock(ListContainerContent::class);

        $this->activity = new ListFiles(
            $this->repository,
            $this->list_content,
        );
    }

    public function testListingIsAQuery(): void
    {
        $this->expectNoFileToBeLookedUp();

        $this->assertSame(ActivityType::Query, $this->activity->getType());
    }

    /**
     * The general listing has already decided what the user may see, this one
     * only adds what a file is.
     */
    public function testTheGeneralListingIsAskedForTheFilesOnly(): void
    {
        $entry = $this->entry(11, 'readable.txt');

        $this->list_content->method('isAllowedToPerform')->willReturn(true);
        $this->list_content->expects($this->once())->method('perform')
            ->with($this->callback(static fn(array $p): bool => $p['types'] === ['file']))
            ->willReturn([new ContainerEntry(11, 111, 'file', 'readable.txt', '')]);

        $this->repository->expects($this->once())->method('getFileEntry')->with(11)->willReturn($entry);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
        ]);

        $this->assertTrue($result->isOK());
        $this->assertSame([$entry], $result->value());
    }

    public function testAnObjectThatIsNoLongerAFileIsSkipped(): void
    {
        $this->list_content->method('isAllowedToPerform')->willReturn(true);
        $this->list_content->expects($this->once())->method('perform')
            ->willReturn([new ContainerEntry(11, 111, 'file', 'gone.txt', '')]);

        $this->repository->expects($this->once())->method('getFileEntry')->willReturn(null);

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
        ]);

        $this->assertTrue($result->isOK());
        $this->assertSame([], $result->value());
    }

    public function testTheRecursiveFlagIsPassedOn(): void
    {
        $this->list_content->method('isAllowedToPerform')->willReturn(true);
        $this->repository->expects($this->never())->method('getFileEntry');
        $this->list_content->expects($this->once())->method('perform')
            ->with($this->callback(static fn(array $p): bool => $p['recursive'] === true))
            ->willReturn([]);

        // over HTTP a flag arrives as a string
        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
            'recursive' => '1',
        ]);

        $this->assertTrue($result->isOK());
        $this->assertSame([], $result->value());
    }

    public function testWhatTheGeneralListingRefusesIsRefusedHereToo(): void
    {
        $this->list_content->method('isAllowedToPerform')->willReturn(false);
        $this->expectNoFileToBeLookedUp();
        $this->list_content->expects($this->never())->method('perform');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
        ]);

        $this->assertTrue($result->isError());
        $this->assertStringContainsString('not allowed', (string) $result->error());
    }

    public function testAContainerIsRequired(): void
    {
        $this->expectNoFileToBeLookedUp();

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, []);

        $this->assertTrue($result->isError());
    }

    private function expectNoFileToBeLookedUp(): void
    {
        $this->repository->expects($this->never())->method('getFileEntry');
        $this->list_content->expects($this->never())->method('perform');
    }

    private function entry(int $ref_id, string $title): FileEntry
    {
        return new FileEntry($ref_id, $ref_id + 100, $title, '', $title, 'text/plain', 12, 1, 'rid-' . $ref_id);
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
