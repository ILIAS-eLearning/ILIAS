<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilStyleSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    /**
     * @var Refinery\Factory
     */
    protected $refinery;

    public function __construct(
        Refinery\Factory $refinery
    ) {
        $this->refinery = $refinery;
    }

    /**
     * @inheritdoc
     */
    public function hasConfig() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilStyleSetupConfig(
                $data["manage_system_styles"] ?? false,
                $data["path_to_lessc"] ?? null
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilStyleConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            return new Setup\ObjectiveCollection(
                "Style update objectives",
                true,
                new ilStyleConfigStoredObjective($config),
                new ilStyleReplaceResponsiveLogoObjective()
            );
        }
        return new ilStyleReplaceResponsiveLogoObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new ilKitchenSinkDataCollectedObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilStyleMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
