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
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveCollection;

class ilTrackingSetupAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Config $config = null): Setup\Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Tracking',
            true,
            new ilDatabaseUpdateStepsExecutedObjective(new ilTrackingUpdateSteps9()),
            new ilDatabaseUpdateStepsExecutedObjective(new ilTrackingUpdateSteps10())
        );
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ObjectiveCollection(
            'Database update status for component/ILIAS/Tracking',
            true,
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTrackingUpdateSteps9()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTrackingUpdateSteps10())
        );
    }
}
