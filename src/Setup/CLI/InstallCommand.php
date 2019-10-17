<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentCollection;
use ILIAS\Setup\ArrayEnvironment;
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
		if ($this->agent->hasConfig()) {
			$config_file = $input->getArgument("config");
			$config_content = $this->config_reader->readConfigFile($config_file);
			$trafo = $this->agent->getArrayToConfigTransformation();
			$config = $trafo->transform($config_content);
		}
		else {
			$config = null;
		}

		$goal = $this->agent->getInstallObjective($config);
		$environment = new ArrayEnvironment([]);

		if ($this->agent instanceof AgentCollection && $config) {
			foreach ($config->getKeys() as $k) {
				$environment = $environment->withConfigFor($k, $config->getConfig($k));
			}
		}

		$goals = new ObjectiveIterator($environment, $goal);
		$io = new IOWrapper($input, $output);
		while($goals->valid()) {
			$current = $goals->current();
			$io->startObjective($current->getLabel(), $current->isNotable());
			$environment = $current->achieve($environment);
			$io->finishedLastObjective($current->getLabel(), $current->isNotable());
			$goals->setEnvironment($environment);
			$goals->next();
		}
	}
}
