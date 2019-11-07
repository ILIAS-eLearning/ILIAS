<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\UnachievableException;
use ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command base class.
 */
abstract class BaseCommand extends Command {
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
		$config = $this->readAgentConfig($this->agent, $input);
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

	abstract protected function printIntroMessage(IOWrapper $io);

	abstract protected function printOutroMessage(IOWrapper $io); 

	abstract protected function readAgentConfig(Agent $agent, InputInterface $input) : ?Config;

	abstract protected function buildEnvironment(Agent $agent, ?Config $config, IOWrapper $io);

	abstract protected function getObjective(Agent $agent, ?Config $config) : Objective;
}
