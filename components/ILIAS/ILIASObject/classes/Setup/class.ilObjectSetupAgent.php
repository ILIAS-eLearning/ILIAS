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

namespace ILIAS\Object\Setup;

use ILIAS\Object\Properties\ObjectTypeSpecificProperties\ilObjectTypeSpecificPropertiesArtifactObjective;
use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Metrics;

class ilObjectSetupAgent extends NullAgent
{
    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Database is updated for ILIASObject',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(
                new ilObjectDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new ilObject9DBUpdateSteps()
            )
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new ObjectiveCollection(
            'components/ILIAS/ILIASObject',
            true,
            new \ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new ilObjectDBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new ilObject9DBUpdateSteps()
            )
        );
    }

    public function getMigrations(): array
    {
        return [
            new ilObjectTileImageMigration()
        ];
    }

    public function getBuildObjective(): Objective
    {
        return new ilObjectTypeSpecificPropertiesArtifactObjective();
    }
}
