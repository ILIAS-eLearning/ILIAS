<?php
/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentFinder;
use ILIAS\Setup\ArrayEnvironment;
use ILIAS\Setup\Environment;
use ILIAS\Setup\NoConfirmationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Installation command.
 */
class BuildArtifactsCommand extends Command
{
    use HasAgent;
    use ObjectiveHelper;

    protected static $defaultName = "build-artifacts";

    public function __construct(AgentFinder $agent_finder)
    {
        parent::__construct();
        $this->agent_finder = $agent_finder;
    }

    public function configure()
    {
        $this->setDescription("Build static artifacts from source");
        $this->addOption("yes", "y", InputOption::VALUE_NONE, "Confirm every message of the setup.");
        $this->configureCommandForPlugins();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new IOWrapper($input, $output);
        $io->printLicenseMessage();
        $io->title("Building Static Artifacts for ILIAS");

        $agent = $this->getRelevantAgent($input);

        $objective = $agent->getBuildArtifactObjective();

        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        try {
            $this->achieveObjective($objective, $environment, $io);
            $io->success("All static artifacts are build!");
        } catch (NoConfirmationException $e) {
            $io->error("Aborting Installation, a necessary confirmation is missing:\n\n" . $e->getRequestedConfirmation());
        }
    }
}
