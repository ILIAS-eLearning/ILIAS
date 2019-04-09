<?php
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

	/**
	 * @var ilBuddyList
	 */
	protected $buddylist;

	/**
	 *
	 */
	public function setUp(): void
	{
		$this->setGlobalVariable(
			'ilAppEventHandler',
			$this->getMockBuilder('ilAppEventHandler')->disableOriginalConstructor()->setMethods(array('raise'))->getMock()
		);
		$this->setGlobalVariable('ilDB', $this->getMockBuilder('ilDBInterface')->getMock());
		$this->setGlobalVariable(
			'lng',
			$this->getMockBuilder('ilLanguage')
				->disableOriginalConstructor()
				->setMethods(array('txt', 'loadLanguageModule'))
				->getMock()
		);
	}

	/**
	 *
	 */
	public function testInstanceCanBeCreatedByGlobalUserObject()
	{
		$user = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->setMethods(array('getId'))->getMock();
		$user->expects($this->once())->method('getId')->will($this->returnValue(self::BUDDY_LIST_OWNER_ID));
		$this->setGlobalVariable('ilUser', $user);

		ilBuddyList::getInstanceByGlobalUser();
	}

	/**
	 * 
	 */
	public function testInstanceCannotBeCreatedByAnonymousGlobalUserObject()
	{
		$this->expectException(ilBuddySystemException::class);
		$user = $this->getMockBuilder('ilObjUser')->disableOriginalConstructor()->setMethods(array('getId'))->getMock();
		$user->expects($this->once())->method('getId')->will($this->returnValue(ANONYMOUS_USER_ID));
		$this->setGlobalVariable('ilUser', $user);

		ilBuddyList::getInstanceByGlobalUser();
	}

	/**
	 * 
	 */
	public function testInstanceByBeCreatedBySingletonMethod()
	{
		$relations = array(
			4711 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState()),
			4712 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState())
		);

		$buddylist       = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->setRelations(new ilBuddySystemRelationCollection($relations));
		$other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$other_buddylist->setRelations(new ilBuddySystemRelationCollection());

		$this->assertEquals($buddylist, $other_buddylist);
	}

	/**
	 * 
	 */
	public function testListIsInitiallyEmpty()
	{
		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->exactly(1))->method('getAll')->willReturn(array());

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);

		$this->assertEmpty($buddylist->getRelations());
	}

	/**
	 *
	 */
	public function testRepositoryIsEnquiredToFetchRelationsWhenRequestedExplicitly()
	{
		$relations = array(
			4711 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState()),
			4712 => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState())
		);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->exactly(1))->method('getAll')->willReturn($relations);

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddylist->getRelations());
		$this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddylist->getRelations());
		$this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddylist->getRelations());
	}

	/**
	 *
	 */
	public function testRepositoryIsEnquiredOnlyOnceToFetchRelationsWhenCalledImplicitly()
	{
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$relations = array(
			$expected_relation->getBuddyUserId() => $expected_relation
		);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->exactly(2))->method('queryF');
		$db->expects($this->exactly(2))->method('fetchAssoc')->will($this->returnValue(array(
			'login' => 'phpunit'
		)));
		$this->setGlobalVariable('ilDB', $db);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->once())->method('getAll')->willReturn($relations);
		$repo->expects($this->exactly(3))->method('save')->with($expected_relation);

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);

		$relation = $buddylist->getRelationByUserId($expected_relation->getBuddyUserId());
		$buddylist->request($relation);
		$buddylist->unlink($relation);
		$buddylist->request($relation);
	}

	/**
	 * 
	 */
	public function testRelationRequestCannotBeApprovedByTheRelationOwner()
	{
		$this->expectException(ilBuddySystemException::class);
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$relations = array(
			$expected_relation->getBuddyUserId() => $expected_relation
		);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->once())->method('queryF');
		$db->expects($this->once())->method('fetchAssoc')->will($this->returnValue(array(
			'login' => 'phpunit'
		)));
		$this->setGlobalVariable('ilDB', $db);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->once())->method('getAll')->willReturn($relations);
		$repo->expects($this->exactly(1))->method('save')->with($expected_relation);

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);

		$relation = $buddylist->getRelationByUserId($expected_relation->getBuddyUserId());
		$buddylist->request($relation);
		$buddylist->link($relation);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testRelationRequestCanBeApprovedByTheRelationTarget()
	{
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$relations = array(
			$expected_relation->getBuddyUserId() => $expected_relation
		);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->any())->method('queryF');
		$db->expects($this->any())->method('fetchAssoc')->will($this->returnValue(array(
			'login' => 'phpunit'
		)));
		$this->setGlobalVariable('ilDB', $db);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->any())->method('getAll')->willReturn($relations);
		$repo->expects($this->any())->method('save')->with($expected_relation);

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$buddylist->request($expected_relation);

		$other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_BUDDY_ID);
		$other_buddylist->reset();
		$other_buddylist->setRepository($repo);
		$other_buddylist->link($expected_relation);
	}

	/**
	 * 
	 */
	public function testRelationRequestCannotBeIgnoredByTheRelationOwner()
	{
		$this->expectException(ilBuddySystemException::class);
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$relations = array(
			$expected_relation->getBuddyUserId() => $expected_relation
		);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->once())->method('queryF');
		$db->expects($this->once())->method('fetchAssoc')->will($this->returnValue(array(
			'login' => 'phpunit'
		)));
		$this->setGlobalVariable('ilDB', $db);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->once())->method('getAll')->willReturn($relations);
		$repo->expects($this->exactly(1))->method('save')->with($expected_relation);

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);

		$relation = $buddylist->getRelationByUserId($expected_relation->getBuddyUserId());
		$buddylist->request($relation);
		$buddylist->ignore($relation);
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testRelationRequestCanBeIgnoredByTheRelationTarget()
	{
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$relations = array(
			$expected_relation->getBuddyUserId() => $expected_relation
		);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->any())->method('queryF');
		$db->expects($this->any())->method('fetchAssoc')->will($this->returnValue(array(
			'login' => 'phpunit'
		)));
		$this->setGlobalVariable('ilDB', $db);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->any())->method('getAll')->willReturn($relations);
		$repo->expects($this->any())->method('save')->with($expected_relation);

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$buddylist->request($expected_relation);

		$other_buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_BUDDY_ID);
		$other_buddylist->reset();
		$other_buddylist->setRepository($repo);
		$other_buddylist->ignore($expected_relation);
	}

	/**
	 * 
	 */
	public function testRelationCannotBeRequestedForAnonymous()
	{
		$this->expectException(ilBuddySystemException::class);
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(ANONYMOUS_USER_ID);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->never())->method('getAll')->willReturn(array());
		$repo->expects($this->never())->method('save');

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$buddylist->request($expected_relation);
	}

	/**
	 * 
	 */
	public function testRelationCannotBeRequestedForUnknownUserAccounts()
	{
		$this->expectException(ilBuddySystemException::class);
		$expected_relation = new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState());
		$expected_relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$expected_relation->setBuddyUserId(-3);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->once())->method('queryF');
		$db->expects($this->once())->method('fetchAssoc')->will($this->returnValue(null));
		$this->setGlobalVariable('ilDB', $db);

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->never())->method('getAll')->willReturn(array());
		$repo->expects($this->never())->method('save');

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$buddylist->request($expected_relation);
	}

	/**
	 * 
	 */
	public function testRepositoryIsEnquiredWhenBuddyListShouldBeDestroyed()
	{
		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->once())->method('destroy');

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$buddylist->destroy();
	}

	/**
	 *
	 */
	public function testUnlinkedRelationIsReturnedWhenRelationWasRequestedForAUknownBuddyId()
	{
		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->once())->method('getAll')->willReturn(array());

		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setRepository($repo);
		$this->assertInstanceOf('ilBuddySystemUnlinkedRelationState', $buddylist->getRelationByUserId(-3)->getState());
	}

	/**
	 *
	 */
	public function testValuesCanBeFetchedByGettersWhenSetBySetters()
	{
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setOwnerId(self::BUDDY_LIST_BUDDY_ID);
		$this->assertEquals(self::BUDDY_LIST_BUDDY_ID, $buddylist->getOwnerId());

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->never())->method('getAll')->willReturn(array());
		$buddylist->setRepository($repo);
		$this->assertEquals($repo, $buddylist->getRepository());

		$relations = array(
			self::BUDDY_LIST_BUDDY_ID => new ilBuddySystemRelation(new ilBuddySystemUnlinkedRelationState())
		);
		$buddylist->setRelations(new ilBuddySystemRelationCollection($relations));
		$this->assertEquals(new ilBuddySystemRelationCollection($relations), $buddylist->getRelations());
	}

	/**
	 *
	 */
	public function testDifferentRelationStatesCanBeRetrieved()
	{
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();

		$relations = array();
	
		$relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);
		$relations[self::BUDDY_LIST_BUDDY_ID] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
		$relation->setUserId(self::BUDDY_LIST_BUDDY_ID + 1);
		$relation->setBuddyUserId(self::BUDDY_LIST_OWNER_ID);
		$relations[self::BUDDY_LIST_BUDDY_ID + 1] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemLinkedRelationState());
		$relation->setUserId(self::BUDDY_LIST_BUDDY_ID + 2);
		$relation->setBuddyUserId(self::BUDDY_LIST_OWNER_ID);
		$relations[self::BUDDY_LIST_BUDDY_ID + 2] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemRequestedRelationState());
		$relation->setUserId(self::BUDDY_LIST_BUDDY_ID + 3);
		$relation->setBuddyUserId(self::BUDDY_LIST_OWNER_ID);
		$relations[self::BUDDY_LIST_BUDDY_ID + 3] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemRequestedRelationState());
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID + 4);
		$relations[self::BUDDY_LIST_BUDDY_ID + 4] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
		$relation->setUserId(self::BUDDY_LIST_BUDDY_ID + 5);
		$relation->setBuddyUserId(self::BUDDY_LIST_OWNER_ID);
		$relations[self::BUDDY_LIST_BUDDY_ID + 5] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID + 6);
		$relations[self::BUDDY_LIST_BUDDY_ID + 6] = $relation;

		$relation = new ilBuddySystemRelation(new ilBuddySystemIgnoredRequestRelationState());
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID + 7);
		$relations[self::BUDDY_LIST_BUDDY_ID + 7] = $relation;

		$repo = $this->getMockBuilder('ilBuddySystemRelationRepository')->disableOriginalConstructor()->getMock();
		$repo->expects($this->any())->method('getAll')->willReturn($relations);
		$buddylist->setRepository($repo);

		$this->assertCount(3, $buddylist->getLinkedRelations());
		$this->assertCount(1, $buddylist->getRequestRelationsForOwner());
		$this->assertCount(1, $buddylist->getRequestRelationsByOwner());
		$this->assertCount(1, $buddylist->getIgnoredRelationsForOwner());
		$this->assertCount(2, $buddylist->getIgnoredRelationsByOwner());
		$this->assertEquals(array_keys($relations), $buddylist->getRelationUserIds());
	}

	/**
	 * 
	 */
	public function testExceptionIsThrownWhenNonNumericOwnerIdIsPassed()
	{
		$this->expectException(InvalidArgumentException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->setOwnerId("phpunit");
	}

	/**
	 * 
	 */
	public function testExceptionIsThrownWhenRelationIsRequestedForANonNumericUserId()
	{
		$this->expectException(InvalidArgumentException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();
		$buddylist->getRelationByUserId("phpunit");
	}

	final private function setPriorRelationState(ilBuddySystemRelation $relation, ilBuddySystemRelationState $state)
	{
		$object = new ReflectionObject($relation);
		$property = $object->getProperty('prior_state');
		$property->setAccessible(true);

		$property->setValue($relation, $state);
	}

	/**
	 * 
	 */
	public function testAlreadyGivenStateExceptionIsThrownWhenALinkedRelationShouldBeMarkedAsLinked()
	{
		$this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();

		$state = new ilBuddySystemLinkedRelationState();

		$relation = new ilBuddySystemRelation($state);
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$this->setPriorRelationState($relation, $state);

		$buddylist->link($relation);
	}

	/**
	 * 
	 */
	public function testAlreadyGivenStateExceptionIsThrownWhenAnIgnoredRelationShouldBeMarkedAsIgnored()
	{
		$this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();

		$state = new ilBuddySystemIgnoredRequestRelationState();

		$relation = new ilBuddySystemRelation($state);
		$relation->setUserId(self::BUDDY_LIST_BUDDY_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_OWNER_ID);

		$this->setPriorRelationState($relation, $state);

		$buddylist->ignore($relation);
	}

	/**
	 * 
	 */
	public function testAlreadyGivenStateExceptionIsThrownWhenAnUnlinkedRelationShouldBeMarkedAsUnlinked()
	{
		$this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();

		$state = new ilBuddySystemUnlinkedRelationState();

		$relation = new ilBuddySystemRelation($state);
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$this->setPriorRelationState($relation, $state);

		$buddylist->unlink($relation);
	}

	/**
	 * 
	 */
	public function testAlreadyGivenStateExceptionIsThrownWhenARequestedRelationShouldBeMarkedAsRequested()
	{
		$this->expectException(ilBuddySystemRelationStateAlreadyGivenException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();

		$state = new ilBuddySystemRequestedRelationState();

		$relation = new ilBuddySystemRelation($state);
		$relation->setUserId(self::BUDDY_LIST_BUDDY_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_OWNER_ID);

		$this->setPriorRelationState($relation, $state);

		$db = $this->getMockBuilder('ilDBInterface')->getMock();
		$db->expects($this->any())->method('fetchAssoc')->will($this->returnValue(array(
			'login' => 'phpunit'
		)));
		$this->setGlobalVariable('ilDB', $db);

		$buddylist->request($relation);
	}

	/**
	 * 
	 */
	public function testStateTransitionExceptionIsThrownWhenALinkedRelationShouldBeMarkedAsIgnored()
	{
		$this->expectException(ilBuddySystemRelationStateTransitionException::class);
		$buddylist = ilBuddyList::getInstanceByUserId(self::BUDDY_LIST_OWNER_ID);
		$buddylist->reset();

		$state = new ilBuddySystemLinkedRelationState();

		$relation = new ilBuddySystemRelation($state);
		$relation->setUserId(self::BUDDY_LIST_OWNER_ID);
		$relation->setBuddyUserId(self::BUDDY_LIST_BUDDY_ID);

		$this->setPriorRelationState($relation, $state);

		$buddylist->ignore($relation);
	}
}