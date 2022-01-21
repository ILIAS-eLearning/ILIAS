<?php declare(strict_types=1);

use ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRule as Cancel;

class ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $this->assertInstanceOf(
            Cancel::class,
            new Cancel($relation)
        );
    }

    public function testMatches() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(true);
        $instance = new Cancel($relation);

        $this->assertTrue($instance->matches());
    }

    public function testMatchesRequested() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(false);
        $relation->expects(self::never())->method('isOwnedByActor');
        $instance = new Cancel($relation);

        $this->assertFalse($instance->matches());
    }

    public function testMatchesOwned() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(false);
        $instance = new Cancel($relation);

        $this->assertFalse($instance->matches());
    }

    public function testInvokeFalse() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemRelationState::class);

        $instance = new Cancel($relation);

        $this->assertFalse($instance($state));
    }

    public function testInvoke() : void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemUnlinkedRelationState::class);

        $instance = new Cancel($relation);

        $this->assertTrue($instance($state));
    }

    private function mock(string $className)
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->getMock();
    }
}
