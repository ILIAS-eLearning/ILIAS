<?php
/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use ILIAS\Setup;
use \ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Input\Field\Input;

abstract class ilPluginDefaultAgent implements Setup\Agent
{
    /**
     * @var string
     */
    protected $plugin_name;

    public function __construct(string $plugin_name)
    {
        $this->plugin_name = $plugin_name;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException(self::class . " has no Config.");
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Complete objectives from Services/Component',
            false,
            new \ilComponentInstallPluginObjective($this->plugin_name),
            new \ilComponentUpdatePluginObjective($this->plugin_name),
            new \ilComponentActivatePluginsObjective($this->plugin_name)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Complete objectives from Services/Component',
            false,
            new \ilComponentUpdatePluginObjective($this->plugin_name),
            new \ilComponentActivatePluginsObjective($this->plugin_name)
        );
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
