<?php
/* Copyright (c) 2020 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Add this to an Command that has an agent.
 */
trait HasAgent
{
    /**
     * @var AgentFinder
     */
    protected $agent_finder = null;

    protected function configureCommandForPlugins()
    {
        $this->addArgument("plugin-name", InputArgument::OPTIONAL, "Name of the plugin to run the command for.");
        $this->addOption("no-plugins", null, InputOption::VALUE_NONE, "Ignore all plugins when running the command.");
        $this->addOption("skip", null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Skip plugin with the supplied <plugin-name> when running the command.");
    }

    protected function getRelevantAgent(InputInterface $input) : Agent
    {
        if (!$this->agent_finder) {
            throw new \LogicException(
                "\$this->agent_finder needs to intialized with an AgentFinder."
            );
        }

        if ($input->getOption("no-plugins")) {
            // The agents of the core are in all folders but the customizing folder.
            return $this->agent_finder->getCoreAgents();
        }

        $plugin_name = $input->getArgument("plugin-name");
        if ($plugin_name) {
            return $this->agent_finder->getPluginAgent($plugin_name);
        }

        $agents = $this->agent_finder->getAgents();
        foreach (($input->getOption("skip") ?? []) as $plugin_name) {
            $agents = $agents->withRemovedAgent(strtolower($plugin_name));
        }

        return $agents;
    }
}
