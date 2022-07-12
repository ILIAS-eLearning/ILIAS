<?php declare(strict_types=1);

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

/**
 * Class ilBuddyListTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilBuddyListTest extends ilBuddySystemBaseTest
{
    private const BUDDY_LIST_OWNER_ID = -1;
    private const BUDDY_LIST_BUDDY_ID = -2;

    protected function setUp() : void
    {
        parent::setUp();

        $this->setGlobalVariable(
            'ilAppEventHandler',
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods(['raise'])->getMock()
        );
        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable(
            'lng',
            $this->getMockBuilder(ilLanguage::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['txt', 'loadLanguageModule'])
                ->getMock()
        );
    }

    public function testInstanceCanBeCreatedByGlobalUserObject() : void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(['getId'])->getMock();
        $user->expects($this->once())->method('getId')->willReturn(self::BUDDY_LIST_OWNER_ID);
        $this->setGlobalVariable('ilUser', $user);

        ilBuddyList::getInstanceByGlobalUser();
    }

    public function testInstanceCannotBeCreatedByAnonymousGlobalUserObject() : void
    {
        $this->expectException(ilBuddySystemException::class);

        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->onlyMethods(['getId'])->getMock();
        $user->expects($this->once())->method('getId')->willReturn(ANONYMOUS_USER_ID);
        $this->setGlobalVariable('ilUser', $user);

        ilBuddyList::getInstanceByGlobalUser();
    }

    public function testInstanceByBeCreatedBySingletonMethod() : void
    {
        $relations = [
            4711 => new ilBuddySystemRelation(
                new ilBuddySystemUnlinkedRelationState(),
                self::BUDDY_LIST_BUDDY_ID,
                self::BUDDY_LIST_OWNER_ID,
                false,
                time()
            ),
            4712 => new ilBuddySystemRelation(
                new ilBuddySystemUnlinkedRelationState(),
                self::BUDDY_LIST_BUDDY_ID,
                self::BUDDY_LIST_OWNER_ID,
                false,
                time()
            )
        ];

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->setRelations(new ilBuddySystemRelationCollection($relations));
        $otherBuddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $otherBuddylist->setRelations(new ilBuddySystemRelationCollection());

        $this->assertEquals($buddyList, $otherBuddylist);
    }

    public function testListIsInitiallyEmpty() : void
    {
        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn([]);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $this->assertEmpty($buddyList->getRelations());
    }

    public function testRepositoryIsEnquiredToFetchRelationsWhenRequestedExplicitly() : void
    {
        $relations = [
            4711 => new ilBuddySystemRelation(
                new ilBuddySystemUnlinkedRelationState(),
                self::BUDDY_LIST_BUDDY_ID,
                self::BUDDY_LIST_OWNER_ID,
                false,
                time()
            ),
            4712 => new ilBuddySystemRelation(
                new ilBuddySystemUnlinkedRelationState(),
                self::BUDDY_LIST_BUDDY_ID,
                self::BUDDY_LIST_OWNER_ID,
                false,
                time()
            )
        ];

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn($relations);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
    }

    public function testRepositoryIsEnquiredOnlyOnceToFetchRelationsWhenCalledImplicitly() : void
    {
        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_BUDDY_ID,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );
        $expectedRelation = $expectedRelation->withUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation = $expectedRelation->withBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->exactly(2))->method('queryF');
        $db->expects($this->exactly(2))->method('fetchAssoc')->willReturn([
            'login' => 'phpunit'
        ]);
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn($relations);
        $repo->expects($this->exactly(3))->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $relation = $buddyList->getRelationByUserId($expectedRelation->getBuddyUsrId());
        $buddyList->request($relation);
        $buddyList->unlink($relation);
        $buddyList->request($relation);
    }

    public function testRelationRequestCannotBeApprovedByTheRelationOwner() : void
    {
        $this->expectException(ilBuddySystemException::class);

        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())->method('queryF');
        $db->expects($this->once())->method('fetchAssoc')->willReturn([
            'login' => 'phpunit'
        ]);
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn($relations);
        $repo->expects($this->once())->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $relation = $buddyList->getRelationByUserId($expectedRelation->getBuddyUsrId());
        $buddyList->request($relation);
        $buddyList->link($relation);
    }

    public function testRelationRequestCanBeApprovedByTheRelationTarget() : void
    {
        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db->method('queryF');
        $db->method('fetchAssoc')->willReturn([
            'login' => 'phpunit'
        ]);
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->method('getAll')->willReturn($relations);
        $repo->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);

        $other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_BUDDY_ID);
        $other_buddylist->reset();
        $other_buddylist->setRepository($repo);
        $other_buddylist->link($expectedRelation);

        $this->assertEquals(new ilBuddySystemLinkedRelationState(), $expectedRelation->getState());
    }

    public function testRelationRequestCannotBeIgnoredByTheRelationOwner() : void
    {
        $this->expectException(ilBuddySystemException::class);

        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())->method('queryF');
        $db->expects($this->once())->method('fetchAssoc')->willReturn([
            'login' => 'phpunit'
        ]);
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn($relations);
        $repo->expects($this->once())->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $relation = $buddyList->getRelationByUserId($expectedRelation->getBuddyUsrId());
        $buddyList->request($relation);
        $buddyList->ignore($relation);
    }

    public function testRelationRequestCanBeIgnoredByTheRelationTarget() : void
    {
        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->createMock(ilDBInterface::class);
        $db->method('queryF');
        $db->method('fetchAssoc')->willReturn([
            'login' => 'phpunit'
        ]);
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->method('getAll')->willReturn($relations);
        $repo->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);

        $other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_BUDDY_ID);
        $other_buddylist->reset();
        $other_buddylist->setRepository($repo);
        $other_buddylist->ignore($expectedRelation);

        $this->assertEquals(new ilBuddySystemIgnoredRequestRelationState(), $expectedRelation->getState());
    }

    public function testRelationCannotBeRequestedForAnonymous() : void
    {
        $this->expectException(ilBuddySystemException::class);

        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            ANONYMOUS_USER_ID,
            false,
            time()
        );

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getAll')->willReturn([]);
        $repo->expects($this->never())->method('save');

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);
    }

    public function testRelationCannotBeRequestedForUnknownUserAccounts() : void
    {
        $this->expectException(ilBuddySystemException::class);

        $expectedRelation = new ilBuddySystemRelation(
            new ilBuddySystemUnlinkedRelationState(),
            self::BUDDY_LIST_BUDDY_ID,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );
        $expectedRelation = $expectedRelation->withUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation = $expectedRelation->withBuddyUsrId(-3);

        $db = $this->createMock(ilDBInterface::class);
        $db->expects($this->once())->method('queryF');
        $db->expects($this->once())->method('fetchAssoc')->willReturn(null);
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getAll')->willReturn([]);
        $repo->expects($this->never())->method('save');

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);

        $this->assertEquals(new ilBuddySystemRequestedRelationState(), $expectedRelation->getState());
    }

    public function testRepositoryIsEnquiredWhenBuddyListShouldBeDestroyed() : void
    {
        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('destroy');

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->destroy();
    }

    public function testUnlinkedRelationIsReturnedWhenRelationWasRequestedForAnUnknownBuddyId() : void
    {
        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn([]);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $this->assertInstanceOf(ilBuddySystemUnlinkedRelationState::class, $buddyList->getRelationByUserId(-3)->getState());
    }

    public function testValuesCanBeFetchedByGettersWhenSetBySetters() : void
    {
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setOwnerId(self::BUDDY_LIST_BUDDY_ID);
        $this->assertSame(self::BUDDY_LIST_BUDDY_ID, $buddyList->getOwnerId());

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getAll')->willReturn([]);
        $buddyList->setRepository($repo);
        $this->assertEquals($repo, $buddyList->getRepository());

        $relations = [
            self::BUDDY_LIST_BUDDY_ID => new ilBuddySystemRelation(
                new ilBuddySystemUnlinkedRelationState(),
                self::BUDDY_LIST_BUDDY_ID,
                self::BUDDY_LIST_OWNER_ID,
                false,
                time()
            )
        ];
        $buddyList->setRelations(new ilBuddySystemRelationCollection($relations));
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
    }

    public function testDifferentRelationStatesCanBeRetrieved() : void
    {
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $relations = [];

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemLinkedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemLinkedRelationState(),
            self::BUDDY_LIST_BUDDY_ID + 1,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 1] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemLinkedRelationState(),
            self::BUDDY_LIST_BUDDY_ID + 2,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 2] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemRequestedRelationState(),
            self::BUDDY_LIST_BUDDY_ID + 3,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 3] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemRequestedRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID + 4,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 4] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemIgnoredRequestRelationState(),
            self::BUDDY_LIST_BUDDY_ID + 5,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 5] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemIgnoredRequestRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID + 6,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 6] = $relation;

        $relation = new ilBuddySystemRelation(
            new ilBuddySystemIgnoredRequestRelationState(),
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID + 7,
            false,
            time()
        );
        $relations[self::BUDDY_LIST_BUDDY_ID + 7] = $relation;

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->method('getAll')->willReturn($relations);
        $buddyList->setRepository($repo);

        $this->assertCount(3, $buddyList->getLinkedRelations());
        $this->assertCount(1, $buddyList->getRequestRelationsForOwner());
        $this->assertCount(1, $buddyList->getRequestRelationsByOwner());
        $this->assertCount(1, $buddyList->getIgnoredRelationsForOwner());
        $this->assertCount(2, $buddyList->getIgnoredRelationsByOwner());
        $this->assertEquals(array_keys($relations), $buddyList->getRelationUserIds());
    }

    /**
     * @param ilBuddySystemRelation $relation
     * @param ilBuddySystemRelationState $state
     * @throws ReflectionException
     */
    private function setPriorRelationState(
        ilBuddySystemRelation $relation,
        ilBuddySystemRelationState $state
    ) : void {
        $object = new ReflectionObject($relation);
        $property = $object->getProperty('priorState');
        $property->setAccessible(true);

        $property->setValue($relation, $state);
    }

    public function testAlreadyGivenStateExceptionIsThrownWhenALinkedRelationShouldBeMarkedAsLinked() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemLinkedRelationState();

        $relation = new ilBuddySystemRelation(
            $state,
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $this->setPriorRelationState($relation, $state);

        $buddyList->link($relation);
    }

    public function testAlreadyGivenStateExceptionIsThrownWhenAnIgnoredRelationShouldBeMarkedAsIgnored() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemIgnoredRequestRelationState();

        $relation = new ilBuddySystemRelation(
            $state,
            self::BUDDY_LIST_BUDDY_ID,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );

        $this->setPriorRelationState($relation, $state);

        $buddyList->ignore($relation);
    }

    public function testAlreadyGivenStateExceptionIsThrownWhenAnUnlinkedRelationShouldBeMarkedAsUnlinked() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemUnlinkedRelationState();

        $relation = new ilBuddySystemRelation(
            $state,
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $this->setPriorRelationState($relation, $state);

        $buddyList->unlink($relation);
    }

    public function testAlreadyGivenStateExceptionIsThrownWhenARequestedRelationShouldBeMarkedAsRequested() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemRequestedRelationState();

        $relation = new ilBuddySystemRelation(
            $state,
            self::BUDDY_LIST_BUDDY_ID,
            self::BUDDY_LIST_OWNER_ID,
            false,
            time()
        );

        $this->setPriorRelationState($relation, $state);

        $db = $this->createMock(ilDBInterface::class);
        $db->method('fetchAssoc')->willReturn([
            'login' => 'phpunit'
        ]);
        $this->setGlobalVariable('ilDB', $db);

        $buddyList->request($relation);
    }

    public function testStateTransitionExceptionIsThrownWhenALinkedRelationShouldBeMarkedAsIgnored() : void
    {
        $this->expectException(ilBuddySystemRelationStateTransitionException::class);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemLinkedRelationState();

        $relation = new ilBuddySystemRelation(
            $state,
            self::BUDDY_LIST_OWNER_ID,
            self::BUDDY_LIST_BUDDY_ID,
            false,
            time()
        );

        $this->setPriorRelationState($relation, $state);

        $buddyList->ignore($relation);
    }
}
