<?php

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;

class ilTestQuestionPoolSetupAgent extends NullAgent
{
    public function getUpdateObjective(ILIAS\Setup\Config $config = null) : ILIAS\Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilTestQuestionPool80DBUpdateSteps());
    }
}
