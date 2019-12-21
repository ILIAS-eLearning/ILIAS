<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilSystemFolderSetupAgent implements Setup\Agent
{
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
    public function getConfigInput(Setup\Config $config = null) : UI\Component\Input\Field\Input
    {
        throw new \LogicException("Not yet implemented.");
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilSystemFolderSetupConfig(
                $data["client"]["name"],
                $data["client"]["description"] ?? null,
                $data["client"]["institution"] ?? null,
                $data["contact"]["firstname"],
                $data["contact"]["lastname"],
                $data["contact"]["title"] ?? null,
                $data["contact"]["position"] ?? null,
                $data["contact"]["institution"] ?? null,
                $data["contact"]["street"] ?? null,
                $data["contact"]["zipcode"] ?? null,
                $data["contact"]["city"] ?? null,
                $data["contact"]["country"] ?? null,
                $data["contact"]["phone"] ?? null,
                $data["contact"]["email"],
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "Complete objectives from Modules/SystemFolder",
            false,
            new ilInstallationInformationStoredObjective($config)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new Setup\NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\NullObjective();
    }
}
