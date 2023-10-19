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

declare(strict_types=1);

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

class ilStudyProgrammeUpdateAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        $update_progresses = new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammeProgressTableUpdateSteps()
        );
        $update_assignments = new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammeAssignmentTableUpdateSteps()
        );
        $update_settings = new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammeSettingsTableUpdateSteps()
        );
        $update_auto_category = new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammeAutoCategoryTableUpdateSteps()
        );
        $enable_pc_statusinfo = new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammePCStatusInfoUpdateSteps()
        );
        $udf_definitions = new ilDatabaseUpdateStepsExecutedObjective(
            new ilStudyProgrammeUDFDefinitionUpdateSteps()
        );

        return new Setup\ObjectiveCollection(
            'Database is updated for Module/Studyprogramme',
            false,
            $update_progresses,
            $update_assignments,
            $update_settings,
            $update_auto_category,
            $enable_pc_statusinfo,
            $udf_definitions
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new Setup\ObjectiveCollection(
            'Module/Studyprogramme',
            true,
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammeProgressTableUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammeAssignmentTableUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammeSettingsTableUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammeAutoCategoryTableUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammePCStatusInfoUpdateSteps()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilStudyProgrammeUDFDefinitionUpdateSteps())
        );
    }
}
