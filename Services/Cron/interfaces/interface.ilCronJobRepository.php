<?php declare(strict_types=1);

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

interface ilCronJobRepository
{
    public function getJobInstanceById(string $id) : ?ilCronJob;

    public function getJobInstance(
        string $a_id,
        string $a_component,
        string $a_class,
        bool $isCreationContext = false
    ) : ?ilCronJob;

    /**
     * Get cron job configuration/execution data
     * @param array|string|null $id
     * @param bool $withInactiveJobsIncluded
     * @return array<int, array<string, mixed>>
     */
    public function getCronJobData($id = null, bool $withInactiveJobsIncluded = true) : array;

    public function registerJob(string $a_component, string $a_id, string $a_class, ?string $a_path) : void;

    public function unregisterJob(string $a_component, array $a_xml_job_ids) : void;

    public function createDefaultEntry(ilCronJob $job, string $component, string $class, ?string $path) : void;

    /**
     * @param bool $withOnlyActive
     * @return array<int, array{0: ilCronJob, 1: array<string, mixed>}>
     */
    public function getPluginJobs(bool $withOnlyActive = false) : array;

    public function resetJob(ilCronJob $job) : void;

    public function updateJobResult(
        ilCronJob $job,
        ilObjUser $actor,
        ilCronJobResult $result,
        bool $wasManualExecution = false
    ) : void;

    public function updateRunInformation(string $jobId, int $runningTimestamp, int $aliveTimestamp) : void;

    public function updateJobSchedule(ilCronJob $job, ?int $scheduleType, ?int $scheduleValue) : void;

    public function activateJob(ilCronJob $job, ilObjUser $actor, bool $wasManuallyExecuted = false) : void;

    public function deactivateJob(ilCronJob $job, ilObjUser $actor, bool $wasManuallyExecuted = false) : void;

    public function findAll() : ilCronJobCollection;
}
