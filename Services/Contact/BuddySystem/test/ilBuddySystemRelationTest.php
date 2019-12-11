<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Contact/BuddySystem/test/ilBuddySystemBaseTest.php';

/**
 * Class ilBuddySystemRelationTest
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 */
class ilBuddySystemRelationTest extends ilBuddySystemBaseTest
{
    const RELATION_OWNER_ID = -1;
    const RELATION_BUDDY_ID = -2;

    /**
     * @var ilBuddyList
     */
    protected $buddylist;

    /**
     *
     */
    public function setUp()
    {
    }

    /**
     *
     */
    public function testPriorStateIsEmptyAfterInstanceWasCreated()
    {
        $state_mock = $this->getMockBuilder('ilBuddySystemRelationState')->getMock();
        $relation   = new ilBuddySystemRelation($state_mock);
        $this->assertNull($relation->getPriorState());
    }

    /**
     *
     */
    public function testPriorStateCanBeRetrievedAfterSubsequentTransitions()
    {
        $state_mock         = $this->getMockBuilder('ilBuddySystemRelationState')->getMock();
        $further_state_mock = $this->getMockBuilder('ilBuddySystemRelationState')->getMock();
        $finish_state_mock  = $this->getMockBuilder('ilBuddySystemRelationState')->getMock();
        $state_mock->expects($this->any())->method('link');

        $relation = new ilBuddySystemRelation($state_mock);
        $relation->setState($further_state_mock);
        $this->assertEquals($state_mock, $relation->getPriorState());
        $relation->setState($finish_state_mock);
        $this->assertEquals($state_mock, $relation->getPriorState());
    }

    /**
     *
     */
    public function testValuesCanBeFetchedByGettersWhenSetBySetters()
    {
        $state_mock = $this->getMockBuilder('ilBuddySystemRelationState')->getMock();
        $relation = new ilBuddySystemRelation($state_mock);

        $relation->setUserId(1);
        $this->assertEquals(1, $relation->getUserId());

        $relation->setBuddyUserId(2);
        $this->assertEquals(2, $relation->getBuddyUserId());

        $ts = time();
        $relation->setTimestamp($ts);
        $this->assertEquals($ts, $relation->getTimestamp());
    }

    /**
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testUsersAreNotAbleToRequestThemselves()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $state_mock = $this->getMockBuilder('ilBuddySystemUnlinkedRelationState')->getMock();
        $expected_relation  = new ilBuddySystemRelation($state_mock);

        $expected_relation->setUserId(self::RELATION_OWNER_ID);
        $expected_relation->setBuddyUserId(self::RELATION_OWNER_ID);

        $expected_relation->request();
    }

    /**
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testUsersAreNotAbleToUnlinkThemselves()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $state_mock = $this->getMockBuilder('ilBuddySystemLinkedRelationState')->getMock();
        $expected_relation  = new ilBuddySystemRelation($state_mock);
        $expected_relation->setUserId(self::RELATION_OWNER_ID);
        $expected_relation->setBuddyUserId(self::RELATION_OWNER_ID);

        $expected_relation->unlink();
    }

    /**
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testUsersAreNotAbleToLinkThemselves()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $state_mock = $this->getMockBuilder('ilBuddySystemRequestedRelationState')->getMock();
        $expected_relation  = new ilBuddySystemRelation($state_mock);
        $expected_relation->setUserId(self::RELATION_OWNER_ID);
        $expected_relation->setBuddyUserId(self::RELATION_OWNER_ID);

        $expected_relation->link();
    }

    /**
     * @expectedException ilBuddySystemRelationStateException
     */
    public function testUsersAreNotAbleToIgnoreThemselves()
    {
        $this->assertException(ilBuddySystemRelationStateException::class);
        $state_mock = $this->getMockBuilder('ilBuddySystemRequestedRelationState')->getMock();
        $expected_relation  = new ilBuddySystemRelation($state_mock);
        $expected_relation->setUserId(self::RELATION_OWNER_ID);
        $expected_relation->setBuddyUserId(self::RELATION_OWNER_ID);

        $expected_relation->ignore();
    }
}
