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
     *
     * The configuration is not to be used to initialize the required environment
     * for the objectives. This must be done via ClientIdReadObjective and depending
     * objectives like ilIniFilesLoadedObjective.
     *
     * If no configuration is provided the configuration of the component should
     * stay as is.
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
     * Get the objective to be achieved when status is requested.
     *
     * Make sure that this runs in a reasonable time and also uses a reasonable
     * amount of ressources, since the command fed by this objective is meant to
     * be called by monitoring systems in short intervalls. So no expansive queries,
     * complicated calculations or long lasting network requests.
     *
     * This is supposed to inform about any kind of metrics regarding the component.
     */
    public function getStatusObjective(Metrics\Storage $storage) : Objective;
}
