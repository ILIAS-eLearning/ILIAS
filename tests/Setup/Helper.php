<?php declare(strict_types=1);

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
 
namespace ILIAS\Tests\Setup;

use ILIAS\Setup;
use ILIAS\UI\Component\Input\Field\Input as Input;

trait Helper
{
    protected function newAgent() : Setup\Agent
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

    protected function newObjective() : Setup\Objective
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

    protected function newInput() : Input
    {
        static $no = 0;

        $input = $this
            ->getMockBuilder(Input::class)
            ->onlyMethods([])
            ->setMockClassName("Mock_InputNo" . ($no++))
            ->getMock();

        return $input;
    }

    protected function newConfig() : Setup\Config
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
