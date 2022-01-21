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
    private const RELATION_OWNER_ID = -1;
    private const RELATION_BUDDY_ID = -2;

    public function testPriorStateIsEmptyAfterInstanceWasCreated() : void
    {
        $stateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $relation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );
        $this->assertNull($relation->getPriorState());
    }

    public function testPriorStateCanBeRetrievedAfterSubsequentTransitions() : void
    {
        $stateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $furtherStateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $finishStateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $stateMock->method('link');

        $relation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );
        $relation->setState($furtherStateMock);
        $this->assertEquals($stateMock, $relation->getPriorState());
        $relation->setState($finishStateMock);
        $this->assertEquals($stateMock, $relation->getPriorState());
    }

    public function testValuesCanBeFetchedByGettersWhenSetBySetters() : void
    {
        $stateMock = $this->getMockBuilder(ilBuddySystemRelationState::class)->getMock();
        $ts = time();
        $relation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );

        $relation = $relation->withUsrId(1);
        $this->assertEquals(1, $relation->getUsrId());

        $relation = $relation->withBuddyUsrId(2);
        $this->assertEquals(2, $relation->getBuddyUsrId());

        $relation = $relation->withTimestamp($ts + 1);
        $this->assertEquals($ts + 1, $relation->getTimestamp());

        $relation = $relation->withIsOwnedByActor(true);
        $this->assertEquals(true, $relation->isOwnedByActor());
    }

    public function testUsersAreNotAbleToRequestThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemUnlinkedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );

        $expectedRelation = $expectedRelation->withUsrId(self::RELATION_OWNER_ID);
        $expectedRelation = $expectedRelation->withBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->request();
    }

    public function testUsersAreNotAbleToUnlinkThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemLinkedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );
        $expectedRelation = $expectedRelation->withUsrId(self::RELATION_OWNER_ID);
        $expectedRelation = $expectedRelation->withBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->unlink();
    }

    public function testUsersAreNotAbleToLinkThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemRequestedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );
        $expectedRelation = $expectedRelation->withUsrId(self::RELATION_OWNER_ID);
        $expectedRelation = $expectedRelation->withBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->link();
    }

    public function testUsersAreNotAbleToIgnoreThemselves() : void
    {
        $this->expectException(ilBuddySystemRelationStateException::class);
        $stateMock = $this->getMockBuilder(ilBuddySystemRequestedRelationState::class)->getMock();
        $expectedRelation = new ilBuddySystemRelation(
            $stateMock,
            self::RELATION_OWNER_ID,
            self::RELATION_OWNER_ID,
            false,
            time()
        );
        $expectedRelation = $expectedRelation->withUsrId(self::RELATION_OWNER_ID);
        $expectedRelation = $expectedRelation->withBuddyUsrId(self::RELATION_OWNER_ID);

        $expectedRelation->ignore();
    }
}
