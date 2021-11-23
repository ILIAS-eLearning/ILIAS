<?php declare(strict_types=1);

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use ILIAS\UI\Component\Input\Field\Input as Input;

trait Helper
{
    protected function newAgent()
    {
        static $no = 0;

        $consumer = $this
            ->getMockBuilder(Setup\Agent::class)
            ->onlyMethods(["hasConfig", "getArrayToConfigTransformation", "getInstallObjective", "getUpdateObjective", "getBuildArtifactObjective", "getStatusObjective", "getMigrations", "getNamedObjectives"])
            ->setMockClassName("Mock_AgentNo" . ($no++))
            ->getMock();

        return $consumer;
    }

    protected function newObjectiveConstructor() : Setup\ObjectiveConstructor
    {
        static $no = 0;
        return new Setup\ObjectiveConstructor("named-objective-" . ($no++), static function () {
            return self::newObjective();
        });
    }

    protected function newObjective()
    {
        static $no = 0;

        $goal = $this
            ->getMockBuilder(Setup\Objective::class)
            ->onlyMethods(["getHash", "getLabel", "isNotable", "getPreconditions", "achieve", "isApplicable"])
            ->setMockClassName("Mock_ObjectiveNo" . ($no++))
            ->getMock();

        $goal
            ->method("getHash")
            ->willReturn("" . $no);

        return $goal;
    }

    protected function newInput()
    {
        static $no = 0;

        $input = $this
            ->getMockBuilder(Input::class)
            ->onlyMethods([])
            ->setMockClassName("Mock_InputNo" . ($no++))
            ->getMock();

        return $input;
    }

    protected function newConfig()
    {
        static $no = 0;

        $config = $this
            ->getMockBuilder(Setup\Config::class)
            ->onlyMethods([])
            ->setMockClassName("Mock_ConfigNo" . ($no++))
            ->getMock();

        return $config;
    }
}
