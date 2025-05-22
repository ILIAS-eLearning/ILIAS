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

use PHPUnit\Framework\Attributes\Depends;

class ilBuddySystemRelationStateNullFilterRuleTestCase extends ilBuddySystemBaseTestCase
{
    public function testConstruct(): ilBuddySystemRelationStateNullFilterRule
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $instance = new ilBuddySystemRelationStateNullFilterRule($relation);
        $this->assertInstanceOf(
            ilBuddySystemRelationStateNullFilterRule::class,
            $instance
        );

        return $instance;
    }

    #[Depends('testConstruct')]
    public function testMatches(ilBuddySystemRelationStateNullFilterRule $instance): void
    {
        $this->assertTrue($instance->matches());
    }

    #[Depends('testConstruct')]
    public function testInvoke(ilBuddySystemRelationStateNullFilterRule $instance): void
    {
        $this->assertTrue($instance($this->getMockBuilder(ilBuddySystemRelationState::class)->disableOriginalConstructor()->getMock()));
    }

    public function testGetStates(): void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();

        $instance = new ilBuddySystemRelationStateNullFilterRule($relation);

        $filtered = $this->getMockBuilder(ilBuddySystemRelationStateCollection::class)->disableOriginalConstructor()->getMock();

        $collection = $this->getMockBuilder(ilBuddySystemRelationStateCollection::class)->disableOriginalConstructor()->getMock();
        $collection->expects($this->once())->method('filter')->with($instance)->willReturn($filtered);

        $state = $this->getMockBuilder(ilBuddySystemRelationState::class)->disableOriginalConstructor()->getMock();
        $state->expects($this->once())->method('getPossibleTargetStates')->willReturn($collection);

        $relation->expects($this->once())->method('getState')->willReturn($state);

        $this->assertEquals($filtered, $instance->getStates());
    }
}
