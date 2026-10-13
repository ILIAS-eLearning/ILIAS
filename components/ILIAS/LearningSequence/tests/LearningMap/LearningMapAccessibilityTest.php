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

namespace ILIAS\LearningSequence\LearningMap;

use ILIAS\LearningSequence\Content\Adaptive\LSOAdaptiveBoundaries;
use ILIAS\LearningSequence\Content\Adaptive\LSOItemPath;
use ILIAS\LearningSequence\Player\LSNavigator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LearningMapAccessibilityTest extends TestCase
{
    public function testVisitedNodeIsNotAutomaticallyAccessible(): void
    {
        $navigator = $this->createMock(LSNavigator::class);
        $navigator->method('canEnterIgnoringEdges')->willReturn(false);

        $position = $this->createMock(LSOLearningMapPosition::class);
        $position->expects($this->any())->method('hasVisited')->with(2001)->willReturn(true);

        $item = $this->createMock(\LSLearnerItem::class);
        $url_builder = $this->createStub(\LSUrlBuilder::class);

        $builder = new class ($navigator, $url_builder) extends LSOLearningMapDataBuilder {
            public function __construct(LSNavigator $navigator, \LSUrlBuilder $url_builder)
            {
                parent::__construct(
                    $navigator,
                    $url_builder,
                    'goto',
                    1,
                    6,
                    static fn(): LSOLearningMapPosition => throw new \LogicException('unused'),
                    static fn(): array => []
                );
            }

            /**
             * @param \LSLearnerItem[] $items
             */
            public function exposeCanAccess(
                LSOLearningMapPosition $position,
                array $items,
                \LSLearnerItem $item,
                int $obj_id,
                int $start_obj_id
            ): bool {
                return $this->canAccess($position, $items, $item, $obj_id, $start_obj_id);
            }
        };

        $this->assertFalse($builder->exposeCanAccess($position, [$item], $item, 2001, 1001));
    }

    public function testJumpAccessRequiresPassablePredecessor(): void
    {
        $navigator = $this->createMock(LSNavigator::class);
        $navigator->method('canEnterIgnoringEdges')->willReturn(true);

        $target = $this->mockItem(153);
        $predecessor = $this->mockItem(151);

        $navigator->method('getPredecessors')->willReturn([$predecessor]);
        $navigator->expects($this->any())->method('canLeave')->with($predecessor)->willReturn(true);
        $navigator->expects($this->any())->method('canEnterFrom')->with($predecessor, $target)->willReturn(false);

        $position = new class ($navigator, $this->createStub(LSOItemPath::class), $this->createStub(LSOAdaptiveBoundaries::class)) extends LSOLearningMapPosition {
            public function __construct(
                LSNavigator $navigator,
                LSOItemPath $item_path,
                LSOAdaptiveBoundaries $boundaries
            ) {
                parent::__construct(
                    $navigator,
                    $item_path,
                    $boundaries,
                    421,
                    6
                );
            }

            /**
             * @param \LSLearnerItem[] $items
             */
            public function exposeMayAccess(array $items, \LSLearnerItem $item): bool
            {
                return $this->mayAccess($items, $item);
            }

            protected function getStartRefId(): int
            {
                return 149;
            }

            protected function lookupObjId(int $ref_id): int
            {
                return match ($ref_id) {
                    151 => 2001,
                    153 => 2003,
                    default => 0
                };
            }

            public function hasCompleted(array $items, int $obj_id): bool
            {
                return $obj_id === 2001;
            }
        };

        $this->assertFalse($position->exposeMayAccess([$target, $predecessor], $target));
    }

    public function testSequentialAlwaysPostConditionRequiresVisitForCompletion(): void
    {
        $navigator = new LSOLearningMapSequentialNavigator();
        $item = $this->mockItem(151);
        $item->method('getPostCondition')->willReturn(
            new \ilLSPostCondition(151, \ilLSPostCondition::OPERATOR_ALWAYS)
        );

        $position = $this->createPosition($navigator, [151 => 2001], []);

        $this->assertFalse($position->hasCompleted([$item], 2001));
    }

    public function testSequentialPositionUsesVisitBasedCompletionForAlwaysPostCondition(): void
    {
        $navigator = new LSOLearningMapSequentialNavigator();
        $item = $this->mockItem(151);
        $item->method('getPostCondition')->willReturn(
            new \ilLSPostCondition(151, \ilLSPostCondition::OPERATOR_ALWAYS)
        );

        $position = new class ($navigator, $this->createStub(LSOItemPath::class), $this->createStub(LSOAdaptiveBoundaries::class)) extends LSOLearningMapSequentialPosition {
            public function __construct(
                LSNavigator $navigator,
                LSOItemPath $item_path,
                LSOAdaptiveBoundaries $boundaries
            ) {
                parent::__construct($navigator, $item_path, $boundaries, 421, 6);
            }

            protected function lookupObjId(int $ref_id): int
            {
                return $ref_id === 151 ? 2001 : 0;
            }

            public function hasVisited(int $obj_id): bool
            {
                return false;
            }
        };

        $position->prepareForItems([$item]);

        $this->assertFalse($position->hasCompleted([$item], 2001));
    }

    public function testItemWithoutOutputConditionsRequiresVisitForCompletion(): void
    {
        $navigator = $this->createMock(LSNavigator::class);
        $navigator->method('getOutputConditionIds')->willReturn([]);
        $item = $this->mockItem(151);

        $position = $this->createPosition($navigator, [151 => 2001], []);

        $this->assertFalse($position->hasCompleted([$item], 2001));
    }

    public function testLearningProgressOutputConditionCanCompleteWithoutVisit(): void
    {
        $navigator = $this->createMock(LSNavigator::class);
        $navigator->method('getOutputConditionIds')->willReturn([1]);
        $navigator->method('canLeave')->willReturn(true);
        $item = $this->mockItem(151);

        $position = $this->createPosition($navigator, [151 => 2001], []);

        $this->assertTrue($position->hasCompleted([$item], 2001));
    }

    public function testAdaptiveAlwaysOutputConditionRequiresVisitForCompletion(): void
    {
        $navigator = $this->getMockBuilder(\ILIAS\LearningSequence\Player\AdaptiveNavigator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOutputConditionIds', 'requiresVisitForCompletion', 'canLeave'])
            ->getMock();
        $navigator->method('getOutputConditionIds')->willReturn([1]);
        $navigator->method('requiresVisitForCompletion')->willReturn(true);
        $navigator->method('canLeave')->willReturn(true);
        $item = $this->mockItem(151);

        $position = $this->createPosition($navigator, [151 => 2001], []);

        $this->assertFalse($position->hasCompleted([$item], 2001));
    }

    public function testFallbackSuccessorsUseNearestPreviousBranchNode(): void
    {
        $navigator = $this->createMock(LSNavigator::class);

        $start = $this->mockItem(149);
        $page1 = $this->mockItem(151);
        $page2 = $this->mockItem(152);
        $page3 = $this->mockItem(153);
        $items = [$start, $page1, $page2, $page3];

        $navigator->method('getSuccessors')
            ->willReturnCallback(
                static fn(array $items, \LSLearnerItem $current): array => match ($current->getRefId()) {
                    151 => [],
                    149 => [$page1, $page2, $page3],
                    default => [],
                }
            );
        $navigator->method('canLeave')
            ->willReturnCallback(
                static fn(\LSLearnerItem $current): bool => $current->getRefId() === 149
            );

        $item_path = $this->createStub(LSOItemPath::class);
        $boundaries = $this->createStub(LSOAdaptiveBoundaries::class);

        $position = new class ($navigator, $item_path, $boundaries) extends LSOLearningMapPosition {
            public function __construct(
                LSNavigator $navigator,
                LSOItemPath $item_path,
                LSOAdaptiveBoundaries $boundaries
            ) {
                parent::__construct(
                    $navigator,
                    $item_path,
                    $boundaries,
                    421,
                    6
                );
            }

            protected function getPath(): array
            {
                return [149, 152, 151];
            }
        };

        $successors = $position->getSuccessors($items, $page1);
        $refs = array_map(static fn(\LSLearnerItem $item): int => $item->getRefId(), $successors);
        sort($refs);

        $this->assertSame([152, 153], $refs);
    }

    public function testSuccessorsExcludeItemsWithFulfilledOutputConditions(): void
    {
        $navigator = $this->createMock(LSNavigator::class);

        $current = $this->mockItem(149);
        $completed = $this->mockItem(151);
        $open = $this->mockItem(152);
        $items = [$current, $completed, $open];

        $navigator->method('getSuccessors')
            ->willReturnCallback(
                static fn(array $items, \LSLearnerItem $item): array => $item->getRefId() === 149 ? [$completed, $open] : []
            );
        $navigator->method('getOutputConditionIds')
            ->willReturnCallback(
                static fn(\LSLearnerItem $item): array => $item->getRefId() === 151 ? [1] : []
            );
        $navigator->method('canLeave')
            ->willReturnCallback(
                static fn(\LSLearnerItem $item): bool => $item->getRefId() === 149 || $item->getRefId() === 151
            );

        $position = $this->createPosition($navigator, [149 => 2000, 151 => 2001, 152 => 2002], []);

        $successors = $position->getSuccessors($items, $current);
        $refs = array_map(static fn(\LSLearnerItem $item): int => $item->getRefId(), $successors);

        $this->assertSame([152], $refs);
    }

    public function testSuccessorsIgnoreLearningProgressWhenNoOutputConditionExists(): void
    {
        $navigator = $this->createMock(LSNavigator::class);

        $current = $this->mockItem(149);
        $lp_completed = $this->mockItem(151);
        $items = [$current, $lp_completed];

        $navigator->method('getSuccessors')
            ->willReturnCallback(
                static fn(array $items, \LSLearnerItem $item): array => $item->getRefId() === 149 ? [$lp_completed] : []
            );
        $navigator->method('getOutputConditionIds')->willReturn([]);
        $navigator->method('canLeave')->willReturn(true);

        $item_path = $this->createStub(LSOItemPath::class);
        $boundaries = $this->createStub(LSOAdaptiveBoundaries::class);
        $position = new class ($navigator, $item_path, $boundaries) extends LSOLearningMapPosition {
            public function __construct(
                LSNavigator $navigator,
                LSOItemPath $item_path,
                LSOAdaptiveBoundaries $boundaries
            ) {
                parent::__construct($navigator, $item_path, $boundaries, 421, 6);
            }

            protected function lookupObjId(int $ref_id): int
            {
                return match ($ref_id) {
                    149 => 2000,
                    151 => 2001,
                    default => 0
                };
            }

            protected function hasLearningProgressCompleted(\LSLearnerItem $item, int $obj_id): bool
            {
                return true;
            }
        };

        $successors = $position->getSuccessors($items, $current);
        $refs = array_map(static fn(\LSLearnerItem $item): int => $item->getRefId(), $successors);

        $this->assertSame([151], $refs);
    }

    public function testItemWithoutStructuralSuccessorIsDeadEndDespiteFallbackSuccessors(): void
    {
        $navigator = $this->createMock(LSNavigator::class);

        $start = $this->mockItem(149);
        $current = $this->mockItem(151);
        $alternative = $this->mockItem(152);
        $items = [$start, $current, $alternative];

        $navigator->method('canLeave')->willReturn(true);
        $navigator->method('getSuccessors')
            ->willReturnCallback(
                static fn(array $items, \LSLearnerItem $item): array => match ($item->getRefId()) {
                    149 => [$current, $alternative],
                    default => [],
                }
            );
        $navigator->method('getStructuralSuccessors')->willReturn([]);

        $item_path = $this->createStub(LSOItemPath::class);
        $boundaries = $this->createStub(LSOAdaptiveBoundaries::class);

        $position = new class ($navigator, $item_path, $boundaries) extends LSOLearningMapPosition {
            public function __construct(
                LSNavigator $navigator,
                LSOItemPath $item_path,
                LSOAdaptiveBoundaries $boundaries
            ) {
                parent::__construct($navigator, $item_path, $boundaries, 421, 6);
            }

            /**
             * @param \LSLearnerItem[] $items
             */
            public function exposeSituation(array $items, \LSLearnerItem $item): string
            {
                return $this->getSituation($items, $item);
            }

            protected function getEndRefId(): int
            {
                return 0;
            }

            protected function getPath(): array
            {
                return [149, 151];
            }
        };

        $this->assertSame(LSOLearningMapPosition::SIT_DEADEND, $position->exposeSituation($items, $current));
    }

    /**
     * @return \LSLearnerItem&MockObject
     */
    private function mockItem(int $ref_id): \LSLearnerItem
    {
        $item = $this->createMock(\LSLearnerItem::class);
        $item->method('getRefId')->willReturn($ref_id);

        return $item;
    }

    /**
     * @param array<int, int> $obj_ids_by_ref_id
     * @param int[] $visited_obj_ids
     */
    private function createPosition(
        LSNavigator $navigator,
        array $obj_ids_by_ref_id,
        array $visited_obj_ids
    ): LSOLearningMapPosition {
        $item_path = $this->createStub(LSOItemPath::class);
        $boundaries = $this->createStub(LSOAdaptiveBoundaries::class);

        return new class ($navigator, $item_path, $boundaries, $obj_ids_by_ref_id, $visited_obj_ids) extends LSOLearningMapPosition {
            /**
             * @param array<int, int> $obj_ids_by_ref_id
             * @param int[] $visited_obj_ids
             */
            public function __construct(
                LSNavigator $navigator,
                LSOItemPath $item_path,
                LSOAdaptiveBoundaries $boundaries,
                private array $obj_ids_by_ref_id,
                private array $visited_obj_ids
            ) {
                parent::__construct($navigator, $item_path, $boundaries, 421, 6);
            }

            protected function lookupObjId(int $ref_id): int
            {
                return $this->obj_ids_by_ref_id[$ref_id] ?? 0;
            }

            public function hasVisited(int $obj_id): bool
            {
                return in_array($obj_id, $this->visited_obj_ids, true);
            }
        };
    }
}
