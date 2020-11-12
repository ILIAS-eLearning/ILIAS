<?php

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ilSetupLanguage;

class ImplementationOfAgentFinder implements AgentFinder
{
    /**
     * @var Refinery|null
     */
    protected $refinery;

    /**
     * @var Data\Factory|null
     */
    protected $data_factory;

    /**
     * @var \ilSetupLanguage
     */
    protected $lng;

    /**
     * @var \ilPluginRawReader|null
     */
    protected $plugin_raw_reader;

    /**
     * @var ImplementationOfInterfaceFinder|null
     */
    protected $interface_finder;

    /**
     * @var array<string, Setup\Agent> $predefined_agents
     */
    protected $predefined_agents;
    
    /**
     * @var array<string, Setup\Agent> $predefined_agents
     */
    public function __construct(
        Refinery $refinery,
        Data\Factory $data_factory,
        \ilSetupLanguage $lng,
        ImplementationOfInterfaceFinder $interface_finder,
        \ilPluginRawReader $plugin_raw_reader,
        array $predefined_agents = []
    ) {
        $this->refinery = $refinery;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
        $this->interface_finder = $interface_finder;
        $this->plugin_raw_reader = $plugin_raw_reader;
        $this->predefined_agents = $predefined_agents;
    }

    /**
     * Collect all agents from the system, core and plugin, bundled in a collection.
     *
     * @param string[]  $ignore folders to be ignored.
     */
    public function getAgents() : AgentCollection
    {
        $agents = $this->getCoreAgents();

        // Get a list of existing plugins in the system.
        $plugins = $this->plugin_raw_reader->getPluginNames();

        foreach ($plugins as $plugin_name) {
            $agents = $agents->withAdditionalAgent(
                strtolower($plugin_name),
                $this->getPluginAgent($plugin_name)
            );
        }

        return $agents;
    }


    /**
     * Collect core agents from the system bundled in a collection.
     */
    public function getCoreAgents() : AgentCollection
    {
        // Initialize the agents.
        $agents = new AgentCollection(
            $this->refinery,
            $this->predefined_agents
        );

        // This is a list of all agent classes in the system (which we don't want to ignore).
        $agent_classes = $this->interface_finder->getMatchingClassNames(
            Agent::class,
            ["[/]Customizing/.*"]
        );
        foreach ($agent_classes as $class_name) {
            $agents = $agents->withAdditionalAgent(
                $this->getAgentNameByClassName($class_name),
                new $class_name(
                    $this->refinery,
                    $this->data_factory,
                    $this->lng
                )
            );
        }

        return $agents;
    }
    
    /**
     * Get a agent from a specific plugin.
     *
     * If there is no plugin agent, this would the default agent.
     * If the plugin contains multiple agents, these will be collected.
     *
     * @param string $name of the plugin to get the agent from
     */
    public function getPluginAgent(string $name) : Agent
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

        if (count($agent_classes) === 0) {
            return new class($name) extends \ilPluginDefaultAgent {
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

        return new \ilPluginDefaultAgent($name);
    }

    /**
     * Derive a name for the agent based on a class name.
     */
    protected function getAgentNameByClassName(string $class_name) : string
    {
        // We assume that the name of an agent in the class ilXYZSetupAgent really
        // is XYZ. If that does not fit we just use the class name.
        $match = [];
        if (preg_match("/il(\w+)SetupAgent/", $class_name, $match)) {
            return strtolower($match[1]);
        }
        return $class_name;
    }
}
