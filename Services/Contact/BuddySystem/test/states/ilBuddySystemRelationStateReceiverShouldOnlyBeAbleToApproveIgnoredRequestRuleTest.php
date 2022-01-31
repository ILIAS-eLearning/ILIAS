<?php declare(strict_types=1);

use ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRule as Approve;

class ilBuddySystemRelationStateReceiverShouldOnlyBeAbleToApproveIgnoredRequestRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $this->assertInstanceOf(
            Approve::class,
            new Approve($relation)
        );
    }

    public function testMatches() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isIgnored')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(false);
        $instance = new Approve($relation);

        $this->assertTrue($instance->matches());
    }

    public function testMatchesIgnored() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isIgnored')->willReturn(false);
        $relation->expects(self::never())->method('isOwnedByActor');
        $instance = new Approve($relation);

        $this->assertFalse($instance->matches());
    }

    public function testMatchesOwned() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isIgnored')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(true);
        $instance = new Approve($relation);

        $this->assertFalse($instance->matches());
    }

    public function testInvokeFalse() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemRelationState::class);

        $instance = new Approve($relation);

        $this->assertFalse($instance($state));
    }

    public function testInvoke() : void
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
