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

use ILIAS\AccessControl\Setup\AccessControl10DBUpdateSteps;
use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Config;
use ILIAS\Setup\Metrics\Storage;

/**
 * @author  Tim Schmitz <schmitz@leifos.de>
 */
class ilAccessRBACSetupAgent extends NullAgent
{
    /**
     * @inheritdoc
     */
    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new AccessControl10DBUpdateSteps());
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Storage $storage): Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective(
            $storage,
            new AccessControl10DBUpdateSteps()
        );
    }
}
