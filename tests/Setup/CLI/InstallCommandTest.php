<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use Symfony\Component\Console\Tester\CommandTester;

class InstallCommandTest extends \PHPUnit\Framework\TestCase {
	public function testBasicFunctionality() {
		$consumer = $this->createMock(Setup\Consumer::class);
		$command = $this
			->getMockBuilder(Setup\CLI\InstallCommand::class)
			->setMethods(["readConfigFile"])
			->setConstructorArgs([$consumer])
			->getMock();
		$tester = new CommandTester($command);

		$config = $this->createMock(Setup\Config::class);
		$config_file = "config_file";
		$config_file_content = ["config_file"];

		$goal = $this->createMock(Setup\Goal::class);
		$env = $this->createMock(Setup\Environment::class);

		$command
			->expects($this->once())
			->method("readConfigFile")
			->with($config_file)
			->willReturn($config_file_content);

		$consumer
			->expects($this->once())
			->method("hasConfig")
			->willReturn(true);

		$consumer
			->expects($this->once())
			->method("getConfigFromArray")
			->with($config_file_content)
			->willReturn($config);

		$consumer
			->expects($this->once())
			->method("getInstallGoal")
			->with($config)
			->willReturn($goal);

		$goal
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([]);

		$goal
			->expects($this->once())
			->method("achieve")
			->willReturn($env);
		
		$tester->execute([
			"config" => $config_file
		]);
	}

	public function testReadConfigFile() {
		$filename = tempnam("/tmp", "ILIAS");
		$expected = [
			"some" => [
				"nested" => "config"
			]
		];
		file_put_contents($filename, json_encode($expected));
		
		$obj = new class extends Setup\CLI\InstallCommand {
			public function __construct() {}
			public function _readConfigFile($n) { return $this->readConfigFile($n); }
		};

		$config = $obj->_readConfigFile($filename);

		$this->assertEquals($expected, $config);
	}
}
