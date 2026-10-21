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
use ILIAS\UI\Component\Input\Factory as InputFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ListContainerContentTest extends TestCase
{
    private const int SOME_USER = 42;
    private const int SOME_CONTAINER = 7;

    private RepositoryProvider&MockObject $repository;
    private ListContainerContent $activity;

    protected function setUp(): void
    {
        // the activities refuse every user as long as they cannot tell the
        // anonymous one, see AccessCheck
        if (!defined('ANONYMOUS_USER_ID')) {
            define('ANONYMOUS_USER_ID', 13);
        }

        $this->repository = $this->createMock(RepositoryProvider::class);
        $this->activity = new ListContainerContent($this->repository, new Parameter(new Refinery(new DataFactory(), $this->createStub(Language::class))));
    }

    public function testListingIsAQuery(): void
    {
        $this->repository->expects($this->never())->method('listContent');

        $this->assertSame(ActivityType::Query, $this->activity->getType());
    }

    public function testOnlyObjectsTheUserMayReadAreListed(): void
    {
        $readable = new ContainerEntry(11, 111, 'cat', 'A category', '');
        $hidden = new ContainerEntry(12, 112, 'crs', 'A course', '');

        $this->givenTheContainerMayBeRead();
        $this->repository->expects($this->once())->method('listContent')
            ->with(self::SOME_CONTAINER, false, [])
            ->willReturn([$readable, $hidden]);

        $this->repository->method('mayRead')->willReturnCallback(
            static fn(int $usr_id, int $ref_id): bool => $ref_id !== 12
        );

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
        ]);

        $this->assertTrue($result->isOK());
        $this->assertSame([$readable], $result->value());
    }

    public function testTypesAndRecursionArePassedOn(): void
    {
        $this->givenTheContainerMayBeRead();
        $this->repository->method('mayRead')->willReturn(true);
        $this->repository->expects($this->once())->method('listContent')
            ->with(self::SOME_CONTAINER, true, ['file', 'cat'])
            ->willReturn([]);

        // over HTTP both arrive as strings
        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
            'types' => 'file, cat',
            'recursive' => '1',
        ]);

        $this->assertTrue($result->isOK());
    }

    public function testAContainerNobodyMayReadIsNotListed(): void
    {
        $this->repository->method('isContainer')->willReturn(true);
        $this->repository->expects($this->once())->method('mayRead')->willReturn(false);
        $this->repository->expects($this->never())->method('listContent');

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
        ]);

        $this->assertTrue($result->isError());
    }

    public function testAnonymousRequestsGetNothing(): void
    {
        $this->repository->expects($this->never())->method('listContent');
        $this->repository->expects($this->never())->method('mayRead');

        $this->assertTrue(
            $this->activity->maybePerformAs($this->inputFactory(), 0, ['parent_ref_id' => self::SOME_CONTAINER])->isError()
        );
    }

    public function testAContainerIsRequired(): void
    {
        $this->repository->expects($this->never())->method('listContent');

        $this->assertTrue($this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [])->isError());
    }

    /**
     * Every entry costs an access check: a listing that large is refused
     * before the first one.
     */
    public function testATooLargeListingIsRefusedBeforeAnythingIsChecked(): void
    {
        $this->givenTheContainerMayBeRead();
        $this->repository->expects($this->once())->method('mayRead')
            ->with(self::SOME_USER, self::SOME_CONTAINER)
            ->willReturn(true);
        $this->repository->method('listContent')->willReturn(array_fill(
            0,
            ListContainerContent::MAX_ENTRIES + 1,
            new ContainerEntry(11, 111, 'file', 'A file', '')
        ));

        $result = $this->activity->maybePerformAs($this->inputFactory(), self::SOME_USER, [
            'parent_ref_id' => self::SOME_CONTAINER,
            'recursive' => true,
        ]);

        $this->assertTrue($result->isError());
        $this->assertSame(422, $result->error()->getCode());
    }

    private function givenTheContainerMayBeRead(): void
    {
        $this->repository->method('isContainer')->willReturn(true);
    }

    private function inputFactory(): InputFactory
    {
        // the activities of this component do their own parameter handling,
        // the factory is only passed along
        return $this->createStub(InputFactory::class);
    }
}
