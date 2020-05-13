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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installation command.
 */
class InstallCommand extends BaseCommand
{
    protected static $defaultName = "install";

    public function configure()
    {
        parent::configure();
        $this->setDescription("Creates a fresh ILIAS installation based on the config");
    }

    protected function printIntroMessage(IOWrapper $io)
    {
        $io->title("Installing ILIAS");
    }

    protected function printOutroMessage(IOWrapper $io)
    {
        $io->success("Installation complete. Thanks and have fun!");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // ATTENTION: This is a hack to get around the usage of the echo/exit pattern in
        // the setup for the command line version of the setup. Do not use this.
        define("ILIAS_SETUP_IGNORE_DB_UPDATE_STEP_MESSAGES", true);
        return parent::execute($input, $output);
    }

    protected function buildEnvironment(Agent $agent, ?Config $config, IOWrapper $io) : Environment
    {
        $environment = new ArrayEnvironment([
            Environment::RESOURCE_ADMIN_INTERACTION => $io
        ]);

        if ($agent instanceof AgentCollection && $config) {
            foreach ($config->getKeys() as $k) {
                $environment = $environment->withConfigFor($k, $config->getConfig($k));
            }
        }

        return $environment;
    }

    protected function getObjective(Agent $agent, ?Config $config) : Objective
    {
        return new ObjectiveCollection(
            "Install and update ILIAS",
            false,
            $agent->getInstallObjective($config),
            $agent->getUpdateObjective($config)
        );
    }
}
