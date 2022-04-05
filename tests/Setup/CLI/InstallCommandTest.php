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
 
namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class InstallCommandTest extends TestCase
{
    public function testBasicFunctionality() : void
    {
        $this->basicFunctionality(false);
    }

    public function testBasicFunctionalityAlreadyAchieved() : void
    {
        $this->basicFunctionality(true);
    }

    public function basicFunctionality(bool $is_applicable) : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $agent = $this->createMock(Setup\AgentCollection::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $command = new Setup\CLI\InstallCommand($agent_finder, $config_reader, []);

        $tester = new CommandTester($command);

        $config = $this->createMock(Setup\ConfigCollection::class);
        $config_file = "config_file";
        $config_file_content = ["config_file"];

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $config_overwrites = [
            "a.b.c" => "foo",
            "d.e" => "bar",
        ];

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(true);

        $config_reader
            ->expects($this->once())
            ->method("readConfigFile")
            ->with($config_file, $config_overwrites)
            ->willReturn($config_file_content);

        $config
            ->expects($this->once())
            ->method("getConfig")
            ->with("common")
            ->willReturn(new class implements Setup\Config {
                public function getClientId() : string
                {
                    return "client_id";
                }
            });

        $agent_finder
            ->expects($this->once())
            ->method("getAgents")
            ->with()
            ->willReturn($agent);

        $agent
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($config_file_content, $config) {
                $this->assertEquals($v, $config_file_content);
                return $config;
            }));

        $agent
            ->expects($this->once())
            ->method("getInstallObjective")
            ->with($config)
            ->willReturn($objective);

        $agent
            ->expects($this->never())
            ->method("getBuildArtifactObjective")
            ->with()
            ->willReturn(new Setup\Objective\NullObjective());

        $agent
            ->expects($this->once())
            ->method("getUpdateObjective")
            ->with()
            ->willReturn(new Setup\Objective\NullObjective());

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $expects = $this->never();
        $return = false;

        if ($is_applicable) {
            $expects = $this->once();
            $return = true;
        }

        $objective
            ->expects($expects)
            ->method("achieve")
            ->willReturn($env);

        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn($return);
        
        $tester->execute([
            "config" => $config_file,
            "--config" => ["a.b.c=foo", "d.e=bar"]
        ]);
    }

    public function testPluginInstallation() : void
    {
        $agent = $this->createMock(Setup\AgentCollection::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $command = new Setup\CLI\InstallCommand($agent_finder, $config_reader, []);

        $tester = new CommandTester($command);

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $agent
            ->expects($this->once())
            ->method("hasConfig")
            ->willReturn(false);

        $agent_finder
            ->expects($this->once())
            ->method("getPluginAgent")
            ->with("test")
            ->willReturn($agent);

        $agent
            ->expects($this->once())
            ->method("getInstallObjective")
            ->with(null)
            ->willReturn($objective);

        $agent
            ->expects($this->once())
            ->method("getUpdateObjective")
            ->with()
            ->willReturn(new Setup\Objective\NullObjective());

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $objective
            ->expects($this->once())
            ->method("achieve")
            ->willReturn($env);

        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn(true);

        $tester->execute([
            "--plugin" => "test"
        ]);
    }
}
