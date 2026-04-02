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

namespace ILIAS\Cron\Setup;

use ILIAS\Refinery\Transformation;
use ILIAS\Setup;
use ILIAS\Setup\Config;
use ILIAS\Setup\Metrics\Storage;
use ILIAS\Setup\Objective;
use ILIAS\Setup\Objective\NullObjective;
use ILIAS\Setup\ObjectiveCollection;
use ILIAS\Setup\ObjectiveConstructor;

final class CronJobSetupAgent implements Setup\Agent
{
    /**
     * @param list<\ILIAS\Cron\CronJob> $jobs
     */
    public function __construct(private readonly array $jobs)
    {
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Transformation
    {
        throw new \LogicException('Agent has no config.');
    }

    public function getInstallObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Cron component installation',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(new CronDBUpdateSteps12()),
            new StoreCronJobsInDatabaseObjective($this->jobs),
            new \ilTreeAdminNodeAddedObjective('cron', 'Cron'),
        );
    }

    public function getUpdateObjective(?Config $config = null): Objective
    {
        return new ObjectiveCollection(
            'Cron component update',
            false,
            new \ilDatabaseUpdateStepsExecutedObjective(new CronDBUpdateSteps12()),
            new StoreCronJobsInDatabaseObjective($this->jobs),
            new \ilTreeAdminNodeAddedObjective('cron', 'Cron'),
        );
    }

    public function getBuildObjective(): Objective
    {
        return new NullObjective();
    }

    public function getStatusObjective(Storage $storage): Objective
    {
        return new ObjectiveCollection(
            'Database is updated for component/ILIAS/Cron',
            true,
            new \ilDatabaseUpdateStepsMetricsCollectedObjective($storage, new CronDBUpdateSteps12()),
        );
    }

    public function getMigrations(): array
    {
        return [];
    }

    public function getNamedObjectives(?Config $config = null): array
    {
        return [
            'cron.registerCronJobs' =>
                new ObjectiveConstructor(
                    'Gathers and registers cron jobs',
                    fn(): Objective => new StoreCronJobsInDatabaseObjective($this->jobs)
                )
        ];
    }
}
