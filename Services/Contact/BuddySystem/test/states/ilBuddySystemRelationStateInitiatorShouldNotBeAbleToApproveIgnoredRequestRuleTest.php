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

use ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRule as DontApprove;

class ilBuddySystemRelationStateInitiatorShouldNotBeAbleToApproveIgnoredRequestRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $this->assertInstanceOf(
            DontApprove::class,
            new DontApprove($relation)
        );
    }

    public function testMatches() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation->expects(self::once())->method('isIgnored')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(true);
        $instance = new DontApprove($relation);

        $this->assertTrue($instance->matches());
    }

    public function testMatchesIgnored() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation->expects(self::once())->method('isIgnored')->willReturn(false);
        $relation->expects(self::never())->method('isOwnedByActor');
        $instance = new DontApprove($relation);

        $this->assertFalse($instance->matches());
    }

    public function testMatchesOwned() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $relation->expects(self::once())->method('isIgnored')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(false);
        $instance = new DontApprove($relation);

        $this->assertFalse($instance->matches());
    }

    public function testInvoke() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $state = $this->getMockBuilder(ilBuddySystemRelationState::class)->disableOriginalConstructor()->getMock();

        $instance = new DontApprove($relation);

        $this->assertTrue($instance($state));
    }

    public function testInvokeFalse() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $state = $this->getMockBuilder(ilBuddySystemLinkedRelationState::class)->disableOriginalConstructor()->getMock();

        $instance = new DontApprove($relation);

        $this->assertFalse($instance($state));
    }
}
