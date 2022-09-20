<?php

declare(strict_types=1);

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

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use Symfony\Component\Console\Input\InputInterface;
use ILIAS\Setup\Config;
use ILIAS\Setup\Agent;
use Symfony\Component\Console\Output\StreamOutput;
use ilUICoreSetupAgent;
use ilLanguage;
use ilSetupAgent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\ObjectiveCollection;
use LogicException;

class TestConfig implements Config
{
    public function getConfig(string $name)
    {
        return ["a" => "b"];
    }

    public function getKeys()
    {
        return ["a"];
    }
}

class TestObject extends Setup\CLI\AchieveCommand
{
    public function readAgentConfig(Agent $agent, InputInterface $input): ?Config
    {
        return new Setup\ConfigCollection(["Test" => new TestConfig()]);
    }
}

class AchieveCommandTest extends TestCase
{
    /**
     * @var Setup\CLI\ConfigReader|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config_reader;

    /**
     * @var Setup\AgentFinder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $agent_finder;

    /**
     * @var Refinery
     */
    protected $refinery;

    /**
     * @var Setup\CLI\AchieveCommand
     */
    protected $command;

    public function setUp(): void
    {
        $this->config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $this->agent_finder = $this->createMock(Setup\AgentFinder::class);
        $this->refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));
        $this->command = new Setup\CLI\AchieveCommand($this->agent_finder, $this->config_reader, [], $this->refinery);
    }

    public function testBasicFunctionality(): void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $agent = $this->createMock(Setup\AgentCollection::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $command = new Setup\CLI\AchieveCommand($agent_finder, $config_reader, [], $refinery);

        $tester = new CommandTester($command);

        $config = $this->createMock(Setup\ConfigCollection::class);
        $config_file = "config_file";
        $config_file_content = ["config_file"];
        $objective_name = "my.objective";

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $config_reader
            ->expects($this->once())
            ->method("readConfigFile")
            ->with($config_file)
            ->willReturn($config_file_content);

        $agent_finder
            ->expects($this->once())
            ->method("getAgents")
            ->with()
            ->willReturn($agent);

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(true);

        $agent
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($config_file_content, $config) {
                $this->assertEquals($v, $config_file_content);
                return $config;
            }));

        $namedObjectives = [
            "my.objective" => new Setup\ObjectiveConstructor(
                "My Objective",
                static function () use ($objective): Setup\Objective {
                    return new Setup\ObjectiveCollection(
                        "My Objective",
                        false,
                        $objective
                    );
                }
            )
        ];

        $agent
            ->expects($this->once())
            ->method("getNamedObjectives")
            ->willReturn($namedObjectives);

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $objective
            ->method("isApplicable")
            ->willReturn(true);

        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env);

        $tester->execute([
            "config" => $config_file,
            "objective" => $objective_name
        ]);
    }

    public function testAchieveObjective(): void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $agent = $this->createMock(Setup\AgentCollection::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $command = new Setup\CLI\AchieveCommand($agent_finder, $config_reader, [], $refinery);

        $tester = new CommandTester($command);

        $config = $this->createMock(Setup\ConfigCollection::class);
        $config_file = "config_file";
        $config_file_content = ["config_file"];
        $objective_name = "my.objective";

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $config_reader
            ->expects($this->once())
            ->method("readConfigFile")
            ->with($config_file)
            ->willReturn($config_file_content);

        $agent_finder
            ->expects($this->once())
            ->method("getAgents")
            ->with()
            ->willReturn($agent);

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(true);

        $agent
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($config_file_content, $config) {
                $this->assertEquals($v, $config_file_content);
                return $config;
            }));


        $seen_config = null;
        $agent
            ->expects($this->once())
            ->method("getNamedObjectives")
            ->will($this->returnCallback(function ($config) use (&$seen_config, $objective) {
                $seen_config = $config;
                return [
                    "my.objective" => new Setup\ObjectiveConstructor(
                        "My Objective",
                        static function () use ($objective): Setup\Objective {
                            return $objective;
                        }
                    )
                ];
            }));

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $objective
            ->method("isApplicable")
            ->willReturn(true);

        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env);

        $tester->execute([
            "config" => $config_file,
            "objective" => $objective_name
        ]);

        $this->assertSame($seen_config, $config);
    }
}
