<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Setup\CLI;

use ILIAS\Setup\Agent;
use ILIAS\Setup\AgentFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Add this to an Command that has an agent.
 */
trait HasAgent
{
    protected ?AgentFinder $agent_finder = null;

    protected function configureCommandForPlugins(): void
    {
        $this->addOption("legacy-plugin", null, InputOption::VALUE_REQUIRED, "Name of the plugin to run the command for.");
        $this->addOption("no-legacy-plugins", null, InputOption::VALUE_NONE, "Ignore all plugins when running the command.");
        $this->addOption("skip-legacy-plugin", null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Skip plugin with the supplied <plugin-name> when running the command.");
    }

    protected function getRelevantAgent(InputInterface $input): Agent
    {
        if (!$this->agent_finder) {
            throw new \LogicException(
                "\$this->agent_finder needs to intialized with an AgentFinder."
            );
        }

        if ($input->hasOption("no-legacy-plugins") && $input->getOption("no-legacy-plugins")) {
            // The agents of the core are in all folders but the customizing folder.
            return $this->agent_finder->getComponentAgents();
        }

        if ($input->hasOption("legacy-plugin")) {
            $plugin_name = $input->getOption("legacy-plugin");
            if ($plugin_name) {
                return $this->agent_finder->getPluginAgent($plugin_name);
            }
        }

        $agents = $this->agent_finder->getAgents();
        if ($input->hasOption("skip-legacy-plugin")) {
            foreach (($input->getOption("skip-legacy-plugin") ?? []) as $plugin_name) {
                $agents = $agents->withRemovedAgent($plugin_name);
            }
        }

        return $agents;
    }
}
