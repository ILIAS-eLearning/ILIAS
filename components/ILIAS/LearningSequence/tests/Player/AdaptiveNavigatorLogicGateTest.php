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
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionNavigationAwareInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use ILIAS\LearningSequence\Player\AdaptiveNavigator;
use PHPUnit\Framework\TestCase;

class AdaptiveNavigatorLogicGateTest extends TestCase
{
    public function testStructuralSuccessorsAcceptRepositoryItems(): void
    {
        $items = array_map(function (int $ref_id): LSItem {
            $item = $this->createStub(LSItem::class);
            $item->method('getRefId')->willReturn($ref_id);
            return $item;
        }, [149, 151]);
        $navigator = $this->buildNavigator([
            149 => [],
            151 => [$this->mockEdgeInput([149], true)],
        ]);

        $navigator->preload($items);

        $this->assertSame(
            [151],
            array_map(
                static fn(LSItem $item): int => $item->getRefId(),
                $navigator->getStructuralSuccessors($items, $items[0])
            )
        );
    }

    public function testDependencyConditionsDriveSuccessorsAndPredecessors(): void
    {
        $items = $this->buildItems([149, 151, 152, 153, 154, 150]);
        $navigator = $this->buildNavigator([
            149 => [$this->mockOutput(true)],
            151 => [$this->mockEdgeInput([149], true), $this->mockOutput(true)],
            152 => [$this->mockEdgeInput([149], true), $this->mockOutput(false)],
            153 => [$this->mockDependencyInput([151, 152], true), $this->mockOutput(true)],
            154 => [$this->mockDependencyInput([151, 152], true), $this->mockEdgeInput([153], true), $this->mockOutput(true)],
            150 => [$this->mockDependencyInput([153, 154], true)],
        ]);

        $navigator->preload($items);

        $this->assertSame([151, 152], $this->getSuccessorRefs($navigator, $items, 149));
        $this->assertSame([153], $this->getSuccessorRefs($navigator, $items, 151));
        $this->assertSame([153], $this->getSuccessorRefs($navigator, $items, 152));
        $this->assertSame([150, 154], $this->getSuccessorRefs($navigator, $items, 153));
        $this->assertSame([150], $this->getSuccessorRefs($navigator, $items, 154));

        $this->assertSame([149], $this->getPredecessorRefs($navigator, $items, 151));
        $this->assertSame([149], $this->getPredecessorRefs($navigator, $items, 152));
        $this->assertSame([151, 152], $this->getPredecessorRefs($navigator, $items, 153));
        $this->assertSame([151, 152, 153], $this->getPredecessorRefs($navigator, $items, 154));
        $this->assertSame([153, 154], $this->getPredecessorRefs($navigator, $items, 150));
    }

    public function testMultipleDependencyConditionsOnSameTargetCanBeEnteredFromEachMatchingSource(): void
    {
        $items = $this->buildItems([201, 202, 203]);
        $navigator = $this->buildNavigator([
            201 => [],
            202 => [],
            203 => [
                $this->mockDependencyInput([201], true),
                $this->mockDependencyInput([202], true),
            ],
        ]);

        $navigator->preload($items);

        $this->assertSame([203], $this->getSuccessorRefs($navigator, $items, 201));
        $this->assertSame([203], $this->getSuccessorRefs($navigator, $items, 202));
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
                $this->buildNavigationSourceCaches(array_map(
                    static fn(LSItem $item): int => $item->getRefId(),
                    $items
                ));
                $this->buildPointsNavigationCaches($items);
            }
        };
    }

    private function mockEdgeInput(array $source_ref_ids, bool $check_result): AbstractCondition
    {
        return new class ($source_ref_ids, $check_result) extends AbstractCondition implements InputConditionInterface, InputConditionNavigationAwareInterface {
            public function __construct(private array $source_ref_ids, private bool $check_result)
            {
            }

            public function check(): bool
            {
                return $this->check_result;
            }

            public function getNavigationMode(): string
            {
                return InputConditionNavigationAwareInterface::NAVIGATION_MODE_EDGE;
            }

            public function getNavigationSourceRefIds(): array
            {
                return $this->source_ref_ids;
            }
        };
    }

    private function mockDependencyInput(array $source_ref_ids, bool $check_result): AbstractCondition
    {
        return new class ($source_ref_ids, $check_result) extends AbstractCondition implements InputConditionInterface, InputConditionNavigationAwareInterface {
            public function __construct(private array $source_ref_ids, private bool $check_result)
            {
            }

            public function check(): bool
            {
                return $this->check_result;
            }

            public function getNavigationMode(): string
            {
                return InputConditionNavigationAwareInterface::NAVIGATION_MODE_DEPENDENCY;
            }

            public function getNavigationSourceRefIds(): array
            {
                return $this->source_ref_ids;
            }
        };
    }

    private function mockOutput(bool $check_result): AbstractCondition
    {
        return new class ($check_result) extends AbstractCondition implements OutputConditionInterface {
            public function __construct(private bool $check_result)
            {
            }

            public function check(): bool
            {
                return $this->check_result;
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
            $navigator->getSuccessors($items, $current)
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
