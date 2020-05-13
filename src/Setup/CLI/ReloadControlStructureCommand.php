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

/**
 * Reload Control Structure command.
 */
class ReloadControlStructureCommand extends BaseCommand
{
    protected static $defaultName = "reload-control-structure";

    public function configure()
    {
        parent::configure();
        $this->setDescription("Reloads the control structure of the installation.");
    }

    protected function printIntroMessage(IOWrapper $io)
    {
        $io->title("Reloading control structure of ILIAS");
    }

    protected function printOutroMessage(IOWrapper $io)
    {
        $io->success("Control structure reloaded. Thanks and have fun!");
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
        // ATTENTION: This is not how we want to do this in general during the
        // setup, stuff should use Dependency Injection. However, since we
        // currently won't get there with the control structure but still need
        // a quick way to reload it, we do it anyway.
        return new ObjectiveCollection(
            "Install and update ILIAS",
            false,
            new \ilCtrlStructureStoredObjective(
                new \ilCtrlStructureReader()
            ),
            new \ilComponentDefinitionsStoredObjective(false)
        );
    }
}
