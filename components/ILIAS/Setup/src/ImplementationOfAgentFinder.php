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

declare(strict_types=1);

namespace ILIAS\Setup;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;

class ImplementationOfAgentFinder implements AgentFinder
{
    protected const string PLUGIN_PATH = "[/]public/Customizing/global/plugins/";

    protected array|AgentCollection $component_agents;
    protected string $plugin_directory;

    public function __construct(
        protected Refinery $refinery,
        protected Data\Factory $data_factory,
        protected \ILIAS\Language\Language $lng,
        protected ImplementationOfInterfaceFinder $interface_finder,
        $component_agents,
        ?string $plugin_directory = null
    ) {
        $this->component_agents = $component_agents;
        $this->plugin_directory = $plugin_directory ?? __DIR__ . "/../../../../public/Customizing/global/plugins";
    }

    /**
     * Collect all agents from the system, core and plugin, bundled in a collection.
     *
     * @param string[]  $ignore folders to be ignored.
     */
    public function getAgents(): AgentCollection
    {
        $agents = $this->getComponentAgents();

        // Searching the classes of all plugins at once, a search per plugin would
        // go through every class of the system once for every plugin.
        $agent_classes_by_plugin = [];
        foreach ($this->interface_finder->getMatchingClassNames(Agent::class, [], self::PLUGIN_PATH . ".*") as $class_name) {
            $plugin_name = $this->getPluginNameOfClass($class_name);
            if ($plugin_name !== null) {
                $agent_classes_by_plugin[$plugin_name][] = $class_name;
            }
        }

        foreach ($this->getPluginNames() as $plugin_name) {
            $agents = $agents->withAdditionalAgent(
                $plugin_name,
                $this->buildPluginAgent($plugin_name, $agent_classes_by_plugin[$plugin_name] ?? [])
            );
        }

        return $agents;
    }


    /**
     * Collect core agents from the system bundled in a collection.
     */
    public function getComponentAgents(): AgentCollection
    {
        if ($this->component_agents instanceof AgentCollection) {
            return $this->component_agents;
        }

        $agents = new AgentCollection($this->refinery, []);

        foreach ($this->component_agents as $agent) {
            $name = $agent instanceof NamedAgent
                ? $agent->getAgentName()
                : $this->getAgentNameByClassName(get_class($agent));
            $agents = $agents->withAdditionalAgent($name, $agent);
        }

        $this->component_agents = $agents;
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
        $agent_classes = array_filter(
            iterator_to_array($this->interface_finder->getMatchingClassNames(
                Agent::class,
                [],
                self::PLUGIN_PATH . ".*/.*/" . $name . "/.*"
            )),
            fn(string $class_name): bool => $this->getPluginNameOfClass($class_name) === $name
        );

        return $this->buildPluginAgent($name, array_values($agent_classes));
    }

    /**
     * @param string[] $agent_classes
     */
    protected function buildPluginAgent(string $name, array $agent_classes): Agent
    {
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

    /**
     * The name of the plugin a class belongs to, or null if it is no class of a plugin.
     */
    protected function getPluginNameOfClass(string $class_name): ?string
    {
        $file = (new \ReflectionClass($class_name))->getFileName();
        $groups = [];
        if ($file !== false
            && preg_match("%/Customizing/global/plugins/(?:Services|Modules)/\\w+/\\w+/([^/.]+)/%", $file, $groups)) {
            return $groups[1];
        }
        return null;
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
        // Plugins live exactly four levels below the plugin directory, so there is
        // no need to walk through every file of every plugin (including their
        // vendor directories) to find them.
        $directories = array_merge(
            glob($this->plugin_directory . "/Services/*/*/*", GLOB_ONLYDIR) ?: [],
            glob($this->plugin_directory . "/Modules/*/*/*", GLOB_ONLYDIR) ?: []
        );
        $names = [];
        foreach ($directories as $dir) {
            $name = basename($dir);
            if (!preg_match("%/\\w+/\\w+/[^/.]+$%", $dir) || isset($names[$name])) {
                continue;
            }
            $names[$name] = true;
            yield $name;
        }
    }
}
