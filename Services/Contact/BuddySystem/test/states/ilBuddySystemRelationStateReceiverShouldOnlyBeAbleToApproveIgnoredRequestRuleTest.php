<?php

declare(strict_types=1);

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

use ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRule as Approve;

class ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $this->assertInstanceOf(
            Approve::class,
            new Approve($relation)
        );
    }

    public function testMatches(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isIgnored')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(false);
        $instance = new Approve($relation);

        $this->assertTrue($instance->matches());
    }

    public function testMatchesIgnored(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isIgnored')->willReturn(false);
        $relation->expects(self::never())->method('isOwnedByActor');
        $instance = new Approve($relation);

        $this->assertFalse($instance->matches());
    }

    public function testMatchesOwned(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isIgnored')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(true);
        $instance = new Approve($relation);

        $this->assertFalse($instance->matches());
    }

    public function testInvokeFalse(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemRelationState::class);

        $instance = new Approve($relation);

        $this->assertFalse($instance($state));
    }

    public function testInvoke(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemLinkedRelationState::class);

        $instance = new Approve($relation);

        $this->assertTrue($instance($state));
    }

    private function mock(string $className)
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
    }
}
