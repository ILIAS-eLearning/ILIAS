<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class ilTreeSetupAgent implements Setup\Agent
{
    /**
     * @var Refinery
     */
    protected $refinery;

    public function __construct(Refinery $refinery)
    {
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
    public function getConfigInput(Setup\Config $config = null) : ILIAS\UI\Component\Input\Field\Input
    {
        throw new \LogicException("NYI!");
    }

    /**
     * @inheritdocs
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    /**
     * @inheritdocs
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilTreeExistsObjective();
    }

    /**
     * @inheritdocs
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdocs
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage) : \ILIAS\Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getMigrations() : array
    {
        return [];
    }
}
