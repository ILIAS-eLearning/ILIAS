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

use ILIAS\Setup\ObjectiveConstructor;
use ILIAS\Setup\Config;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery;
use ILIAS\Setup;
use ILIAS\File\Icon\ilObjFileDefaultIconsObjective;

/**
 * @author       Thibeau Fuhrer <thibeau@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
class ilFileObjectAgent implements Setup\Agent
{
    protected Factory $refinery;

    public function __construct(Factory $refinery)
    {
        $this->refinery = $refinery;
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(Config $config = null): Setup\Objective
    {
        return new \ILIAS\File\Icon\ilObjFileDefaultIconsObjective(true, true);
    }

    public function getUpdateObjective(Config $config = null): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            "",
            true,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilFileObjectDatabaseObjective()
            ),
            new \ILIAS\File\Icon\ilObjFileDefaultIconsObjective(false, false),
            new ilFileObjectSettingsUpdatedObjective(),
            new ilFileObjectRBACDatabase(
                new ilFileObjectRBACDatabaseSteps()
            )
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\ObjectiveCollection(
            'Component File',
            true,
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilFileObjectDatabaseObjective()),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilFileObjectRBACDatabaseSteps())
        );
    }

    public function getMigrations(): array
    {
        return [

        ];
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [
            'resetDefaultIcons' => new ObjectiveConstructor(
                'resets the default suffix specific file icons.',
                function (): \ILIAS\Setup\Objective {
                    return new ilObjFileDefaultIconsObjective(true, false);
                }
            ),
        ];
    }
}
