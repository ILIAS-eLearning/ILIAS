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
 * Class ilBuddySystemRelationTest
 * @author Michael Jansen <mjansen@databay.de>
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
        $this->assertSame(1, $relation->getUsrId());

        $relation = $relation->withBuddyUsrId(2);
        $this->assertSame(2, $relation->getBuddyUsrId());

        $relation = $relation->withTimestamp($ts + 1);
        $this->assertSame($ts + 1, $relation->getTimestamp());

        $relation = $relation->withIsOwnedByActor(true);
        $this->assertTrue($relation->isOwnedByActor());
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
