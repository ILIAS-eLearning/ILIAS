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

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Metrics;

class ilTestQuestionPoolSetupAgent extends NullAgent
{
    public function getUpdateObjective(ILIAS\Setup\Config $config = null): ILIAS\Setup\Objective
    {
        return $this->getObjectiveCollection();
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, $this->getObjectiveCollection());
    }

    private function getObjectiveCollection(): ObjectiveCollection
    {
        return new ObjectiveCollection(
            'Database is updated for Module/TestQuestionPool',
            false,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilTestQuestionPool80DBUpdateSteps()
            ),
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilTestQuestionPool9DBUpdateSteps()
            )
        );
    }
}
