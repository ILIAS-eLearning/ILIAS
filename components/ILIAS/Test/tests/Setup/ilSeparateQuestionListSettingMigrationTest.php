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

namespace Setup;

use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ILIAS\Setup\Environment;
use ILIAS\Test\Setup\ilSeparateQuestionListSettingMigration;
use ilTestBaseTestCase;

class ilSeparateQuestionListSettingMigrationTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilSeparateQuestionListSettingMigration = new ilSeparateQuestionListSettingMigration();
        $this->assertInstanceOf(ilSeparateQuestionListSettingMigration::class, $ilSeparateQuestionListSettingMigration);
    }

    public function testGetLabel(): void
    {
        $ilSeparateQuestionListSettingMigration = new ilSeparateQuestionListSettingMigration();
        $this->assertEquals('Update QuestionList Settings', $ilSeparateQuestionListSettingMigration->getLabel());
    }

    public function testGetDefaultAmountOfStepsPerRun(): void
    {
        $ilSeparateQuestionListSettingMigration = new ilSeparateQuestionListSettingMigration();
        $this->assertEquals(1, $ilSeparateQuestionListSettingMigration->getDefaultAmountOfStepsPerRun());
    }

    public function testGetPreconditions(): void
    {
        $ilSeparateQuestionListSettingMigration = new ilSeparateQuestionListSettingMigration();
        $result = $ilSeparateQuestionListSettingMigration->getPreconditions($this->createMock(Environment::class));
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertInstanceOf(ilDatabaseInitializedObjective::class, $result[0]);
        $this->assertInstanceOf(ilDatabaseUpdatedObjective::class, $result[1]);
    }
}