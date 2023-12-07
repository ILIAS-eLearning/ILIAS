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

namespace ILIAS\Test\Setup;

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Refinery\Transformation;

class ilTestSetupAgent extends NullAgent
{
    use HasNoNamedObjective;

    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Database is updated for ILIAS\Test',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(
                new Test9DBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsExecutedObjective(
                new Test10DBUpdateSteps()
            )
        );
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new ObjectiveCollection(
            'ILIAS\Test',
            true,
            new \ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new Test9DBUpdateSteps()
            ),
            new \ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new Test10DBUpdateSteps()
            )
        );
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(Config $config = null): Objective
    {
        return new NullObjective();
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getMigrations(): array
    {
        return [];
    }
}
