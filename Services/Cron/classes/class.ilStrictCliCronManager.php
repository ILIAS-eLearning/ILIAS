<?php

declare(strict_types=1);

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

class ilStrictCliCronManager implements ilCronManager
{
    protected ilCronManager $cronManager;

    public function __construct(ilCronManager $cronManager)
    {
        $this->cronManager = $cronManager;
    }

    /**
     * @return string[]
     */
    private function getValidPhpApis(): array
    {
        return [
            'cli'
        ];
    }

    public function runActiveJobs(ilObjUser $actor): void
    {
        if (in_array(PHP_SAPI, array_map('strtolower', $this->getValidPhpApis()), true)) {
            $this->cronManager->runActiveJobs($actor);
        }
    }

    public function runJobManual(string $jobId, ilObjUser $actor): bool
    {
        return $this->cronManager->runJobManual($jobId, $actor);
    }

    public function resetJob(ilCronJob $job, ilObjUser $actor): void
    {
        $this->cronManager->resetJob($job, $actor);
    }

    public function activateJob(ilCronJob $job, ilObjUser $actor, bool $wasManuallyExecuted = false): void
    {
        $this->cronManager->activateJob($job, $actor, $wasManuallyExecuted);
    }

    public function deactivateJob(ilCronJob $job, ilObjUser $actor, bool $wasManuallyExecuted = false): void
    {
        $this->cronManager->deactivateJob($job, $actor, $wasManuallyExecuted);
    }

    public function isJobActive(string $jobId): bool
    {
        return $this->cronManager->isJobActive($jobId);
    }

    public function isJobInactive(string $jobId): bool
    {
        return $this->cronManager->isJobInactive($jobId);
    }

    public function ping(string $jobId): void
    {
        $this->cronManager->ping($jobId);
    }
}
