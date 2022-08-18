<?php declare(strict_types=1);

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
use ILIAS\Setup\Objective;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Metrics;
use ILIAS\Setup\Config;
use ILIAS\Setup\ObjectiveConstructor;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Setup\NullConfig;

class ilSettingsSetupAgent implements Agent
{
    protected Refinery $refinery;

    public function __construct(Refinery $refinery)
    {
        $this->refinery = $refinery;
    }

    public function hasConfig() : bool
    {
        return false;
    }

    public function getArrayToConfigTransformation() : Transformation
    {
        return $this->refinery->custom()->transformation(static function ($data) : Config {
            return new NullConfig();
        });
    }

    public function getInstallObjective(Config $config = null) : Objective
    {
        return new Objective\NullObjective();
    }

    public function getUpdateObjective(Config $config = null) : Objective
    {
        return new Objective\NullObjective();
    }

    public function getBuildArtifactObjective() : Objective
    {
        return new Objective\NullObjective();
    }

    public function getStatusObjective(Metrics\Storage $storage) : Objective
    {
        return new ilSettingsMetricsCollectedObjective($storage);
    }

    public function getMigrations() : array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null) : array
    {
        return [
            'varchar-to-clob' => new ObjectiveConstructor(
                "Migrate the 'value' field of table 'settings' to a CLOB type",
                static function () : Objective {
                    return new ilSettingsValueDatabaseTypeSwitch(
                        ilSettingsValueDatabaseTypeSwitch::VARCHAR_TO_CLOB
                    );
                }
            ),
            'clob-to-varchar' => new ObjectiveConstructor(
                "Migrate the 'value' field of table 'settings' to a VARCHAR type",
                static function () : Objective {
                    return new ilSettingsValueDatabaseTypeSwitch(
                        ilSettingsValueDatabaseTypeSwitch::CLOB_TO_VARCHAR
                    );
                }
            )
        ];
    }
}
