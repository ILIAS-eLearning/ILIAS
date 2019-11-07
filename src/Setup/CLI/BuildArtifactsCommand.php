<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Config;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Installation command.
 */
class BuildArtifactsCommand extends BaseCommand {
	protected static $defaultName = "build-artifacts";

	public function configure() {
	}

	protected function printIntroMessage(IOWrapper $io) {
		$io->title("Building static artifacts");
	}

	protected function printOutroMessage(IOWrapper $io) {
		$io->success("All static artifacts are build!");
	}

	protected function readAgentConfig(Agent $agent, InputInterface $input) : ?Config {
		return null;
	}

	protected function buildEnvironment(Agent $agent, ?Config $config, IOWrapper $io) {
		return new ArrayEnvironment([]);
	}

	protected function getObjective(Agent $agent, ?Config $config) : Objective {
		return $agent->getBuildArtifactObjective();
	}
}
