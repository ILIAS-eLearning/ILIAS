<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Data;
use ILIAS\UI;

class ilBackgroundTasksSetupAgent implements Setup\Agent
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
        return $this->refinery->custom()->transformation(fn ($data) : \ilBackgroundTasksSetupConfig => new \ilBackgroundTasksSetupConfig(
            $data["type"] ?? \ilBackgroundTasksSetupConfig::TYPE_SYNCHRONOUS,
            $data["max_number_of_concurrent_tasks"] ?? 1
        ));
    }
    
    /**
     * @inheritdoc
     */
    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        /** @noinspection PhpParamsInspection */
        return new ilBackgroundTasksConfigStoredObjective($config);
    }
    
    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        if ($config !== null) {
            /** @noinspection PhpParamsInspection */
            return new ilBackgroundTasksConfigStoredObjective($config);
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
        return new ilBackgroundTasksMetricsCollectedObjective($storage);
    }
    
    /**
     * @inheritDoc
     */
    public function getMigrations() : array
    {
        return [];
    }
}
