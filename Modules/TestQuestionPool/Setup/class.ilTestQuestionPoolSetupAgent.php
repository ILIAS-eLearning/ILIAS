<?php

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

class ilTestQuestionPoolSetupAgent extends NullAgent
{
    public function getUpdateObjective(ILIAS\Setup\Config $config = null) : ILIAS\Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilTestQuestionPool80DBUpdateSteps());
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTestQuestionPool80DBUpdateSteps());
    }
}
