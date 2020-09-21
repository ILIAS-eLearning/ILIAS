<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup;

use ILIAS\UI;
use ILIAS\Refinery\Transformation;

/**
 * A agent is some component that performs part of the setup process.
 */
interface Agent
{
    /**
     * Does this agent require a configuration?
     */
    public function hasConfig() : bool;

    /**
     * Agents must be able to tell how to create a configuration from a
     * nested array.
     *
     * @throw LogicException if Agent has no Config
     */
    public function getArrayToConfigTransformation() : Transformation;

    /**
     * Get the goals the agent wants to achieve on setup.
     *
     * @throw InvalidArgumentException if Config does not match the Agent..
     */
    public function getInstallObjective(Config $config = null) : Objective;

    /**
     * Get the goal the agent wants to achieve on update.
     *
     * @throw InvalidArgumentException if Config does not match the Agent..
     */
    public function getUpdateObjective(Config $config = null) : Objective;

    /**
     * Get the goal the agent wants to achieve to build artifacts.
     *
     * @throw InvalidArgumentException if Config does not match the Agent.
     */
    public function getBuildArtifactObjective() : Objective;
}
