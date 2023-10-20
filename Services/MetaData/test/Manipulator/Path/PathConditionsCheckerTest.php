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

namespace ILIAS\MetaData\Manipulator\Path;

use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Elements\NullElement;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorInterface;
use ILIAS\MetaData\Paths\Navigator\NullNavigator;
use ILIAS\MetaData\Paths\Navigator\NullNavigatorFactory;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\Steps\NullStep;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use PHPUnit\Framework\TestCase;

class PathConditionsCheckerTest extends TestCase
{
    protected function getElementMock(): ElementInterface
    {
        return new NullElement();
    }

    public function getStepMock(string|StepToken $name): StepInterface
    {
        return new class ($name) extends NullStep {
            public function __construct(protected string|StepToken $name)
            {
            }

            public function name(): string|StepToken
            {
                return $this->name;
            }
        };
    }

    /**
     * @param StepInterface[] $steps
     */
    public function getPathMock(
        array $steps,
        bool $is_relative,
        bool $leads_exactly_to_one
    ): PathInterface {
        return new class ($steps, $is_relative, $leads_exactly_to_one) extends NullPath {
            /**
             * @param StepInterface[] $steps
             */
            public function __construct(
                protected array $steps,
                protected bool $is_relative,
                protected bool $leads_exactly_to_one
            ) {
            }

            public function isRelative(): bool
            {
                return $this->is_relative;
            }

            public function leadsToExactlyOneElement(): bool
            {
                return $this->leads_exactly_to_one;
            }

            public function steps(): \Generator
            {
                yield from $this->steps;
            }
        };
    }

    /**
     * @param PathInterface[] $step_conditions_dict
     */
    protected function getPathConditionsCollectionMock(
        PathInterface $path_without_conditions,
        array $step_conditions_dict
    ): PathConditionsCollectionInterface {
        return new class ($path_without_conditions, $step_conditions_dict) extends NullPathConditionsCollection {
            /**
             * @param PathInterface[] $conditions
             */
            public function __construct(
                protected PathInterface $path_without_conditions,
                protected array $conditions
            ) {
            }

            public function getPathWithoutConditions(): PathInterface
            {
                return $this->path_without_conditions;
            }

            public function getConditionPathByStepName(string $name): PathInterface
            {
                if (!key_exists($name, $this->conditions)) {
                    return new NullPath();
                }
                return $this->conditions[$name];
            }
        };
    }

    /**
     * @param bool[] $steps_has_elements
     */
    public function getNavigatorMock(
        array $steps_has_elements
    ): NavigatorInterface {
        return new class ($steps_has_elements) extends NullNavigator {
            protected int $index;
            /**
             * @param bool[] $step_has_elements
             */
            public function __construct(protected array $step_has_elements)
            {
                $this->index = -1;
            }

            public function nextStep(): ?NavigatorInterface
            {
                $clone = clone $this;
                $clone->index++;
                if ($clone->index >= count($this->step_has_elements)) {
                    return null;
                }
                return $clone;
            }

            public function previousStep(): ?NavigatorInterface
            {
                $clone = clone $this;
                $clone->index--;
                if ($clone->index < 0) {
                    return null;
                }
                return $clone;
            }

            public function hasNextStep(): bool
            {
                return $this->index < (count($this->step_has_elements) - 1);
            }

            public function hasPreviousStep(): bool
            {
                return $this->index > 0;
            }

            public function hasElements(): bool
            {
                return $this->index === -1 || $this->step_has_elements[$this->index];
            }
        };
    }

    protected function getNavigatorFactoryMock(): NavigatorFactoryInterface
    {
        return new class ($this) extends NullNavigatorFactory {
            public function __construct(protected PathConditionsCheckerTest $test)
            {
            }

            /**
             * @return bool[]
             */
            protected function stepsToBoolArray(StepInterface ...$steps): array
            {
                $has_elements = [];
                foreach ($steps as $step) {
                    $step_str = $step->name() instanceof StepToken ? $step->name()->value : $step->name();
                    $has_elements[] = str_ends_with($step_str, 'has_elements');
                }
                return $has_elements;
            }

            public function navigator(PathInterface $path, ElementInterface $start_element): NavigatorInterface
            {
                return $this->test->getNavigatorMock($this->stepsToBoolArray(...$path->steps()));
            }
        };
    }

    public function testIsPathConditionMet_001(): void
    {
        $path_without_conditions = $this->getPathMock(
            [
                $this->getStepMock('start'),
                $this->getStepMock('step1'),
                $this->getStepMock('step2'),
                $this->getStepMock('end'),
            ],
            false,
            false
        );

        $path_checker = new PathConditionsChecker(
            $this->getPathConditionsCollectionMock(
                $path_without_conditions,
                []
            ),
            $this->getNavigatorFactoryMock()
        );

        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('start'), $this->getElementMock())
        );
        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('step1'), $this->getElementMock())
        );
        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('step2'), $this->getElementMock())
        );
        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('end'), $this->getElementMock())
        );
        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('not_a_step'), $this->getElementMock())
        );
    }

    public function testIsPathConditionMet_002(): void
    {
        $path_without_conditions = $this->getPathMock(
            [
                $this->getStepMock('start'),
                $this->getStepMock('step1'),
                $this->getStepMock('step2'),
                $this->getStepMock('end'),
            ],
            false,
            false
        );

        $path_conditions = $this->getPathConditionsCollectionMock(
            $path_without_conditions,
            [
                'start' => $this->getPathMock(
                    [
                        $this->getStepMock('has_elements')
                    ],
                    true,
                    false
                ),
                'step1' => $this->getPathMock(
                    [
                        $this->getStepMock('has_elements'),
                        $this->getStepMock('has_elements')
                    ],
                    true,
                    false
                ),
                'step2' => $this->getPathMock(
                    [
                        $this->getStepMock('has_elements'),
                        $this->getStepMock('no_elements')
                    ],
                    true,
                    false
                ),
                'end' => $this->getPathMock(
                    [
                        $this->getStepMock('no_elements')
                    ],
                    true,
                    false
                )
            ]
        );

        $path_checker = new PathConditionsChecker(
            $path_conditions,
            $this->getNavigatorFactoryMock()
        );

        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('start'), $this->getElementMock())
        );
        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('step1'), $this->getElementMock())
        );
        $this->assertFalse(
            $path_checker->isPathConditionMet($this->getStepMock('step2'), $this->getElementMock())
        );
        $this->assertFalse(
            $path_checker->isPathConditionMet($this->getStepMock('end'), $this->getElementMock())
        );
        $this->assertTrue(
            $path_checker->isPathConditionMet($this->getStepMock('not_a_step'), $this->getElementMock())
        );
    }
}
