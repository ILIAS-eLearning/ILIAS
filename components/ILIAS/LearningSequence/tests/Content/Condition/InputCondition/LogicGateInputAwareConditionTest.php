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
use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ilObjLearningSequenceConditionDiscover;
use ILIAS\LearningSequence\Content\Condition\InputCondition\LogicGateInputAwareCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\OutputConditionInterface;
use PHPUnit\Framework\TestCase;

class LogicGateInputAwareConditionTest extends TestCase
{
    public function testOrGateEvaluatesConfiguredItemsWithoutWhitespaceDependency(): void
    {
        $condition = (new ReflectionClass(LogicGateInputAwareCondition::class))
            ->newInstanceWithoutConstructor();
        $condition->setSubtype('logic_gate_or');
        $condition->setItems([151, 152]);

        $this->injectDependencies(
            $condition,
            new class () extends ilObjLearningSequenceConditionDiscover {
                public function getAllConditionIdsForItem(int $item_ref_id): array
                {
                    return match ($item_ref_id) {
                        151 => [1],
                        152 => [2],
                        default => [],
                    };
                }
            },
            new class () extends ConditionFactory {
                public function __construct()
                {
                }

                public function getConditionInstanceById(int $condition_id): AbstractCondition
                {
                    return new class ($condition_id === 1) extends AbstractCondition implements OutputConditionInterface {
                        public function __construct(private bool $check_result)
                        {
                        }

                        public function check(): bool
                        {
                            return $this->check_result;
                        }
                    };
                }
            }
        );

        $this->assertTrue($condition->check());
        $this->assertSame([151, 152], $condition->getNavigationSourceRefIds());
    }

    public function testNotGateExposesStaticConflictConstraints(): void
    {
        $condition = (new ReflectionClass(LogicGateInputAwareCondition::class))
            ->newInstanceWithoutConstructor();
        $condition->setSubtype('logic_gate_not');
        $condition->setItems([151, 152]);

        $constraints = $condition->getStaticInputConditionConstraints();

        $this->assertCount(1, $constraints);
        $this->assertSame('none_completed', $constraints[0]['kind']);
        $this->assertSame([151, 152], $constraints[0]['ref_ids']);
    }

    public function testNotGateOnStartObjectIsReportedAsStaticConflict(): void
    {
        $condition = (new ReflectionClass(LogicGateInputAwareCondition::class))
            ->newInstanceWithoutConstructor();
        $condition->setObjRefId(200);
        $condition->setSubtype('logic_gate_not');
        $condition->setItems([151, 152]);

        $this->assertTrue($condition->hasStaticInputConfigurationConflict(['start_ref_id' => 151]));
        $this->assertFalse($condition->hasStaticInputConfigurationConflict(['start_ref_id' => 999]));
        $this->assertSame(
            [200],
            $condition->getStaticInputConfigurationIssues(['start_ref_id' => 151])[0]->affected_ref_ids
        );
        $this->assertSame(
            'lso_logic_gate_input_not_start_reference',
            $condition->getStaticInputConfigurationIssues(['start_ref_id' => 151])[0]->details[0]->title_language_var
        );
    }

    public function testMissingReferencedObjectIsReportedAsStaticConflict(): void
    {
        $condition = (new ReflectionClass(LogicGateInputAwareCondition::class))
            ->newInstanceWithoutConstructor();
        $condition->setSubtype('logic_gate_and');
        $condition->setItems([151, 999]);

        $this->assertTrue($condition->hasStaticInputConfigurationConflict([
            'valid_ref_ids' => [151, 152],
        ]));
    }

    private function injectDependencies(
        LogicGateInputAwareCondition $condition,
        ilObjLearningSequenceConditionDiscover $discoverer,
        ConditionFactory $factory
    ): void {
        $discover_property = new ReflectionProperty($condition, 'discover');
        $discover_property->setValue($condition, $discoverer);

        $factory_property = new ReflectionProperty($condition, 'condition_factory');
        $factory_property->setValue($condition, $factory);
    }
}
