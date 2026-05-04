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

use ILIAS\Cron\Job\Schedule\JobScheduleType;

/**
 * @phpstan-type CronJobRecord array{
 *     job_id: string,
 *     component: string|null,
 *     schedule_type: int|null,
 *     schedule_value: int|null,
 *     job_status: int|null,
 *     job_status_user_id: int|null,
 *     job_status_type: int|null,
 *     job_status_ts: int|null,
 *     job_result_status: int|null,
 *     job_result_user_id: int|null,
 *     job_result_code: string|null,
 *     job_result_message: string|null,
 *     job_result_type: int|null,
 *     job_result_ts: int|null,
 *     class: string|null,
 *     running_ts: int|null,
 *     job_result_dur: int|null,
 *     alive_ts: int|null
 * }
 */
interface JobRepository
{
    public function getJobInstanceById(string $id): ?\ILIAS\Cron\CronJob;

    public function getJobInstance(
        string $id,
        string $component,
        string $class,
    ): ?\ILIAS\Cron\CronJob;

    /**
     * Get cron job configuration/execution data
     * @param null|list<string>|string $id
     * @return list<CronJobRecord>
     */
    public function getCronJobData(null|array|string $id = null, bool $with_inactive_jobs_included = true): array;

    /**
     * Ensures all jobs from the component registry exist in persistence, also removes obsolete jobs.
     */
    public function syncJobsFromRegistry(): void;

    public function resetJob(\ILIAS\Cron\CronJob $job): void;

    public function updateJobResult(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        JobResult $result,
        bool $was_manual_execution = false
    ): void;

    public function updateRunInformation(string $id, int $running_timestamp, int $alive_timestamp): void;

    public function updateJobSchedule(
        \ILIAS\Cron\CronJob $job,
        ?JobScheduleType $schedule_type,
        ?int $schedule_value
    ): void;

    public function activateJob(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        bool $was_manually_executed = false
    ): void;

    public function deactivateJob(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        bool $was_manually_executed = false
    ): void;

    public function findAll(): JobCollection;

    public function getEntityById(string $id): ?JobEntity;
}
