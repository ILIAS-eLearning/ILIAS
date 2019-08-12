<?php declare(strict_types=1);
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
     *
     */
    public function testPriorStateIsEmptyAfterInstanceWasCreated() : void
    {
        $stateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $relation = new ilBuddySystemRelation($stateMock);
        $this->assertNull($relation->getPriorState());
    }

    /**
     *
     */
    public function testPriorStateCanBeRetrievedAfterSubsequentTransitions() : void
    {
        $stateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $furtherStateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $finishStateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $stateMock->expects($this->any())->method('link');

        $relation = new ilBuddySystemRelation($stateMock);
        $relation->setState($furtherStateMock);
        $this->assertEquals($stateMock, $relation->getPriorState());
        $relation->setState($finishStateMock);
        $this->assertEquals($stateMock, $relation->getPriorState());
    }

    /**
     *
     */
    public function testValuesCanBeFetchedByGettersWhenSetBySetters() : void
    {
        $stateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $relation = new ilBuddySystemRelation($stateMock);

        $relation->setUsrId(1);
        $this->assertEquals(1, $relation->getUsrId());

        $relation->setBuddyUsrId(2);
        $this->assertEquals(2, $relation->getBuddyUsrId());

        $ts = time();
        $relation->setTimestamp($ts);
        $this->assertEquals($ts, $relation->getTimestamp());
    }

    /**
     *
     */
    public function testUsersAreNotAbleToRequestThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemUnlinkedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation($stateMock);

        $expectedRelation->setUsrId(self::RELATION_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->request();
    }

    /**
     *
     */
    public function testUsersAreNotAbleToUnlinkThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemLinkedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation($stateMock);
        $expectedRelation->setUsrId(self::RELATION_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->unlink();
    }

    /**
     *
     */
    public function testUsersAreNotAbleToLinkThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemRequestedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation($stateMock);
        $expectedRelation->setUsrId(self::RELATION_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->link();
    }

    /**
     *
     */
    public function testUsersAreNotAbleToIgnoreThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemRequestedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation($stateMock);
        $expectedRelation->setUsrId(self::RELATION_OWNER_ID);
        $expectedRelation->setBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->ignore();
    }
}