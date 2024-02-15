<?php

declare(strict_types=1);

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

namespace ILIAS\Setup;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;

class ImplementationOfAgentFinder implements AgentFinder
{
    protected AgentCollection $component_agents;

    /**
     * @var array<string, Agent> $predefined_agents
     */
    public function __construct(
        protected Refinery $refinery,
        protected Data\Factory $data_factory,
        protected \ILIAS\Language\Language $lng,
        protected ImplementationOfInterfaceFinder $interface_finder,
        $component_agents
    ) {
        $this->component_agents = new AgentCollection($refinery, $component_agents);
    }

    /**
     * Collect all agents from the system, core and plugin, bundled in a collection.
     *
     * @param string[]  $ignore folders to be ignored.
     */
    public function getAgents(): AgentCollection
    {
        $agents = $this->getComponentAgents();

        // Get a list of existing plugins in the system.
        $plugins = $this->getPluginNames();

        foreach ($plugins as $plugin_name) {
            $agents = $agents->withAdditionalAgent(
                $plugin_name,
                $this->getPluginAgent($plugin_name)
            );
        }

        return $agents;
    }


    /**
     * Collect core agents from the system bundled in a collection.
     */
    public function getComponentAgents(): AgentCollection
    {
        return $this->component_agents;
    }

    /**
     * Get a agent from a specific plugin.
     *
     * If there is no plugin agent, this would the default agent.
     * If the plugin contains multiple agents, these will be collected.
     *
     * @param string $name of the plugin to get the agent from
     */
    public function getPluginAgent(string $name): Agent
    {
        // TODO: This seems to be something that rather belongs to Services/Component/
        // but we put it here anyway for the moment. This seems to be something that
        // could go away when we unify Services/Modules/Plugins to one common concept.
        $path = "[/]Customizing/global/plugins/.*/.*/" . $name . "/.*";
        $agent_classes = iterator_to_array($this->interface_finder->getMatchingClassNames(
            Agent::class,
            [],
            $path
        ));

        if ($agent_classes === []) {
            return new class ($name) extends \ilPluginDefaultAgent {
            };
        }

        $agents = [];
        foreach ($agent_classes as $class_name) {
            $agents[] = new $class_name(
                $this->refinery,
                $this->data_factory,
                $this->lng
            );
        }

        if (count($agents) === 1) {
            return $agents[0];
        }

        return new AgentCollection(
            $this->refinery,
            $agents
        );
    }

    public function getAgentByClassName(string $class_name): Agent
    {
        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("Class '" . $class_name . "' not found.");
        }

        return new $class_name(
            $this->refinery,
            $this->data_factory,
            $this->lng
        );
    }

    /**
     * Derive a name for the agent based on a class name.
     */
    public function getAgentNameByClassName(string $class_name): string
    {
        // We assume that the name of an agent in the class ilXYZSetupAgent really
        // is XYZ. If that does not fit we just use the class name.
        $match = [];
        if (preg_match("/il(\w+)SetupAgent/", $class_name, $match)) {
            return strtolower($match[1]);
        }
        return $class_name;
    }

    /**
     * @return \Generator <string>
     */
    protected function getPluginNames(): \Generator
    {
        $directories =
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(__DIR__ . "/../../../../Customizing/global/plugins/")
            );
        $names = [];
        foreach ($directories as $dir) {
            $groups = [];
            if (preg_match("%^" . __DIR__ . "/[.][.]/[.][.]/[.][.]/[.][.]/Customizing/global/plugins/((Modules)|(Services))/((\\w+/){2})([^/\.]+)(/|$)%", (string) $dir, $groups)) {
                $name = $groups[6];
                if (isset($names[$name])) {
                    continue;
                }
                $names[$name] = true;
                yield $name;
            }
        }
    }
}
