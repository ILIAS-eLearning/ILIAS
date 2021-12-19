<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\Setup;
use ILIAS\Refinery;

class ilMathJaxSetupAgent implements Setup\Agent
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
            return new ilMathJaxSetupConfig((array) $data);
        });
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        /** @var ilMathJaxSetupConfig $config */
        return new ilMathJaxConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            /** @var ilMathJaxSetupConfig $config */
            return new ilMathJaxConfigStoredObjective($config);
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
        return new ilMathJaxMetricsCollectedObjective($storage);
    }

    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
