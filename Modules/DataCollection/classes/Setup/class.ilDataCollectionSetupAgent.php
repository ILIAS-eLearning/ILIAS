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
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Refinery\Transformation;

class ilDataCollectionSetupAgent implements Setup\Agent
{
    public function getUpdateObjective(Config $config = null): Objective
    {
        return new ilDataCollectionObjective(new ilDataCollectionDBUpdateSteps9());
    }

    public function getMigrations(): array
    {
        return [
            new ilDataCollectionStorageMigration()
        ];
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new LogicException(self::class . " has no config.");
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
        return new NullObjective();
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [];
    }
}
