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

interface ilCronManager
{
    public function runActiveJobs(ilObjUser $actor): void;

    public function runJobManual(string $jobId, ilObjUser $actor): bool;

    public function resetJob(ilCronJob $job, ilObjUser $actor): void;

    public function activateJob(ilCronJob $job, ilObjUser $actor, bool $wasManuallyExecuted = false): void;

    public function deactivateJob(ilCronJob $job, ilObjUser $actor, bool $wasManuallyExecuted = false): void;

    public function isJobActive(string $jobId): bool;

    public function isJobInactive(string $jobId): bool;

    public function ping(string $jobId): void;
}
