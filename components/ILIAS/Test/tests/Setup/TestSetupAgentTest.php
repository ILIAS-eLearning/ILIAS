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
    private TestSetupAgent $testObj;
    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;
        $this->testObj = new TestSetupAgent($DIC['refinery']);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestSetupAgent::class, $this->testObj);
    }

    public function testGetUpdateObjective(): void
    {
        $this->assertInstanceOf(ObjectiveCollection::class, $this->testObj->getUpdateObjective());
    }

    public function testGetStatusObjective(): void
    {
        $this->assertInstanceOf(ObjectiveCollection::class, $this->testObj->getStatusObjective(
            $this->createMock(Storage::class)
        ));
    }

    public function testHasConfig(): void
    {
        $this->assertFalse($this->testObj->hasConfig());
    }

    public function testGetArrayToConfigTransformation(): void
    {
        $this->expectException(\LogicException::class);
        $this->testObj->getArrayToConfigTransformation();
    }

    public function testGetInstallObjective(): void
    {
        $this->assertInstanceOf(NullObjective::class, $this->testObj->getInstallObjective());
    }

    public function testGetBuildArtifactObjective(): void
    {
        $this->assertInstanceOf(NullObjective::class, $this->testObj->getBuildObjective());
    }

    public function testGetMigrations(): void
    {
        $this->assertIsArray($this->testObj->getMigrations());
    }
}
