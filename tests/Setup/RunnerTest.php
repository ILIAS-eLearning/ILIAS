<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup;

use ILIAS\Setup;

class RunnerTest extends \PHPUnit\Framework\TestCase {
	public function testBasicAlgorithm() {
		$goal = $this->createMock(Setup\Goal::class);
		$config = $this->createMock(Setup\Config::class);
		$configuration_loader = $this->createMock(Setup\ConfigurationLoader::class);
		$environment = $this->createMock(Setup\Environment::class);

		$type = "TYPE";

		$runner = new Setup\Runner($environment, $configuration_loader, $goal);

		$configuration_loader
			->expects($this->once())
			->method("loadConfigurationFor")
			->with($type)
			->willReturn($config);

		$goal
			->expects($this->once())
			->method("getType")
			->willReturn($type);

		$goal
			->expects($this->once())
			->method("withConfiguration")
			->with($config)
			->willReturn($goal);

		$goal
			->expects($this->once())
			->method("withResourcesFrom")
			->with($environment)
			->willReturn($goal);

		$goal
			->expects($this->once())
			->method("getPreconditions")
			->willReturn([]);

		$goal
			->expects($this->once())
			->method("achieve")
			->with($environment)
			->willReturn(null);	

		$runner->run();
	}
}
