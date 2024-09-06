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

use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Test\Setup\TestSetupAgent;
use PHPUnit\Framework\MockObject\Exception;

class TestSetupAgentTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertInstanceOf(TestSetupAgent::class, $test_setup_agent);
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetUpdateObjective(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertInstanceOf(ObjectiveCollection::class, $test_setup_agent->getUpdateObjective());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetStatusObjective(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertInstanceOf(ObjectiveCollection::class, $test_setup_agent->getStatusObjective(
            $this->createMock(Storage::class)
        ));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testHasConfig(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertFalse($test_setup_agent->hasConfig());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetArrayToConfigTransformation(): void
    {
        $this->expectException(LogicException::class);
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $test_setup_agent->getArrayToConfigTransformation();
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetInstallObjective(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertInstanceOf(NullObjective::class, $test_setup_agent->getInstallObjective());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetBuildArtifactObjective(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertInstanceOf(NullObjective::class, $test_setup_agent->getBuildObjective());
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetMigrations(): void
    {
        $test_setup_agent = $this->createInstanceOf(TestSetupAgent::class);
        $this->assertIsArray($test_setup_agent->getMigrations());
    }
}
