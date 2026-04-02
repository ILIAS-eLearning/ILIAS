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

namespace ILIAS\Cron\Job;

interface JobManager
{
    public function runActiveJobs(\ilObjUser $actor): void;

    public function runJobManual(string $id, \ilObjUser $actor): bool;

    public function resetJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor): void;

    public function activateJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor, bool $was_manually_executed = false): void;

    public function deactivateJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor, bool $was_manually_executed = false): void;

    public function isJobActive(string $id): bool;

    public function isJobInactive(string $id): bool;

    public function ping(string $id): void;
}
