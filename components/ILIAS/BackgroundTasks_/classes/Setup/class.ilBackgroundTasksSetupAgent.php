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

use ILIAS\Setup\Agent;
use ILIAS\Setup\Agent\HasNoNamedObjective;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;
use ILIAS\Setup\Config;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\ObjectiveCollection;

class ilBackgroundTasksSetupAgent implements Agent
{
    use HasNoNamedObjective;

    public function __construct(protected Factory $refinery)
    {
    }

    /**
     * @inheritdoc
     */
    public function hasConfig(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getArrayToConfigTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(fn($data): \ilBackgroundTasksSetupConfig => new \ilBackgroundTasksSetupConfig(
            $data["type"] ?? \ilBackgroundTasksSetupConfig::TYPE_SYNCHRONOUS,
            $data["max_number_of_concurrent_tasks"] ?? 1
        ));
    }

    /**
     * @inheritdoc
     */
    public function getInstallObjective(?Config $config = null): Objective
    {
        /** @noinspection PhpParamsInspection */
        return new ilBackgroundTasksConfigStoredObjective($config);
    }

    /**
     * @inheritdoc
     */
    public function getUpdateObjective(?Config $config = null): Objective
    {
        if ($config !== null) {
            /** @noinspection PhpParamsInspection */
            return new ilBackgroundTasksConfigStoredObjective($config);
        }
        return new NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    /**
     * @inheritdoc
     */
    public function getStatusObjective(Storage $storage): Objective
    {
        return new ObjectiveCollection(
            'Component BackgroundTasks',
            true,
            new ilBackgroundTasksMetricsCollectedObjective($storage),
            new ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new ilBackgroundTasksDB80())
        );
    }

    /**
     * @inheritDoc
     */
    public function getMigrations(): array
    {
        return [];
    }
}
