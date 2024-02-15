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

use ILIAS\Setup;
use ILIAS\Refinery;
use ILIAS\Setup\ObjectiveCollection;

/**
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilUserSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new \LogicException("Agent has no config.");
    }

    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        $dir_objective = new ilFileSystemComponentDataDirectoryCreatedObjective(
            'usr_images',
            ilFileSystemComponentDataDirectoryCreatedObjective::WEBDIR
        );

        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/User",
            false,
            $dir_objective
        );
    }

    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ObjectiveCollection(
            'Database is updated for Services/User',
            false,
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilUser8DBUpdateSteps()
            ),
            new ilDatabaseUpdateStepsExecutedObjective(
                new ilUserDB90()
            )
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new ObjectiveCollection(
            'components/ILIAS/User',
            true,
            new ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new ilUser8DBUpdateSteps()
            ),
            new ilDatabaseUpdateStepsMetricsCollectedObjective(
                $storage,
                new ilUserDB90()
            )
        );
    }

    public function getMigrations(): array
    {
        return [
            new ilUserProfilePictureMigration(),
            new ilUpdateNewAccountMailTemplatesForMustache()
        ];
    }
}
