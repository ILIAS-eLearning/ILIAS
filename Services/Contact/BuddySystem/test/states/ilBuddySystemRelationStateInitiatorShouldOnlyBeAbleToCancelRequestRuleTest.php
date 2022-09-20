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

use ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRule as Cancel;

class ilBuddySystemRelationStateInitiatorShouldOnlyBeAbleToCancelRequestRuleTest extends ilBuddySystemBaseTest
{
    public function testConstruct(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $this->assertInstanceOf(
            Cancel::class,
            new Cancel($relation)
        );
    }

    public function testMatches(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(true);
        $instance = new Cancel($relation);

        $this->assertTrue($instance->matches());
    }

    public function testMatchesRequested(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(false);
        $relation->expects(self::never())->method('isOwnedByActor');
        $instance = new Cancel($relation);

        $this->assertFalse($instance->matches());
    }

    public function testMatchesOwned(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $relation->expects(self::once())->method('isRequested')->willReturn(true);
        $relation->expects(self::once())->method('isOwnedByActor')->willReturn(false);
        $instance = new Cancel($relation);

        $this->assertFalse($instance->matches());
    }

    public function testInvokeFalse(): void
    {
        $relation = $this->mock(ilBuddySystemRelation::class);
        $state = $this->mock(ilBuddySystemRelationState::class);

        $instance = new Cancel($relation);

        $this->assertFalse($instance($state));
    }

    public function testInvoke(): void
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
