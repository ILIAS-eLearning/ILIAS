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
class BuildArtifactsCommand extends Command {
	protected static $defaultName = "build-artifacts";

	/**
	 * @var Agent
	 */
	protected $agent;

	public function __construct(Agent $agent) {
		parent::__construct();
		$this->agent = $agent;
	}

	public function configure() {
	}

	public function execute(InputInterface $input, OutputInterface $output) {
		$goal = $this->agent->getBuildArtifactObjective();
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
}
