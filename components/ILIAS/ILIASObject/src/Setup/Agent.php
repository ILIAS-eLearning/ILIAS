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

namespace ILIAS\ILIASObject\Setup;

use ILIAS\ILIASObject\Properties\ObjectTypeSpecificProperties\ArtifactObjective;
use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;

class Agent extends NullAgent
{
    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new \ilDatabaseUpdateStepsExecutedObjective(
            new DBUpdateSteps11()
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective(
            $storage,
            new ilObjectDBUpdateSteps()
        );
    }

    public function getBuildObjective(): Objective
    {
        return new ArtifactObjective();
    }

    public function getMigrations(): array
    {
        return [
            new MigrateTranslations()
        ];
    }
}
