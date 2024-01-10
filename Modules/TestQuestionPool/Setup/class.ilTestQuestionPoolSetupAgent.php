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

use ILIAS\Setup\Agent;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Refinery\Transformation;

class ilTestQuestionPoolSetupAgent implements Agent
{
    use HasNoNamedObjective;

    public function getUpdateObjective(ILIAS\Setup\Config $config = null): ILIAS\Setup\Objective
    {
        return new ilDatabaseUpdateStepsExecutedObjective(new ilTestQuestionPool80DBUpdateSteps());
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException('Agent has no config.');
    }

    public function getInstallObjective(Config $config = null): Objective
    {
        return new NullObjective();
    }

    public function getBuildArtifactObjective(): Objective
    {
        return new NullObjective();
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilTestQuestionPool80DBUpdateSteps());
    }

    public function getMigrations(): array
    {
        return [
            new ilFixMissingQuestionDuplicationMigration()
        ];
    }
}
