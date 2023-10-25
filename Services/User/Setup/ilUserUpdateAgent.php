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

class ilUserUpdateAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        $update_user_table = new ilDatabaseUpdateStepsExecutedObjective(
            new ilUserTableUpdateSteps()
        );

        return new Setup\ObjectiveCollection(
            'Database is updated for Services/User',
            false,
            $update_user_table
        );
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Services/User',
            true,
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilUserTableUpdateSteps())
        );
    }
}
