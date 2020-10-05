<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilHttpSetupAgent implements Setup\Agent
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
    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilHttpSetupConfig(
                $data["path"],
                (isset($data["https_autodetection"]) && $data["https_autodetection"])
                    ? true
                    : false,
                (isset($data["https_autodetection"]) && $data["https_autodetection"])
                    ? $data["https_autodetection"]["header_name"]
                    : null,
                (isset($data["https_autodetection"]) && $data["https_autodetection"])
                    ? $data["https_autodetection"]["header_value"]
                    : null,
                (isset($data["proxy"]) && $data["proxy"])
                    ? true
                    : false,
                (isset($data["proxy"]) && $data["proxy"])
                    ? $data["proxy"]["host"]
                    : null,
                (isset($data["proxy"]) && $data["proxy"])
                    ? $data["proxy"]["port"]
                    : null,
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilHttpConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilHttpConfigStoredObjective($config);
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
}
