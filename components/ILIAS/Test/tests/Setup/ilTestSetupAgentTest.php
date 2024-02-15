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
        $this->assertInstanceOf(NullObjective::class, $ilTestSetupAgentTest->getBuildObjective());
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
