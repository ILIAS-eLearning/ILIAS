<?php

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

    public function testPrepare(): void
    {
        $this->markTestSkipped();
    }

    public function testStep(): void
    {
        $this->markTestSkipped();
    }

    public function testGetRemainingAmountOfSteps(): void
    {
        $this->markTestSkipped();
    }
}