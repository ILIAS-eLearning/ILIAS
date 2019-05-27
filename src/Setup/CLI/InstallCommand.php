<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
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

	public function __construct(Agent $agent) {
		parent::__construct();
		$this->agent = $agent;
	}

	public function configure() {
		$this
			->addArgument("config", InputArgument::REQUIRED, "Configuration for the Setup.");
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		if ($this->agent->hasConfig()) {
			$config_file = $input->getArgument("config");
			$config_content = $this->readConfigFile($config_file);
			$trafo = $this->agent->getArrayToConfigTransformation();
			$config = $trafo->transform($config_content);
		}
		else {
			$config = null;
		}

		$goal = $this->agent->getInstallObjective($config);
		$environment = new ArrayEnvironment([]);

		$goals = new ObjectiveIterator($environment, $goal);
		while($goals->valid()) {
			$current = $goals->current();
			if ($current->isNotable() || $output->isVeryVerbose()  || $output->isDebug()) {
				$output->writeln($current->getLabel());
			}
			$environment = $current->achieve($environment);
			$goals->setEnvironment($environment);
			$goals->next();
		}
	}

	protected function readConfigFile(string $name) : array {
		if (!file_exists($name) || !is_readable($name)) {
			throw new \InvalidArgumentException(
				"Config-file $name does not exist or is not readable."
			);
		}
		$json = json_decode(file_get_contents($name), JSON_OBJECT_AS_ARRAY);
		if (!is_array($json)) {
			throw new \InvalidArgumentException(
				"Could not find JSON-array in $name."
			);
		}
		return $json;
	}
}
