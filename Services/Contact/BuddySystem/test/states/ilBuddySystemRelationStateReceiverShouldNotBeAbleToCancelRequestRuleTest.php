<?php declare(strict_types=1);

use ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRule as DontCancel;

class ilBuddySystemRelationStateReceiverShouldNotBeAbleToCancelRequestRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $this->assertInstanceOf(
            DontCancel::class,
            new DontCancel($relation)
        );
    }

    public function testMatches() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(false);
        $instance = new DontCancel($relation);

        $this->assertTrue($instance->matches());
    }

    public function testMatchesRequested() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(false);
        $relation->expects(self::never())->method('isOwnedByActor');
        $instance = new DontCancel($relation);

        $this->assertFalse($instance->matches());
    }

    public function testMatchesOwned() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(true);
        $instance = new DontCancel($relation);

        $this->assertFalse($instance->matches());
    }

    public function testInvokeFalse() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemUnlinkedRelationState::class);

        $instance = new DontCancel($relation);

        $this->assertFalse($instance($state));
    }

    public function testInvoke() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemRelationState::class);
        $instance = new DontCancel($relation);

        $this->assertTrue($instance($state));
    }

    private function mock(string $className)
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
    }
}
