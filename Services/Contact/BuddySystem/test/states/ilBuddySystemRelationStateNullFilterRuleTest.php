<?php declare(strict_types=1);

class ilBuddySystemRelationStateNullFilterRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct() : ilBuddySystemRelationStateNullFilterRule
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();
        $instance = new ilBuddySystemRelationStateNullFilterRule($relation);
        $this->assertInstanceOf(
            ilBuddySystemRelationStateNullFilterRule::class,
            $instance
        );

        return $instance;
    }

    /**
     * @depends testConstruct
     */
    public function testMatches(ilBuddySystemRelationStateNullFilterRule $instance) : void
    {
        $this->assertTrue($instance->matches());
    }

    /**
     * @depends testConstruct
     */
    public function testInvoke(ilBuddySystemRelationStateNullFilterRule $instance) : void
    {
        $this->assertTrue($instance($this->getMockBuilder(ilBuddySystemRelationState::class)->disableOriginalConstructor()->getMock()));
    }

    public function testGetStates() : void
    {
        $relation = $this->getMockBuilder(ilBuddySystemRelation::class)->disableOriginalConstructor()->getMock();

        $instance = new ilBuddySystemRelationStateNullFilterRule($relation);

        $filtered = $this->getMockBuilder(ilBuddySystemRelationStateCollection::class)->disableOriginalConstructor()->getMock();

        $collection = $this->getMockBuilder(ilBuddySystemRelationStateCollection::class)->disableOriginalConstructor()->getMock();
        $collection->expects(self::once())->method('filter')->with($instance)->willReturn($filtered);

        $state = $this->getMockBuilder(ilBuddySystemRelationState::class)->disableOriginalConstructor()->getMock();
        $state->expects(self::once())->method('getPossibleTargetStates')->willReturn($collection);

        $relation->expects(self::once())->method('getState')->willReturn($state);

        $this->assertEquals($filtered, $instance->getStates());
    }
}
