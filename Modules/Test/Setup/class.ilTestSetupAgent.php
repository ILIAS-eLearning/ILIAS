<?php declare(strict_types = 1);

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

class ilTestSetupAgent extends NullAgent
{
    public function getUpdateObjective(ILIAS\Setup\Config $config = null) : Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilTest8DBUpdateSteps());
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTest8DBUpdateSteps());
    }
}
