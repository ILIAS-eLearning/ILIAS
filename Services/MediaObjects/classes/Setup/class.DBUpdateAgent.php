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

namespace ILIAS\MediaObjects\Setup;

use ILIAS\Setup;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class DBUpdateAgent extends Setup\Agent\NullAgent
{
    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        return new \ilDatabaseUpdateStepsExecutedObjective(new ilMediaObjectsDBUpdateSteps());
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilMediaObjectsDBUpdateSteps());
    }
}
