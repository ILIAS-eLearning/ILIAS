<?php declare(strict_types=1);

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Setup\Agent;

use ILIAS\Setup\Agent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

/**
 * An agent that just doesn't do a thing.
 */
class NullAgent implements Agent
{
    protected Refinery $refinery;

    public function __construct(
        Refinery $refinery
    ) {
        $this->refinery = $refinery;
    }

    /**
      * @inheritdocs
      */
    public function hasConfig() : bool
    {
        return false;
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException(
            self::class . " has no config."
        );
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(Config $config = null) : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(Config $config = null) : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getBuildArtifactObjective() : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null) : array
    {
        return [];
    }
}
