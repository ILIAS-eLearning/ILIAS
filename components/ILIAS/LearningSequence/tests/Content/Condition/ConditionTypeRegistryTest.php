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
use ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition;
use ILIAS\LearningSequence\Content\Condition\ConditionTypeRegistry;
use ILIAS\LearningSequence\Content\Condition\InputCondition\InputConditionInterface;
use ILIAS\LearningSequence\Content\Condition\InputCondition\PointsInputCondition\PointsInputCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\SubsetInputCondition\SubsetInputCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\AlwaysOutputCondition\AlwaysOutputCondition;
use ILIAS\LearningSequence\Content\Condition\OutputCondition\PointsOutputCondition\PointsOutputCondition;
use PHPUnit\Framework\TestCase;

class ConditionTypeRegistryTest extends TestCase
{
    private function getRegistry(): ConditionTypeRegistry
    {
        return new ConditionTypeRegistry([
            new ConditionTypeDefinition(PointsInputCondition::class),
            new ConditionTypeDefinition(AlwaysOutputCondition::class),
            new ConditionTypeDefinition(SubsetInputCondition::class),
            new ConditionTypeDefinition(PointsOutputCondition::class),
            new ConditionTypeDefinition(PointsInputCondition::class),
        ]);
    }

    public function testSplitsContributedTypesIntoInputAndOutputConditions(): void
    {
        $registry = $this->getRegistry();

        $this->assertSame(
            [PointsInputCondition::class, SubsetInputCondition::class],
            $registry->getAllInputConditions()
        );
        $this->assertSame(
            [AlwaysOutputCondition::class, PointsOutputCondition::class],
            $registry->getAllOutputConditions()
        );
    }

    public function testListsEveryContributedTypeOnce(): void
    {
        $this->assertSame(
            [
                PointsInputCondition::class,
                AlwaysOutputCondition::class,
                SubsetInputCondition::class,
                PointsOutputCondition::class,
            ],
            $this->getRegistry()->getAllConditions()
        );
    }

    public function testResolvesConditionClassByName(): void
    {
        $registry = $this->getRegistry();

        $this->assertSame(PointsOutputCondition::class, $registry->getConditionByName('PointsOutput'));
        $this->assertNull($registry->getConditionByName('LogicGateInputAware'));
    }

    public function testWithoutContributionsNoConditionsAreKnown(): void
    {
        $registry = new ConditionTypeRegistry([]);

        $this->assertSame([], $registry->getAllConditions());
        $this->assertSame([], $registry->getAllInputConditions());
        $this->assertSame([], $registry->getAllOutputConditions());
    }

    public static function invalidContributions(): array
    {
        return [
            'missing class' => ['ILIAS\\LearningSequence\\DoesNotExistCondition', 'does not exist'],
            'no condition' => [stdClass::class, 'does not extend'],
            'abstract condition' => [ConditionTypeRegistryTestAbstractCondition::class, 'cannot be instantiated'],
            'neither input nor output' => [
                ConditionTypeRegistryTestUndirectedCondition::class,
                'neither an input nor an output condition'
            ],
        ];
    }

    #[PHPUnit\Framework\Attributes\DataProvider('invalidContributions')]
    public function testInvalidContributionIsRejected(string $class, string $message): void
    {
        $registry = new ConditionTypeRegistry([new ConditionTypeDefinition($class)]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($message);
        $registry->getAllConditions();
    }

    public function testContributionsSharingTheNameAreRejected(): void
    {
        $alias = 'ILIAS\\LearningSequence\\Tests\\Fixture\\PointsInputCondition';
        if (!class_exists($alias, false)) {
            class_alias(PointsInputCondition::class, $alias);
        }
        $registry = new ConditionTypeRegistry([
            new ConditionTypeDefinition(PointsInputCondition::class),
            new ConditionTypeDefinition($alias),
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("share the name 'PointsInput'");
        $registry->getAllInputConditions();
    }
}

/**
 * Fixture: a condition which cannot be instantiated.
 */
abstract class ConditionTypeRegistryTestAbstractCondition extends AbstractCondition implements InputConditionInterface
{
}

/**
 * Fixture: a condition which is neither an input nor an output condition.
 */
class ConditionTypeRegistryTestUndirectedCondition extends AbstractCondition
{
    public function check(): bool
    {
        return true;
    }
}
