<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\Setup;

interface AgentFinder
{
    /**
     * Collect all agents from the system, core and plugin, bundled in a collection.
     */
    public function getAgents() : AgentCollection;

    /**
     * Collect core agents from the system bundled in a collection.
     */
    public function getCoreAgents() : AgentCollection;
    
    /**
     * Get a agent from a specific plugin.
     *
     * If there is no plugin agent, this would the default agent.
     * If the plugin contains multiple agents, these will be collected.
     *
     * @param string $name of the plugin to get the agent from
     */
    public function getPluginAgent(string $name) : Agent;
}
