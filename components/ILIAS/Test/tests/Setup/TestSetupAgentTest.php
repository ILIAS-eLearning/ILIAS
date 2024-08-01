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

namespace ILIAS\Test\test;

use ILIAS\Setup\Metrics\Storage;
use ILIAS\Test\Setup\TestSetupAgent;

use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\Objective\NullObjective;
use ilTestBaseTestCase;

class TestSetupAgentTest extends ilTestBaseTestCase
{
    private TestSetupAgent $testSetupAgent;
    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $this->testSetupAgent = new TestSetupAgent($DIC['refinery']);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestSetupAgent::class, $this->testSetupAgent);
    }

    public function testGetUpdateObjective(): void
    {
        $this->assertInstanceOf(ObjectiveCollection::class, $this->testSetupAgent->getUpdateObjective());
    }

    public function testGetStatusObjective(): void
    {
        $this->assertInstanceOf(ObjectiveCollection::class, $this->testSetupAgent->getStatusObjective(
            $this->createMock(Storage::class)
        ));
    }

    public function testHasConfig(): void
    {
        $this->assertFalse($this->testSetupAgent->hasConfig());
    }

    public function testGetArrayToConfigTransformation(): void
    {
        $this->expectException(\LogicException::class);
        $this->testSetupAgent->getArrayToConfigTransformation();
    }

    public function testGetInstallObjective(): void
    {
        $this->assertInstanceOf(NullObjective::class, $this->testSetupAgent->getInstallObjective());
    }

    public function testGetBuildArtifactObjective(): void
    {
        $this->assertInstanceOf(NullObjective::class, $this->testSetupAgent->getBuildObjective());
    }

    public function testGetMigrations(): void
    {
        $this->assertIsArray($this->testSetupAgent->getMigrations());
    }
}
