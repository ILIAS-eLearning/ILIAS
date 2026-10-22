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

use ILIAS\LearningSequence\Content\Condition\ConditionFactory;
use ILIAS\LearningSequence\Content\Condition\ConditionTypeDefinition;
use ILIAS\LearningSequence\Content\Condition\ConditionTypeRegistry;
use ILIAS\LearningSequence\Content\Condition\InputCondition\PointsInputCondition\PointsInputCondition;
use ILIAS\LearningSequence\Content\Condition\UnknownConditionTypeException;
use PHPUnit\Framework\TestCase;

class ConditionFactoryTest extends TestCase
{
    private function getFactory(?array $stored_condition = null): ConditionFactory
    {
        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturn($this->createStub(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturn($stored_condition);

        return new ConditionFactory(
            new ConditionTypeRegistry([new ConditionTypeDefinition(PointsInputCondition::class)]),
            $db
        );
    }

    public function testUnknownConditionNameIsReported(): void
    {
        try {
            $this->getFactory()->getConditionInstanceByName('GhostInput');
            $this->fail('UnknownConditionTypeException expected.');
        } catch (UnknownConditionTypeException $e) {
            $this->assertSame('GhostInput', $e->getConditionName());
        }
    }

    public function testStoredConditionOfUnknownTypeIsReported(): void
    {
        $factory = $this->getFactory([
            'condition_id' => 7,
            'lso_ref_id' => 1,
            'obj_ref_id' => 2,
            'type_id' => 99,
            'condition_name' => 'GhostInput',
        ]);

        $this->expectException(UnknownConditionTypeException::class);
        $factory->getConditionInstanceById(7);
    }

    public function testUnknownConditionTypeIsAnIlException(): void
    {
        $this->assertInstanceOf(ilException::class, new UnknownConditionTypeException('GhostInput'));
    }
}
