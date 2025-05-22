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

namespace ILIAS\Cron\Job\Manager;

use ILIAS\Cron\Job\JobManager;

final readonly class StrictCliJobManager implements JobManager
{
    public function __construct(protected JobManager $job_manager)
    {
    }

    /**
     * @return list<string>
     */
    private function getValidPhpApis(): array
    {
        return [
            'cli'
        ];
    }

    public function runActiveJobs(\ilObjUser $actor): void
    {
        if (\in_array(PHP_SAPI, array_map('strtolower', $this->getValidPhpApis()), true)) {
            $this->job_manager->runActiveJobs($actor);
        }
    }

    public function runJobManual(string $jobId, \ilObjUser $actor): bool
    {
        return $this->job_manager->runJobManual($jobId, $actor);
    }

    public function resetJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor): void
    {
        $this->job_manager->resetJob($job, $actor);
    }

    public function activateJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor, bool $wasManuallyExecuted = false): void
    {
        $this->job_manager->activateJob($job, $actor, $wasManuallyExecuted);
    }

    public function deactivateJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor, bool $wasManuallyExecuted = false): void
    {
        $this->job_manager->deactivateJob($job, $actor, $wasManuallyExecuted);
    }

    public function isJobActive(string $jobId): bool
    {
        return $this->job_manager->isJobActive($jobId);
    }

    public function isJobInactive(string $jobId): bool
    {
        return $this->job_manager->isJobInactive($jobId);
    }

    public function ping(string $jobId): void
    {
        $this->job_manager->ping($jobId);
    }
}
