<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Config;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\AchievementTracker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;


/**
 * Installation command.
 */
class InstallCommand extends BaseCommand {
	protected static $defaultName = "install";

	public function configure() {
		$this
			->addArgument("config", InputArgument::REQUIRED, "Configuration for the setup")
			->setDescription("Creates a fresh ILIAS installation based on the config");
	}

	protected function printIntroMessage(IOWrapper $io) {
		$io->title("Installing ILIAS");
	}

	protected function printOutroMessage(IOWrapper $io) {
		$io->success("Installation complete. Thanks and have fun!");
	}

	protected function readAgentConfig(Agent $agent, InputInterface $input) : ?Config {
		if (!$agent->hasConfig()) {
			return null;
		}

		$config_file = $input->getArgument("config");
		$config_content = $this->config_reader->readConfigFile($config_file);
		$trafo = $this->agent->getArrayToConfigTransformation();
		return $trafo->transform($config_content);
	}

	protected function buildEnvironment(Agent $agent, ?Config $config, IOWrapper $io) : Environment {
		$environment = new ArrayEnvironment([
			Environment::RESOURCE_ADMIN_INTERACTION => $io,
			// TODO: This needs to be implemented correctly...
			Environment::RESOURCE_ACHIEVEMENT_TRACKER => new class implements AchievementTracker {
				public function trackAchievementOf(Objective $objective) : void {}
				public function isAchieved(Objective $objective) : bool { return false; }
			}
		]);

		if ($agent instanceof AgentCollection && $config) {
			foreach ($config->getKeys() as $k) {
				$environment = $environment->withConfigFor($k, $config->getConfig($k));
			}
		}

		return $environment;
	}

	protected function getObjective(Agent $agent, ?Config $config) : Objective {
		return new ObjectiveCollection(
			"Install and update ILIAS",
			false,
			$agent->getBuildArtifactObjective(),
			$agent->getInstallObjective($config),
			$agent->getUpdateObjective($config)
		);
	}
}
