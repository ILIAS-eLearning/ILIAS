<?php

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
class ilPreviewSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;

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
        return $this->refinery->custom()->transformation(fn ($data) : \ilPreviewSetupConfig => new \ilPreviewSetupConfig(
            $data["path_to_ghostscript"] ?? null
        ));
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        /** @noinspection PhpParamsInspection */
        return new ilPreviewConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            /** @noinspection PhpParamsInspection */
            return new ilPreviewConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
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
        return new ilPreviewMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
