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

namespace ILIAS\Logging\Setup;

use ILIAS\Setup\Agent as AgentInterface;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\Config as ConfigInterface;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;
use ilDatabaseUpdateStepsExecutedObjective;
use ILIAS\Logging\Setup\Steps\DBUpdateSteps12;

class Agent implements AgentInterface
{
    use HasNoNamedObjective;

    protected Factory $refinery;

    public function __construct(Factory $refinery)
    {
        $this->refinery = $refinery;
    }

    public function hasConfig(): bool
    {
        return true;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(function ($data) {
            return new Config(
                $data["enable"] ?? false,
                $data["path_to_logfile"] ?? null,
                $data["default_level"] ?? null,
                $data["errorlog_dir"] ?? null,
            );
        });
    }

    public function getInstallObjective(?ConfigInterface $config = null): Objective
    {
        return new ConfigStoredObjective($config);
    }

    public function getUpdateObjective(?ConfigInterface $config = null): Objective
    {
        $objective = new NullObjective();
        if ($config !== null) {
            $objective = new ConfigStoredObjective($config);
        }
        return new ObjectiveCollection(
            'Update of ILIAS\Logging',
            false,
            $objective,
            new DefaultLevelMigratedObjective(),
            new ilDatabaseUpdateStepsExecutedObjective(
                new DBUpdateSteps12()
            )
        );
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new ObjectiveCollection(
            'Component ILIAS\Logging',
            true,
            new MetricsCollectedObjective($storage)
        );
    }

    public function getMigrations(): array
    {
        return [];
    }
}
