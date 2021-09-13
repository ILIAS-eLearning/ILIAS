<?php declare(strict_types = 1);

use ILIAS\Setup;

class ilDatabaseUpdateAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilDatabaseSetupStepsUpdateSteps()
        );
    }
}
