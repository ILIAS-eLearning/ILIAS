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

//namespace ILIAS\Exercise\Setup;

use ILIAS\Setup;
use ILIAS\Exercise\Setup\ilExerciseDBUpdateSteps;
use ILIAS\Setup\ObjectiveCollection;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExerciseSetupAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Exercise',
            false,
            new ilDatabaseUpdateStepsExecutedObjective(new ilExerciseDBUpdateSteps()),
        );
    }

    public function getMigrations(): array
    {
        return [
            new ilExerciseInstructionFilesMigration(),
            new ilExerciseSampleSolutionMigration(),
            new ilExerciseTutorFeedbackFileMigration(),
            new ilExerciseTutorTeamFeedbackFileMigration(),
            new ilExerciseSubmissionMigration(),
            new ilExercisePeerFeedbackMigration()
        ];
    }
}
