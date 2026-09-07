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

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Config;
use ILIAS\Setup;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Course\Setup\UpdateStepsV12;

class ilCourseSetupAgent extends NullAgent
{
    use Setup\Agent\HasNoNamedObjective;

    public function getUpdateObjective(?ILIAS\Setup\Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Update Course component',
            false,
            new ilDatabaseUpdateStepsExecutedObjective(new ilCourseDBUpdateSteps()),
            new ilDatabaseUpdateStepsExecutedObjective(new UpdateStepsV12())
        );
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilCourseDBUpdateSteps());
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(?Config $config = null): Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getBuildObjective(): Objective
    {
        return new Setup\Objective\NullObjective();
    }
}
