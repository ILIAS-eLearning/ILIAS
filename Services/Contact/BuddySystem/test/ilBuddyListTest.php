<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/ilBuddySystemBaseTest.php';

/**
 * Class ilBuddyListTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddyListTest extends ilBuddySystemBaseTest
{
    const BUDDY_LIST_OWNER_ID = -1;
    const BUDDY_LIST_BUDDY_ID = -2;

    /** @var ilBuddyList */
    protected $buddyList;

    /**
     *
     */
    public function setUp() : void
    {
        $this->setGlobalVariable(
            'ilAppEventHandler',
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->setMethods(['raise'])->getMock()
        );
        $this->setGlobalVariable('ilDB', $this->getMockBuilder(ilDBInterface::class)->getMock());
        $this->setGlobalVariable(
            'lng',
            $this->getMockBuilder(ilLanguage::class)
                ->disableOriginalConstructor()
                ->setMethods(['txt', 'loadLanguageModule'])
                ->getMock()
        );
    }

    /**
     *
     */
    public function testInstanceCanBeCreatedByGlobalUserObject() : void
    {
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->setMethods(['getId'])->getMock();
        $user->expects($this->once())->method('getId')->will($this->returnValue(self::BUDDY_LIST_OWNER_ID));
        $this->setGlobalVariable('ilUser', $user);

        ilBuddyList::getInstanceByGlobalUser();
    }

    /**
     *
     */
    public function testInstanceCannotBeCreatedByAnonymousGlobalUserObject() : void
    {
        $this->expectException(ilBuddySystemException::class);
        $user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->setMethods(['getId'])->getMock();
        $user->expects($this->once())->method('getId')->will($this->returnValue(ANONYMOUS_USER_ID));
        $this->setGlobalVariable('ilUser', $user);

        ilBuddyList::getInstanceByGlobalUser();
    }

    /**
     *
     */
    public function testInstanceByBeCreatedBySingletonMethod() : void
    {
        $relations = [
            4711 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState()),
            4712 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState())
        ];

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->setRelations(new ilBuddySystemRelationCollection($relations));
        $otherBuddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $otherBuddylist->setRelations(new ilBuddySystemRelationCollection());

        $this->assertEquals($buddyList, $otherBuddylist);
    }

    /**
     *
     */
    public function testListIsInitiallyEmpty() : void
    {
        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->exactly(1))->method('getAll')->willReturn([]);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $this->assertEmpty($buddyList->getRelations());
    }

    /**
     *
     */
    public function testRepositoryIsEnquiredToFetchRelationsWhenRequestedExplicitly() : void
    {
        $relations = [
            4711 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState()),
            4712 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState())
        ];

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->exactly(1))->method('getAll')->willReturn($relations);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
    }

    /**
     *
     */
    public function testRepositoryIsEnquiredOnlyOnceToFetchRelationsWhenCalledImplicitly() : void
    {
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->exactly(2))->method('queryF');
        $db->expects($this->exactly(2))->method('fetchAssoc')->will($this->returnValue([
            'login' => 'phpunit'
        ]));
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

    /**
     *
     */
    public function testRelationRequestCannotBeApprovedByTheRelationOwner() : void
    {
        $this->expectException(ilBuddySystemException::class);
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->once())->method('queryF');
        $db->expects($this->once())->method('fetchAssoc')->will($this->returnValue([
            'login' => 'phpunit'
        ]));
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn($relations);
        $repo->expects($this->exactly(1))->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $relation = $buddyList->getRelationByUserId($expectedRelation->getBuddyUsrId());
        $buddyList->request($relation);
        $buddyList->link($relation);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testRelationRequestCanBeApprovedByTheRelationTarget() : void
    {
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->any())->method('queryF');
        $db->expects($this->any())->method('fetchAssoc')->will($this->returnValue([
            'login' => 'phpunit'
        ]));
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->any())->method('getAll')->willReturn($relations);
        $repo->expects($this->any())->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);

        $other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_BUDDY_ID);
        $other_buddylist->reset();
        $other_buddylist->setRepository($repo);
        $other_buddylist->link($expectedRelation);
    }

    /**
     *
     */
    public function testRelationRequestCannotBeIgnoredByTheRelationOwner() : void
    {
        $this->expectException(ilBuddySystemException::class);
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->once())->method('queryF');
        $db->expects($this->once())->method('fetchAssoc')->will($this->returnValue([
            'login' => 'phpunit'
        ]));
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn($relations);
        $repo->expects($this->exactly(1))->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);

        $relation = $buddyList->getRelationByUserId($expectedRelation->getBuddyUsrId());
        $buddyList->request($relation);
        $buddyList->ignore($relation);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testRelationRequestCanBeIgnoredByTheRelationTarget() : void
    {
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $relations = [
            $expectedRelation->getBuddyUsrId() => $expectedRelation
        ];

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->any())->method('queryF');
        $db->expects($this->any())->method('fetchAssoc')->will($this->returnValue([
            'login' => 'phpunit'
        ]));
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->any())->method('getAll')->willReturn($relations);
        $repo->expects($this->any())->method('save')->with($expectedRelation);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);

        $other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_BUDDY_ID);
        $other_buddylist->reset();
        $other_buddylist->setRepository($repo);
        $other_buddylist->ignore($expectedRelation);
    }

    /**
     *
     */
    public function testRelationCannotBeRequestedForAnonymous() : void
    {
        $this->expectException(ilBuddySystemException::class);
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(ANONYMOUS_USER_ID);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getAll')->willReturn([]);
        $repo->expects($this->never())->method('save');

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);
    }

    /**
     *
     */
    public function testRelationCannotBeRequestedForUnknownUserAccounts() : void
    {
        $this->expectException(ilBuddySystemException::class);
        $expectedRelation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
        $expectedRelation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $expectedRelation->setBuddyUsrId(-3);

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->once())->method('queryF');
        $db->expects($this->once())->method('fetchAssoc')->will($this->returnValue(null));
        $this->setGlobalVariable('ilDB', $db);

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getAll')->willReturn([]);
        $repo->expects($this->never())->method('save');

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->request($expectedRelation);
    }

    /**
     *
     */
    public function testRepositoryIsEnquiredWhenBuddyListShouldBeDestroyed() : void
    {
        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('destroy');

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $buddyList->destroy();
    }

    /**
     *
     */
    public function testUnlinkedRelationIsReturnedWhenRelationWasRequestedForAnUnknownBuddyId() : void
    {
        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->once())->method('getAll')->willReturn([]);

        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setRepository($repo);
        $this->assertInstanceOf('ilBuddySystemUnlinkedRelationState', $buddyList->getRelationByUserId(-3)->getState());
    }

    /**
     *
     */
    public function testValuesCanBeFetchedByGettersWhenSetBySetters() : void
    {
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();
        $buddyList->setOwnerId(self::BUDDY_LIST_BUDDY_ID);
        $this->assertEquals(self::BUDDY_LIST_BUDDY_ID, $buddyList->getOwnerId());

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->never())->method('getAll')->willReturn([]);
        $buddyList->setRepository($repo);
        $this->assertEquals($repo, $buddyList->getRepository());

        $relations = [
            self::BUDDY_LIST_BUDDY_ID => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState())
        ];
        $buddyList->setRelations(new ilBuddySystemRelationCollection($relations));
        $this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddyList->getRelations());
    }

    /**
     *
     */
    public function testDifferentRelationStatesCanBeRetrieved() : void
    {
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $relations = [];

        $relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);
        $relations[self::BUDDY_LIST_BUDDY_ID] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
        $relation->setUsrId(self::BUDDY_LIST_BUDDY_ID + 1);
        $relation->setBuddyUsrId(self::BUDDY_LIST_OWNER_ID);
        $relations[self::BUDDY_LIST_BUDDY_ID + 1] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
        $relation->setUsrId(self::BUDDY_LIST_BUDDY_ID + 2);
        $relation->setBuddyUsrId(self::BUDDY_LIST_OWNER_ID);
        $relations[self::BUDDY_LIST_BUDDY_ID + 2] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemRequestedRelationState());
        $relation->setUsrId(self::BUDDY_LIST_BUDDY_ID + 3);
        $relation->setBuddyUsrId(self::BUDDY_LIST_OWNER_ID);
        $relations[self::BUDDY_LIST_BUDDY_ID + 3] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemRequestedRelationState());
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID + 4);
        $relations[self::BUDDY_LIST_BUDDY_ID + 4] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
        $relation->setUsrId(self::BUDDY_LIST_BUDDY_ID + 5);
        $relation->setBuddyUsrId(self::BUDDY_LIST_OWNER_ID);
        $relations[self::BUDDY_LIST_BUDDY_ID + 5] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID + 6);
        $relations[self::BUDDY_LIST_BUDDY_ID + 6] = $relation;

        $relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID + 7);
        $relations[self::BUDDY_LIST_BUDDY_ID + 7] = $relation;

        $repo = $this->getMockBuilder(ilBuddySystemRelationRepository::class)->disableOriginalConstructor()->getMock();
        $repo->expects($this->any())->method('getAll')->willReturn($relations);
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

    /**
     *
     */
    public function testAlreadyGivenStateExceptionIsThrownWhenALinkedRelationShouldBeMarkedAsLinked() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemLinkedRelationState();

        $relation = new ilBuddySystemRelation($state);
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $this->setPriorRelationState($relation, $state);

        $buddyList->link($relation);
    }

    /**
     *
     */
    public function testAlreadyGivenStateExceptionIsThrownWhenAnIgnoredRelationShouldBeMarkedAsIgnored() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemIgnoredRequestRelationState();

        $relation = new ilBuddySystemRelation($state);
        $relation->setUsrId(self::BUDDY_LIST_BUDDY_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_OWNER_ID);

        $this->setPriorRelationState($relation, $state);

        $buddyList->ignore($relation);
    }

    /**
     *
     */
    public function testAlreadyGivenStateExceptionIsThrownWhenAnUnlinkedRelationShouldBeMarkedAsUnlinked() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemUnlinkedRelationState();

        $relation = new ilBuddySystemRelation($state);
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $this->setPriorRelationState($relation, $state);

        $buddyList->unlink($relation);
    }

    /**
     *
     */
    public function testAlreadyGivenStateExceptionIsThrownWhenARequestedRelationShouldBeMarkedAsRequested() : void
    {
        $this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemRequestedRelationState();

        $relation = new ilBuddySystemRelation($state);
        $relation->setUsrId(self::BUDDY_LIST_BUDDY_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_OWNER_ID);

        $this->setPriorRelationState($relation, $state);

        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->expects($this->any())->method('fetchAssoc')->will($this->returnValue([
            'login' => 'phpunit'
        ]));
        $this->setGlobalVariable('ilDB', $db);

        $buddyList->request($relation);
    }

    /**
     *
     */
    public function testStateTransitionExceptionIsThrownWhenALinkedRelationShouldBeMarkedAsIgnored() : void
    {
        $this->expectException(ilBuddySystemRelationStateTransitionException::class);
        $buddyList = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
        $buddyList->reset();

        $state = new ilBuddySystemLinkedRelationState();

        $relation = new ilBuddySystemRelation($state);
        $relation->setUsrId(self::BUDDY_LIST_OWNER_ID);
        $relation->setBuddyUsrId(self::BUDDY_LIST_BUDDY_ID);

        $this->setPriorRelationState($relation, $state);

        $buddyList->ignore($relation);
    }
}
