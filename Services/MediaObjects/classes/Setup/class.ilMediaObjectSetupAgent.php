<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilMediaObjectSetupAgent implements Setup\Agent
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
            return new \ilMediaObjectSetupConfig(
                $data["path_to_ffmpeg"] ?? null
            );
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        $dir_objective = new ilFileSystemComponentDataDirectoryCreatedObjective(
            'mobs',
            ilFileSystemComponentDataDirectoryCreatedObjective::WEBDIR
        );

        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/MediaObject",
            false,
            new ilMediaObjectConfigStoredObjective($config),
            $dir_objective
        );
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilMediaObjectConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }
}
