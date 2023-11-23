<?php

namespace Setup;

use ilDatabaseUpdateStepsExecutedObjective;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Test\Setup\ilManScoringSettingsToOwnDbTableMigration;
use ILIAS\Test\Setup\ilRemoveDynamicTestsAndCorrespondingDataMigration;
use ILIAS\Test\Setup\ilSeparateQuestionListSettingMigration;
use ilTestBaseTestCase;
use ilTestSetupAgent;

class ilTestSetupAgentTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestSetupAgentTest = new ilTestSetupAgent($this->createMock(Refinery::class));
        $this->assertInstanceOf(ilTestSetupAgent::class, $ilTestSetupAgentTest);
    }

    public function testGetUpdateObjective(): void
    {
        $ilTestSetupAgentTest = new ilTestSetupAgent($this->createMock(Refinery::class));
        $this->assertInstanceOf(ilDatabaseUpdateStepsExecutedObjective::class, $ilTestSetupAgentTest->getUpdateObjective());
    }

    public function testGetStatusObjective(): void
    {
        $this->markTestSkipped();
    }

    public function testHasConfig(): void
    {
        $ilTestSetupAgentTest = new ilTestSetupAgent($this->createMock(Refinery::class));
        $this->assertFalse($ilTestSetupAgentTest->hasConfig());
    }

    public function testGetInstallObjective(): void
    {
        $ilTestSetupAgentTest = new ilTestSetupAgent($this->createMock(Refinery::class));
        $this->assertInstanceOf(NullObjective::class, $ilTestSetupAgentTest->getInstallObjective());
    }

    public function testGetBuildArtifactObjective(): void
    {
        $ilTestSetupAgentTest = new ilTestSetupAgent($this->createMock(Refinery::class));
        $this->assertInstanceOf(NullObjective::class, $ilTestSetupAgentTest->getBuildArtifactObjective());
    }

    public function testGetMigrations(): void
    {
        $ilTestSetupAgentTest = new ilTestSetupAgent($this->createMock(Refinery::class));
        $this->assertIsArray($ilTestSetupAgentTest->getMigrations());
        $this->assertNotEmpty($ilTestSetupAgentTest->getMigrations());
        $this->assertInstanceOf(ilManScoringSettingsToOwnDbTableMigration::class, $ilTestSetupAgentTest->getMigrations()[0]);
        $this->assertInstanceOf(ilRemoveDynamicTestsAndCorrespondingDataMigration::class, $ilTestSetupAgentTest->getMigrations()[1]);
        $this->assertInstanceOf(ilSeparateQuestionListSettingMigration::class, $ilTestSetupAgentTest->getMigrations()[2]);
    }
}