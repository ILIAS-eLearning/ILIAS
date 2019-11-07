<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\UnachievableException;
use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\AchievementTracker;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installation command.
 */
class InstallCommand extends Command {
	protected static $defaultName = "install";

	/**
	 * @var Agent
	 */
	protected $agent;

	/**
	 * @var ConfigReader
	 */
	protected $config_reader;

	public function __construct(Agent $agent, ConfigReader $config_reader) {
		parent::__construct();
		$this->agent = $agent;
		$this->config_reader = $config_reader;
	}

	public function configure() {
		$this
			->addArgument("config", InputArgument::REQUIRED, "Configuration for the Setup.");
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$io = new IOWrapper($input, $output);
		$config = $this->readAgentConfig($this->agent, $input->getArgument("config"));
		$environment = $this->buildEnvironment($this->agent, $config, $io);
		$goal = $this->getObjective($this->agent, $config);
		$goals = new ObjectiveIterator($environment, $goal);

		$this->printIntroMessage($io);

		while($goals->valid()) {
			$current = $goals->current();
			$io->startObjective($current->getLabel(), $current->isNotable());
			try {
				$environment = $current->achieve($environment);
				$io->finishedLastObjective($current->getLabel(), $current->isNotable());
				$goals->setEnvironment($environment);
			}
			catch (UnachievableException $e) {
				$goals->markAsFailed($current);
				$io->failedLastObjective($current->getLabel());
			}
			$goals->next();
		}

		$this->printOutroMessage($io);
	}

	protected function printIntroMessage(IOWrapper $io) {
		$io->title("Installing ILIAS");
	}

	protected function printOutroMessage(IOWrapper $io) {
		$io->success("Installation complete. Thanks and have fun!");
	}

	protected function readAgentConfig(Agent $agent, string $config_file) : ?Config {
		if (!$agent->hasConfig()) {
			return null;
		}

		$config_content = $this->config_reader->readConfigFile($config_file);
		$trafo = $this->agent->getArrayToConfigTransformation();
		return $trafo->transform($config_content);
	}

	protected function buildEnvironment(Agent $agent, Config $config, IOWrapper $io) : Environment {
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

	protected function getObjective(Agent $agent, Config $config) : Objective {
		return new ObjectiveCollection(
			"Install and update ILIAS",
			false,
			$agent->getBuildArtifactObjective(),
			$agent->getInstallObjective($config),
			$agent->getUpdateObjective($config)
		);
	}
}
