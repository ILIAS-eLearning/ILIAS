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

use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\AccruedValueInputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\AccruedValueOutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ILIAS\LearningSequence\Player\AdaptiveNavigator;
use PHPUnit\Framework\TestCase;

class AdaptiveNavigatorPointsTest extends TestCase
{
    public function testPointsTargetsBecomeGlobalSuccessorsWhenThresholdIsMet(): void
    {
        $items = $this->buildItems([135, 137, 138, 139, 136, 140]);
        $navigator = $this->buildNavigator([
            135 => [$this->mockPointsReward(10, true)],
            137 => [$this->mockPointsInput(10, true), $this->mockPointsReward(20, false)],
            138 => [$this->mockPointsInput(10, true), $this->mockPointsReward(15, false)],
            139 => [$this->mockPointsInput(25, false), $this->mockPointsReward(5, false)],
            136 => [$this->mockPointsInput(30, false)],
            140 => [$this->mockPointsInput(30, false), $this->mockPointsReward(1, false)],
        ]);

        $navigator->preload($items);

        $successor_refs = array_map(
            static fn(LSLearnerItem $item): int => $item->getRefId(),
            $navigator->getSuccessors($items, $items[0])
        );

        $this->assertSame([137, 138], $successor_refs);
    }

    public function testPointsStructuralSuccessorsFollowUnlockSteps(): void
    {
        $items = $this->buildItems([135, 137, 138, 139, 136, 140]);
        $navigator = $this->buildNavigator([
            135 => [$this->mockPointsReward(10, true)],
            137 => [$this->mockPointsInput(10, true), $this->mockPointsReward(20, true)],
            138 => [$this->mockPointsInput(10, true), $this->mockPointsReward(15, true)],
            139 => [$this->mockPointsInput(25, true), $this->mockPointsReward(5, true)],
            136 => [$this->mockPointsInput(30, true)],
            140 => [$this->mockPointsInput(30, true), $this->mockPointsReward(1, true)],
        ]);

        $navigator->preload($items);

        $this->assertSame([137, 138], $this->getSuccessorRefs($navigator, $items, 135));
        $this->assertSame([136, 139, 140], $this->getSuccessorRefs($navigator, $items, 137));
        $this->assertSame([139], $this->getSuccessorRefs($navigator, $items, 138));
        $this->assertSame([136, 140], $this->getSuccessorRefs($navigator, $items, 139));

        $this->assertSame([135], $this->getPredecessorRefs($navigator, $items, 137));
        $this->assertSame([135], $this->getPredecessorRefs($navigator, $items, 138));
        $this->assertSame([137, 138], $this->getPredecessorRefs($navigator, $items, 139));
        $this->assertSame([137, 139], $this->getPredecessorRefs($navigator, $items, 136));
        $this->assertSame([137, 139], $this->getPredecessorRefs($navigator, $items, 140));
    }

    public function testPointsStructuralCalculationTreatsBrokenPointsOutputAsZero(): void
    {
        $items = $this->buildItems([135, 137]);
        $navigator = $this->buildNavigator([
            135 => [$this->mockBrokenPointsReward()],
            137 => [$this->mockPointsInput(1, false)],
        ]);

        $navigator->preload($items);

        $this->assertSame([], $this->getSuccessorRefs($navigator, $items, 135));
        $this->assertSame([], $this->getPredecessorRefs($navigator, $items, 137));
    }

    public function testPointsStructuralCalculationTreatsMissingPointsOutputAsZero(): void
    {
        $items = $this->buildItems([135, 137]);
        $navigator = $this->buildNavigator([
            137 => [$this->mockPointsInput(1, false)],
        ]);

        $navigator->preload($items);

        $this->assertSame([], $this->getSuccessorRefs($navigator, $items, 135));
        $this->assertSame([], $this->getPredecessorRefs($navigator, $items, 137));
    }

    public function testPointsOutputRefIdsAreTakenFromPreloadedNavigatorState(): void
    {
        $items = $this->buildItems([135, 137, 139, 140]);
        $navigator = $this->buildNavigator([
            135 => [$this->mockPointsReward(10, true)],
            137 => [$this->mockOtherReward()],
            139 => [$this->mockPointsInput(10, true)],
            140 => [$this->mockBrokenPointsReward()],
        ]);

        $navigator->preload($items);

        $this->assertSame([135, 140], $navigator->getPointsOutputRefIds($items));
    }

    public function testMixedPointsSourcesOnlyUnlockWithConfiguredPointsOutputs(): void
    {
        $items = $this->buildItems([135, 137, 139, 140]);
        $navigator = $this->buildNavigator([
            135 => [$this->mockPointsReward(5, true)],
            137 => [],
            139 => [$this->mockBrokenPointsReward()],
            140 => [$this->mockPointsInput(6, false)],
        ]);

        $navigator->preload($items);

        $this->assertSame([], $this->getSuccessorRefs($navigator, $items, 135));
        $this->assertSame([], $this->getSuccessorRefs($navigator, $items, 137));
        $this->assertSame([], $this->getSuccessorRefs($navigator, $items, 139));
        $this->assertSame([], $this->getPredecessorRefs($navigator, $items, 140));
    }

    /**
     * @param int[] $ref_ids
     * @return LSLearnerItem[]
     */
    private function buildItems(array $ref_ids): array
    {
        return array_map(function (int $ref_id): LSLearnerItem {
            $item = $this->createStub(LSLearnerItem::class);
            $item->method('getRefId')->willReturn($ref_id);
            return $item;
        }, $ref_ids);
    }

    /**
     * @param array<int, array> $conditions_by_ref_id
     */
    private function buildNavigator(array $conditions_by_ref_id): AdaptiveNavigator
    {
        return new class ($conditions_by_ref_id) extends AdaptiveNavigator {
            public function __construct(private array $seed_conditions)
            {
            }

            public function preload(array $items): void
            {
                $this->conditions_cache = $this->seed_conditions;
                foreach ($items as $item) {
                    $this->conditions_cache[$item->getRefId()] ??= [];
                }
                $this->buildNavigationSourceCaches(array_map(
                    static fn(LSLearnerItem $item): int => $item->getRefId(),
                    $items
                ));
                $this->buildPointsNavigationCaches($items);
            }
        };
    }

    private function mockBrokenPointsReward(): AbstractCondition
    {
        return new class () extends AbstractCondition implements OutputConditionInterface, AccruedValueOutputConditionInterface {
            protected const string NAME = 'points_output';

            public function __construct()
            {
            }

            public function check(): bool
            {
                return true;
            }

            public function getAccumulationIdentifier(): string
            {
                return 'points';
            }

            public function getAccumulatedValue(): int
            {
                throw new LogicException('Points are not stored.');
            }
        };
    }

    private function mockPointsInput(int $points, bool $check_result): AbstractCondition
    {
        return new class ($points, $check_result) extends AbstractCondition implements
            InputConditionInterface,
            InputConditionNavigationAwareInterface,
            AccruedValueInputConditionInterface {
            protected const string NAME = 'points_input';

            public function __construct(private int $points, private bool $check_result)
            {
            }

            public function check(): bool
            {
                return $this->check_result;
            }

            public function getPoints(): int
            {
                return $this->points;
            }

            public function getNavigationMode(): string
            {
                return InputConditionNavigationAwareInterface::NAVIGATION_MODE_GLOBAL;
            }

            public function getNavigationSourceRefIds(): array
            {
                return [];
            }

            public function getAccumulationIdentifier(): string
            {
                return 'points';
            }

            public function getRequiredAccumulatedValue(): int
            {
                return $this->points;
            }
        };
    }

    private function mockPointsReward(int $points, bool $check_result): AbstractCondition
    {
        return new class ($points, $check_result) extends AbstractCondition implements OutputConditionInterface, AccruedValueOutputConditionInterface {
            protected const string NAME = 'points_output';

            public function __construct(private int $points, private bool $check_result)
            {
            }

            public function check(): bool
            {
                return $this->check_result;
            }

            public function getPoints(): int
            {
                return $this->points;
            }

            public function getAccumulationIdentifier(): string
            {
                return 'points';
            }

            public function getAccumulatedValue(): int
            {
                return $this->points;
            }
        };
    }

    private function mockOtherReward(): AbstractCondition
    {
        return new class () extends AbstractCondition implements OutputConditionInterface, AccruedValueOutputConditionInterface {
            protected const string NAME = 'other_output';

            public function __construct()
            {
            }

            public function check(): bool
            {
                return true;
            }

            public function getAccumulationIdentifier(): string
            {
                return 'other';
            }

            public function getAccumulatedValue(): int
            {
                return 10;
            }
        };
    }

    /**
     * @param LSLearnerItem[] $items
     * @return int[]
     */
    private function getSuccessorRefs(AdaptiveNavigator $navigator, array $items, int $ref_id): array
    {
        $current = array_values(array_filter(
            $items,
            static fn(LSLearnerItem $item): bool => $item->getRefId() === $ref_id
        ))[0];

        $refs = array_map(
            static fn(LSLearnerItem $item): int => $item->getRefId(),
            $navigator->getStructuralSuccessors($items, $current)
        );
        sort($refs);

        return $refs;
    }

    /**
     * @param LSLearnerItem[] $items
     * @return int[]
     */
    private function getPredecessorRefs(AdaptiveNavigator $navigator, array $items, int $ref_id): array
    {
        $current = array_values(array_filter(
            $items,
            static fn(LSLearnerItem $item): bool => $item->getRefId() === $ref_id
        ))[0];

        $refs = array_map(
            static fn(LSLearnerItem $item): int => $item->getRefId(),
            $navigator->getPredecessors($items, $current)
        );
        sort($refs);

        return $refs;
    }
}
