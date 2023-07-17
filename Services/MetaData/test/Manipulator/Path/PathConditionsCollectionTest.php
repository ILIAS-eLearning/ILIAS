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

use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Paths\NullPath;
use ILIAS\MetaData\Paths\NullFactory;
use ILIAS\MetaData\Paths\Steps\NullStep;
use ILIAS\MetaData\Paths\Steps\StepInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Paths\PathInterface;
use PHPUnit\Framework\TestCase;

class PathConditionsCollectionTest extends TestCase
{
    protected function getPartiallyMockedPathConditionsCollection(PathInterface $path): PathConditionsCollection
    {
        return new class (new NullFactory(), $path, $this) extends PathConditionsCollection {
            public function __construct(
                PathFactory $path_factory,
                PathInterface $path,
                protected PathConditionsCollectionTest $path_conditions_collection_test
            ) {
                parent::__construct($path_factory, $path);
            }

            protected function buildPathFromSteps(array $steps, bool $is_relative, bool $leads_to_exactly_one): PathInterface
            {
                return $this->path_conditions_collection_test->getMockPath($steps, $is_relative, $leads_to_exactly_one);
            }
        };
    }

    public function getMockStep(string|StepToken $name): StepInterface
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
    public function getMockPath(
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

            public function toString(): string
            {
                $step_string = '';
                foreach ($this->steps as $step) {
                    $step_string .= '/' . ($step->name() instanceof StepToken ? $step->name()->value : $step->name());
                }
                return $step_string;
            }
        };
    }

    public function testPathWithoutConditions_001(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('general'),
                $this->getMockStep('keyword'),
                $this->getMockStep('string')
            ],
            false,
            false
        ));
        $this->assertSame(
            '/general/keyword/string',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('general')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('keyword')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('string')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithoutConditions_002(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [],
            false,
            false
        ));
        $this->assertSame(
            '',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithNestedConditions_001(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('start'),
                $this->getMockStep('step_1'),
                $this->getMockStep('condition'),
                $this->getMockStep('nested_condition'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('target')
            ],
            false,
            false
        ));
        $this->assertSame(
            '/start/step_1/target',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('start')->toString(),
        );
        $this->assertSame(
            '/condition/nested_condition/^/^',
            $path_conditions->getConditionPathByStepName('step_1')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('target')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithNestedConditions_002(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('start'),
                $this->getMockStep('step_1'),
                $this->getMockStep('condition'),
                $this->getMockStep('nested_condition'),
                $this->getMockStep('nested_nested_condition'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('target')
            ],
            false,
            false
        ));
        $this->assertSame(
            '/start/step_1/target',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('start')->toString(),
        );
        $this->assertSame(
            '/condition/nested_condition/nested_nested_condition/^/^/^',
            $path_conditions->getConditionPathByStepName('step_1')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('target')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithChainedConditions_001(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('start'),
                $this->getMockStep('step_1'),
                $this->getMockStep('condition'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('target')
            ],
            false,
            false
        ));
        $this->assertSame(
            '/start/step_1/target',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('start')->toString(),
        );
        $this->assertSame(
            '/condition/^',
            $path_conditions->getConditionPathByStepName('step_1')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('target')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithChainedConditions_002(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('start'),
                $this->getMockStep('step_1'),
                $this->getMockStep('condition_1'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('step_2'),
                $this->getMockStep('condition_2'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('condition_3'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('target')
            ],
            false,
            false
        ));
        $this->assertSame(
            '/start/step_1/step_2/target',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('start')->toString(),
        );
        $this->assertSame(
            '/condition_1/^',
            $path_conditions->getConditionPathByStepName('step_1')->toString(),
        );
        $this->assertSame(
            '/condition_2/^/condition_3/^',
            $path_conditions->getConditionPathByStepName('step_2')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('target')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithNestedAndChainedConditions_001(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('start'),
                $this->getMockStep('condition_1'),
                $this->getMockStep('nested_condition_1'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('step_1'),
                $this->getMockStep('condition_2'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('step_2'),
                $this->getMockStep('condition_3'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('condition_4'),
                $this->getMockStep('nested_condition_4'),
                $this->getMockStep('nested_nested_condition_4'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('target')
            ],
            false,
            false
        ));
        $this->assertSame(
            '/start/step_1/step_2/target',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '/condition_1/nested_condition_1/^/^',
            $path_conditions->getConditionPathByStepName('start')->toString(),
        );
        $this->assertSame(
            '/condition_2/^',
            $path_conditions->getConditionPathByStepName('step_1')->toString(),
        );
        $this->assertSame(
            '/condition_3/^/condition_4/nested_condition_4/nested_nested_condition_4/^/^/^',
            $path_conditions->getConditionPathByStepName('step_2')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('target')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }

    public function testPathWithNestedAndChainedConditions_002(): void
    {
        $path_conditions = $this->getPartiallyMockedPathConditionsCollection($this->getMockPath(
            [
                $this->getMockStep('start'),
                $this->getMockStep('condition_1'),
                $this->getMockStep('nested_condition_1'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('step_1'),
                $this->getMockStep('condition_2'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('step_2'),
                $this->getMockStep('condition_3'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('condition_4'),
                $this->getMockStep('nested_condition_4'),
                $this->getMockStep('nested_nested_condition_4'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep('target'),
                $this->getMockStep('condition_5'),
                $this->getMockStep('nested_condition_5'),
                $this->getMockStep(StepToken::SUPER),
                $this->getMockStep(StepToken::SUPER)
            ],
            false,
            false
        ));
        $this->assertSame(
            '/start/step_1/step_2/target',
            $path_conditions->getPathWithoutConditions()->toString(),
        );
        $this->assertSame(
            '/condition_1/nested_condition_1/^/^',
            $path_conditions->getConditionPathByStepName('start')->toString(),
        );
        $this->assertSame(
            '/condition_2/^',
            $path_conditions->getConditionPathByStepName('step_1')->toString(),
        );
        $this->assertSame(
            '/condition_3/^/condition_4/nested_condition_4/nested_nested_condition_4/^/^/^',
            $path_conditions->getConditionPathByStepName('step_2')->toString(),
        );
        $this->assertSame(
            '/condition_5/nested_condition_5/^/^',
            $path_conditions->getConditionPathByStepName('target')->toString(),
        );
        $this->assertSame(
            '',
            $path_conditions->getConditionPathByStepName('not_a_step')->toString(),
        );
    }
}
