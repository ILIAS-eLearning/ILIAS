<?php

declare(strict_types=1);

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

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Config;
use ILIAS\Setup;
use ILIAS\Refinery\Transformation;
use ILIAS\Test\Setup\ilManScoringSettingsToOwnDbTableMigration;

class ilTestSetupAgent extends NullAgent
{
    use Setup\Agent\HasNoNamedObjective;

    public function getUpdateObjective(ILIAS\Setup\Config $config = null) : Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilTest8DBUpdateSteps());
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTest8DBUpdateSteps());
    }

    public function hasConfig() : bool
    {
        return false;
    }

    public function getArrayToConfigTransformation() : Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(Config $config = null) : Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getBuildArtifactObjective() : Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getMigrations() : array
    {
        return [
            new ilManScoringSettingsToOwnDbTableMigration()
        ];
    }
}
