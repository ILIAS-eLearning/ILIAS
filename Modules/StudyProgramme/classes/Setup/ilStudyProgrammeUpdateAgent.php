<?php declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

class ilStudyProgrammeUpdateAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammeProgressTableUpdateSteps()
        );
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammeProgressTableUpdateSteps());
    }
}
