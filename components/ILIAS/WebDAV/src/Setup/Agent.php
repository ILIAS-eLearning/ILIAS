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

namespace ILIAS\WebDAV\Setup;

use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Config;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\WebDAV\Setup\KeyRotationObjective;
use ILIAS\Setup\Metrics\Storage;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class Agent implements \ILIAS\Setup\Agent
{
    public function getInstallObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'WebDAV',
            true,
            new KeyRotationObjective()
        );
    }

    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'WebDAV',
            true,
            new KeyRotationObjective()
        );
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException('This agent does not support configuration');
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [];
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new NullObjective();
    }

    public function getMigrations(): array
    {
        return [];
    }
}
