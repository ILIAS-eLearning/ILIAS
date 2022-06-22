<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Setup\Config;

class ilAccessRBACSetupAgent extends Setup\Agent\NullAgent
{

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilAccessRBACDeleteDbkSteps);
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilAccessRBACDeleteDbkSteps);
    }
}
