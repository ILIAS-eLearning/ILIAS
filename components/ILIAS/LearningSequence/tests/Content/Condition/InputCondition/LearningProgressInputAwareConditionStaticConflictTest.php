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

use ILIAS\LearningSequence\Content\Condition\InputCondition\LearningProgressInputConditions\LearningProgressInputAwareCondition;
use PHPUnit\Framework\TestCase;

class LearningProgressInputAwareConditionStaticConflictTest extends TestCase
{
    public function testMissingReferencedObjectIsReportedAsStaticConflict(): void
    {
        $condition = (new ReflectionClass(LearningProgressInputAwareCondition::class))
            ->newInstanceWithoutConstructor();
        $condition->setObjRefId(200);
        $condition->setConditionTargetRefId(999);

        $this->assertTrue($condition->hasStaticInputConfigurationConflict([
            'valid_ref_ids' => [151, 152],
        ]));
        $this->assertSame(
            [200],
            $condition->getStaticInputConfigurationIssues(['valid_ref_ids' => [151, 152]])[0]->affected_ref_ids
        );
        $this->assertSame(
            [999],
            $condition->getStaticInputConfigurationIssues(['valid_ref_ids' => [151, 152]])[0]
                ->details[0]
                ->properties_by_language_var['lso_static_input_configuration_referenced_objects']
        );
    }

    public function testExistingReferencedObjectIsNotReportedAsConflict(): void
    {
        $condition = (new ReflectionClass(LearningProgressInputAwareCondition::class))
            ->newInstanceWithoutConstructor();
        $condition->setConditionTargetRefId(151);

        $this->assertFalse($condition->hasStaticInputConfigurationConflict([
            'valid_ref_ids' => [151, 152],
        ]));
    }
}
