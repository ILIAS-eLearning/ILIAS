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
use Symfony\Component\Console\Input\InputInterface;


/**
 * Update command.
 */
class UpdateCommand extends BaseCommand {
	protected static $defaultName = "update";

	public function configure() {
		parent::configure();
		$this->setDescription("Updates an existing ILIAS installation");
	}

	protected function printIntroMessage(IOWrapper $io) {
		$io->title("Updating ILIAS");
	}

	protected function printOutroMessage(IOWrapper $io) {
		$io->success("Update complete. Thanks and have fun!");
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
			"Update ILIAS",
			false,
			$agent->getBuildArtifactObjective(),
			$agent->getUpdateObjective($config)
		);
	}
}
