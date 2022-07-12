<?php declare(strict_types=1);

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

    /**
     * Get an agent by class name.
     *
     * Throws an exception if the class doesn't exists.
     *
     * @param string $class_name
     * @return AgentCollection
     * @throws \InvalidArgumentException
     */
    public function getAgentByClassName(string $class_name) : Agent;

    /**
     * Derive a name for the agent based on a class name.
     */
    public function getAgentNameByClassName(string $class_name) : string;
}
