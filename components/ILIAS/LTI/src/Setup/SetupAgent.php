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

namespace ILIAS\LTI\Setup;

use ILIAS\Setup;
use ILIAS\Refinery;

class SetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(?Setup\Config $config = null): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getUpdateObjective(?Setup\Config $config = null): Setup\Objective
    {
        // ilLTIConsumerDatabaseUpdateSteps and ilLTIDatabaseUpdateSteps keep their class names:
        // il_db_steps tracks executed steps by class, so installations updated from ILIAS 11 do not run them again.
        return new Setup\ObjectiveCollection(
            'LTI',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(new \ilLTIConsumerDatabaseUpdateSteps()),
            new \ilDatabaseUpdateStepsExecutedObjective(new \ilLTIDatabaseUpdateSteps()),
            new \ilDatabaseUpdateStepsExecutedObjective(new DatabaseUpdateSteps())
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'LTI',
            true,
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLTIConsumerDatabaseUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new \ilLTIDatabaseUpdateSteps()),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new DatabaseUpdateSteps())
        );
    }

    /**
     * @return mixed[]
     */
    public function getMigrations(): array
    {
        return [];
    }
}
