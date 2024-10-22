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
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;

class ilConditionSetupAgent extends NullAgent
{
    use Setup\Agent\HasNoNamedObjective;

    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Conditions',
            false,
            new ilDatabaseUpdateStepsExecutedObjective(new ilConditionDBUpdateSteps()),
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Conditions',
            true,
            new ilDatabaseUpdateStepsExecutedObjective(new ilConditionDBUpdateSteps()),
        );
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException('Agent has no config.');
    }

    public function getInstallObjective(Config $config = null): Objective
    {
        return new Objective\NullObjective();
    }

    public function getBuildObjective(): Objective
    {
        return new Objective\NullObjective();
    }
}
