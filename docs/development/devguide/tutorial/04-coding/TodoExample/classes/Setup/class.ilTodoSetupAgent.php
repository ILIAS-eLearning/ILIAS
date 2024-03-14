<?php

use ILIAS\Setup\Agent;
use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics\Storage;

class ilTodoSetupAgent extends NullAgent implements Agent
{
    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilTodoDBUpdateSteps());
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTodoDBUpdateSteps());
    }
}
