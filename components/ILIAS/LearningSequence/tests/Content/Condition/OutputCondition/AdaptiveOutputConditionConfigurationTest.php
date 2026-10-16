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

use ILIAS\LearningSequence\Content\Condition\OutputCondition\AccruedValueOutputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\AlwaysOutputCondition\AlwaysOutputCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\LearningProgressOutputConditions\LearningProgressOutputAwareCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\PointsOutputCondition\PointsOutputCondition;
use PHPUnit\Framework\TestCase;

class AdaptiveOutputConditionConfigurationTest extends TestCase
{
    public function testAlwaysOutputIsAlwaysFulfilledAndUniquePerObject(): void
    {
        $condition = $this->newCondition(AlwaysOutputCondition::class);

        $this->assertTrue($condition->check());
        $this->assertFalse($condition->allowMultipleConditionsOfSameType());
        $this->assertSame([], AlwaysOutputCondition::migrate());
    }

    public function testPointsOutputNormalizesFormDataAndExposesAccumulatedValue(): void
    {
        $condition = $this->newCondition(PointsOutputCondition::class);

        $condition->applyAdditionalFormData(['7']);

        $this->assertInstanceOf(AccruedValueOutputConditionInterface::class, $condition);
        $this->assertSame('points', $condition->getAccumulationIdentifier());
        $this->assertSame(7, $condition->getPoints());
        $this->assertSame(7, $condition->getAccumulatedValue());
    }

    public function testPointsOutputWithoutConditionIdDefaultsToZeroPoints(): void
    {
        $condition = $this->newCondition(PointsOutputCondition::class);

        $this->assertSame(0, $condition->getPoints());
        $this->assertSame(0, $condition->getAccumulatedValue());
    }

    public function testPointsOutputSetterUpdatesAccumulatedValue(): void
    {
        $condition = $this->newCondition(PointsOutputCondition::class);

        $condition->setPoints(13);

        $this->assertSame(13, $condition->getPoints());
        $this->assertSame(13, $condition->getAccumulatedValue());
    }

    public function testLearningProgressOutputSupportsAllAdaptiveSubtypes(): void
    {
        $condition = $this->newCondition(LearningProgressOutputAwareCondition::class);

        $this->assertSame(
            ['not_attempted', 'in_progress', 'completed', 'failed'],
            $condition->getSupportedSubtypes()
        );

        foreach ($condition->getSupportedSubtypes() as $subtype) {
            $condition->setSubtype($subtype);
            $this->assertSame($subtype, $condition->getSubtype());
        }
    }

    public function testAdaptiveOutputConditionMigrationTablesAreDeclared(): void
    {
        $this->assertSame('lso_c_points_output', PointsOutputCondition::migrate()[0]->tableName);
        $this->assertSame(
            'lso_c_learning_progress_output',
            LearningProgressOutputAwareCondition::migrate()[0]->tableName
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $class_name
     * @return T
     */
    private function newCondition(string $class_name): object
    {
        return (new ReflectionClass($class_name))->newInstanceWithoutConstructor();
    }
}
