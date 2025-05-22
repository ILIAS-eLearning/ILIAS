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

interface JobRepository
{
    public function getJobInstanceById(string $id): ?\ILIAS\Cron\CronJob;

    public function getJobInstance(
        string $a_id,
        string $a_component,
        string $a_class,
        bool $isCreationContext = false
    ): ?\ILIAS\Cron\CronJob;

    /**
     * Get cron job configuration/execution data
     * @param list<string>|string|null $id
     * @return list<array<string, mixed>>
     */
    public function getCronJobData($id = null, bool $withInactiveJobsIncluded = true): array;

    public function registerJob(string $a_component, string $a_id, string $a_class, ?string $a_path): void;

    /**
     * @param list<string> $a_xml_job_ids
     */
    public function unregisterJob(string $a_component, array $a_xml_job_ids): void;

    public function createDefaultEntry(\ILIAS\Cron\CronJob $job, string $component, string $class, ?string $path): void;

    /**
     * @return array<string, array{0: \ILIAS\Cron\CronJob, 1: array<string, mixed>}>
     */
    public function getPluginJobs(bool $withOnlyActive = false): array;

    public function resetJob(\ILIAS\Cron\CronJob $job): void;

    public function updateJobResult(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        JobResult $result,
        bool $wasManualExecution = false
    ): void;

    public function updateRunInformation(string $jobId, int $runningTimestamp, int $aliveTimestamp): void;

    public function updateJobSchedule(\ILIAS\Cron\CronJob $job, ?JobScheduleType $scheduleType, ?int $scheduleValue): void;

    public function activateJob(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        bool $wasManuallyExecuted = false
    ): void;

    public function deactivateJob(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        bool $wasManuallyExecuted = false
    ): void;

    public function findAll(): JobCollection;
}
