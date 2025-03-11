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

use ILIAS\Setup;
use ILIAS\Refinery;

class ilCronJobSetupAgent implements Setup\Agent
{
    public function __construct(
        private readonly array $cronjobs
    ) {
    }

    public function hasConfig(): bool
    {
        return false;
    }

    public function getArrayToConfigTransformation(): Refinery\Transformation
    {
        throw new LogicException('Agent has no config.');
    }

    public function getInstallObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ilCronjobsRegisteredObjective(
            $this->cronjobs
        );
    }

    public function getUpdateObjective(Setup\Config $config = null): Setup\Objective
    {
        return new ilCronjobsRegisteredObjective(
            $this->cronjobs
        );
    }

    public function getBuildObjective(): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getStatusObjective(Setup\Metrics\Storage $storage): Setup\Objective
    {
        return new Setup\Objective\NullObjective();
    }

    public function getMigrations(): array
    {
        return [];
    }

    public function getNamedObjectives(?Setup\Config $config = null): array
    {
        return [
            'registerCronJobs' => new Setup\ObjectiveConstructor(
                'registers cron jobs',
                fn(): Setup\Objective => new ilCronjobsRegisteredObjective(
                    $this->cronjobs
                )
            )
        ];
    }
}
