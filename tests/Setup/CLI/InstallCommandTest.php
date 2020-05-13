<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class InstallCommandTest extends TestCase
{
    public function testBasicFunctionality()
    {
        $this->basicFunctionality(false);
    }

    public function testBasicFunctionalityAlreadyAchieved()
    {
        $this->basicFunctionality(true);
    }

    public function basicFunctionality(bool $is_applicable) : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $agent = $this->createMock(Setup\Agent::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $command = new Setup\CLI\InstallCommand(function () use ($agent) {
            return $agent;
        }, $config_reader, []);

        $tester = new CommandTester($command);

        $config = $this->createMock(Setup\Config::class);
        $config_file = "config_file";
        $config_file_content = ["config_file"];

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $config_overwrites = [
            "a.b.c" => "foo",
            "d.e" => "bar",
        ];

        $config_reader
            ->expects($this->once())
            ->method("readConfigFile")
            ->with($config_file, $config_overwrites)
            ->willReturn($config_file_content);

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
            ->with($config)
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
}
