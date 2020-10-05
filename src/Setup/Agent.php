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
     * The provided configuration is to be used to set according configuration
     * values in the installation.
     *
     * @throw InvalidArgumentException if Config does not match the Agent..
     */
    public function getInstallObjective(Config $config = null) : Objective;

    /**
     * Get the goal the agent wants to achieve on update.
     *
     * The provided configuration is to be used to change according configuration
     * values in the installation. If this is not possible for some reason, an
     * according UnachievableException needs to be thrown in the according objective.
     * The configuration is not to be used to initialize the required environment
     * for the objectives. This must be done via ClientIdReadObjective and depending
     * objectives like ilIniFilesLoadedObjective.
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

    /**
     * Get the objective to collect metrics about the component the agent belongs
     * to.
     *
     * This is supposed to inform about any kind of metrics regarding the component.
     */
    public function getStatusObjective(Metrics\Storage $storage) : Objective;
}
