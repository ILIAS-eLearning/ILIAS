<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class InstallCommandTest extends \PHPUnit\Framework\TestCase {
	public function testBasicFunctionality() {
		$refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

		$agent = $this->createMock(Setup\Agent::class);
		$config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
		$command = new Setup\CLI\InstallCommand($agent, $config_reader);

		$tester = new CommandTester($command);

		$config = $this->createMock(Setup\Config::class);
		$config_file = "config_file";
		$config_file_content = ["config_file"];

		$objective = $this->createMock(Setup\Objective::class);
		$env = $this->createMock(Setup\Environment::class);

		$config_reader
			->expects($this->once())
			->method("readConfigFile")
			->with($config_file)
			->willReturn($config_file_content);

		$agent
			->expects($this->once())
			->method("hasConfig")
			->willReturn(true);

		$agent
			->expects($this->once())
			->method("getArrayToConfigTransformation")
			->with()
			->willReturn($refinery->custom()->transformation(function($v) use ($config_file_content, $config) {
				$this->assertEquals($v, $config_file_content);
				return $config;
			}));

		$agent
			->expects($this->once())
			->method("getInstallObjective")
			->with($config)
			->willReturn($objective);

		$objective
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([]);

		$objective
			->expects($this->once())
			->method("achieve")
			->willReturn($env);
		
		$tester->execute([
			"config" => $config_file
		]);
	}
}
