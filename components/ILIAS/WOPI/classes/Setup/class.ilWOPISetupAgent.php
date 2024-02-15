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

use ILIAS\Setup\Agent;
use ILIAS\Setup\Objective;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Config;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ilWOPISetupAgent implements Agent
{
    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getInstallObjective(Config $config = null): Objective
    {
        return new \ilDatabaseUpdateStepsExecutedObjective(new ilWOPIDB90());
    }

    public function getUpdateObjective(Config $config = null): Objective
    {
        return new \ilDatabaseUpdateStepsExecutedObjective(new ilWOPIDB90());
    }

    public function getBuildObjective(): Objective
    {
        return new Objective\NullObjective();
    }

    public function getStatusObjective(Metrics\Storage $storage): Objective
    {
        return new Objective\NullObjective();
    }

    public function getMigrations(): array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [];
    }

}
