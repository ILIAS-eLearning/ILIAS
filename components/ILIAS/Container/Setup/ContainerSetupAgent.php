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

namespace ILIAS\Container\Setup;

use ILIAS\Setup\Agent\NullAgent;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;

class ContainerSetupAgent extends NullAgent
{
    use HasNoNamedObjective;

    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Database is updated for ILIAS\Container',
            false,
            new WebFeedCreationDeletedObjective('cat', 'create_feed'),
            new WebFeedCreationDeletedObjective('grp', 'create_feed'),
            new WebFeedCreationDeletedObjective('crs', 'create_feed'),
            new WebFeedCreationDeletedObjective('fold', 'create_feed')
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

    public function getInstallObjective(?Config $config = null): Objective
    {
        return new NullObjective();
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getMigrations(): array
    {
        return [
        ];
    }
}
