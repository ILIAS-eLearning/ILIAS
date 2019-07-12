<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class BuildArtifactsCommandTest extends \PHPUnit\Framework\TestCase {
	public function testBasicFunctionality() {
		$refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

		$agent = $this->createMock(Setup\Agent::class);
		$command = new Setup\CLI\BuildArtifactsCommand($agent);

		$tester = new CommandTester($command);

		$objective = $this->createMock(Setup\Objective::class);
		$env = $this->createMock(Setup\Environment::class);

		$agent
			->expects($this->once())
			->method("getBuildArtifactObjective")
			->with()
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
		]);
	}
}
