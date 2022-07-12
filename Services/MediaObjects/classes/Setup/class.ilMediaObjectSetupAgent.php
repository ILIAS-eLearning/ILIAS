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

/**
 * Richard Klees <richard.klees@concepts-and-training.de>
 */
class ilMediaObjectSetupAgent implements Setup\Agent
{
    use Setup\Agent\HasNoNamedObjective;

    protected Refinery\Factory $refinery;

    public function __construct(
        Refinery\Factory $refinery
    ) {
        $this->refinery = $refinery;
    }

    public function hasConfig() : bool
    {
        return true;
    }

    public function getArrayToConfigTransformation() : Refinery\Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new \ilMediaObjectSetupConfig(
                $data["path_to_ffmpeg"] ?? null
            );
        });
    }

    public function getInstallObjective(Setup\Config $config = null) : Setup\Objective
    {
        $dir_objective = new ilFileSystemComponentDataDirectoryCreatedObjective(
            'mobs',
            ilFileSystemComponentDataDirectoryCreatedObjective::WEBDIR
        );

        /** @var ilMediaObjectSetupConfig $config */
        return new Setup\ObjectiveCollection(
            "Complete objectives from Services/MediaObject",
            false,
            new ilMediaObjectConfigStoredObjective($config),
            $dir_objective
        );
    }

    public function getUpdateObjective(Setup\Config $config = null) : Setup\Objective
    {
        /** @var ilMediaObjectSetupConfig $config */
        if ($config !== null) {
            return new ilMediaObjectConfigStoredObjective($config);
        }
        return new Setup\Objective\NullObjective();
    }

    public function getBuildArtifactObjective() : Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage) : Setup\Objective
    {
        return new ilMediaObjectMetricsCollectedObjective($storage);
    }

    public function getMigrations() : array
    {
        return [];
    }
}
