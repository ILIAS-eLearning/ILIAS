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

class ilTreeSetupAgent implements Agent
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
        return new ilTreeMetricsCollectedObjective($storage);
    }

    public function getMigrations() : array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null) : array
    {
        return [
            'mp-to-ns' => new ObjectiveConstructor(
                'Migrate the ILIAS repository tree from Materialized Path to Nested Set',
                static function () : Objective {
                    return new ilTreeImplementationSwitch(
                        ilTreeImplementationSwitch::MATERIALIZED_PATH_TO_NESTED_SET
                    );
                }
            ),
            'ns-to-mp' => new ObjectiveConstructor(
                'Migrate the ILIAS repository tree from Nested to Materialized Path',
                static function () : Objective {
                    return new ilTreeImplementationSwitch(
                        ilTreeImplementationSwitch::NESTED_SET_TO_MATERIALIZED_PATH
                    );
                }
            )
        ];
    }
}
