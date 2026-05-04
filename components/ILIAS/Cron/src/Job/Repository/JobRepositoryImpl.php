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

namespace ILIAS\Cron\Job\Repository;

use ILIAS\Cron\CronJobRegistry;
use ILIAS\Cron\Job\JobRepository;
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobEntity;
use ILIAS\Cron\CronJob;

readonly class JobRepositoryImpl implements JobRepository
{
    public function __construct(
        private CronJobRegistry $registry,
        private \ilDBInterface $db,
        private \ilSetting $setting,
        private \ilLogger $logger,
        private \ilComponentRepository $component_repository,
        private \ilComponentFactory $component_factory,
        private \ILIAS\Language\Language $language,
        private \ILIAS\Logging\LoggerFactory $logger_factory,
    ) {
    }

    public function getJobInstanceById(string $id): ?CronJob
    {
        $jobs_data = $this->getCronJobData($id);
        if ($jobs_data !== [] && $jobs_data[0]['job_id'] === $id) {
            return $this->getJobInstance(
                $jobs_data[0]['job_id'],
                $jobs_data[0]['component'],
                $jobs_data[0]['class'],
            );
        }

        $this->logger->info('CRON - job ' . $id . ' seems invalid or is inactive');

        return null;
    }

    public function getJobInstance(
        string $id,
        string $component,
        string $class,
    ): ?CronJob {
        if (class_exists($class)) {
            $job = new $class(
                $component,
                $this->language,
                $this->logger_factory
            );

            if ($job instanceof CronJob && $job->getId() === $id) {
                return $job;
            }
        }

        return null;
    }

    public function getCronJobData(null|array|string $id = null, bool $with_inactive_jobs_included = true): array
    {
        if ($id && !\is_array($id)) {
            $id = [$id];
        }

        if ($id && !array_is_list($id)) {
            throw new \InvalidArgumentException('Job IDs must be provided as an array or a single string');
        }

        $query = 'SELECT * FROM cron_job';
        $where = [];
        if ($id) {
            $where[] = $this->db->in('job_id', $id, false, \ilDBConstants::T_TEXT);
        }
        if (!$with_inactive_jobs_included) {
            $where[] = 'job_status = ' . $this->db->quote(1, \ilDBConstants::T_INTEGER);
        }
        if ($where !== []) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        // TODO: discuss job execution order
        $query .= ' ORDER BY job_id';

        $res = $this->db->query($query);
        $job_data = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $job_data[] = $row;
        }

        return $job_data;
    }

    public function syncJobsFromRegistry(): void
    {
        $keep_ids = [];
        foreach ($this->registry->getAllJobs() as $job) {
            $this->registerJob($job);
            $keep_ids[] = $job->getId();
        }

        $this->deleteJobsNotIn($keep_ids);
    }

    private function registerJob(CronJob $job): void
    {
        if (!$this->db->tableExists('cron_job')) {
            return;
        }

        $this->createDefaultEntry($job);
    }

    /**
     * @param list<string> $keep_job_ids
     */
    private function deleteJobsNotIn(array $keep_job_ids): void
    {
        if (!$this->db->tableExists('cron_job')) {
            return;
        }

        if ($keep_job_ids === []) {
            $this->db->manipulate('DELETE FROM cron_job');
            return;
        }

        $this->db->manipulate(
            'DELETE FROM cron_job WHERE ' . $this->db->in('job_id', $keep_job_ids, true, \ilDBConstants::T_TEXT)
        );
    }

    private function createDefaultEntry(CronJob $job): void
    {
        $query = 'SELECT job_id, schedule_type, component, class FROM cron_job' .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT);
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        $job_id = $row['job_id'] ?? null;
        $job_exists = ($job_id === $job->getId());
        $schedule_type_value = $row['schedule_type'] ?? null;
        $schedule_type = is_numeric($schedule_type_value) ? JobScheduleType::tryFrom(
            (int) $schedule_type_value
        ) : null;
        $component = $job->getComponent();
        $class = $job::class;

        if ($job_exists && ($row['component'] !== $component || $row['class'] !== $class)) {
            $this->db->manipulateF(
                'UPDATE cron_job SET component = %s, class = %s WHERE job_id = %s',
                [\ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT, \ilDBConstants::T_TEXT],
                [$component, $class, $job->getId()]
            );
        }

        // new job
        if (!$job_exists) {
            $query = 'INSERT INTO cron_job (job_id, component, class)' .
                ' VALUES (' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT) . ', ' .
                $this->db->quote($component, \ilDBConstants::T_TEXT) . ', ' .
                $this->db->quote($class, \ilDBConstants::T_TEXT) . ')';
            $this->db->manipulate($query);

            $this->logger->info('Cron XML - Job ' . $job->getId() . ' in class ' . $class . ' added.');

            // only if flexible
            $this->updateJobSchedule(
                $job,
                $job->getDefaultScheduleType(),
                $job->getDefaultScheduleValue()
            );

            if ($job->hasAutoActivation()) {
                $this->activateJob($job, new \DateTimeImmutable('@' . time()));
                $job->activationWasToggled($this->db, $this->setting, true);
            } else {
                // to overwrite dependent settings
                $job->activationWasToggled($this->db, $this->setting, false);
            }
        } elseif ($schedule_type === null && $job->hasFlexibleSchedule()) {
            // existing job - but schedule is flexible now
            $this->updateJobSchedule(
                $job,
                $job->getDefaultScheduleType(),
                $job->getDefaultScheduleValue()
            );
        } elseif ($schedule_type !== null && !$job->hasFlexibleSchedule()) {
            // existing job - but schedule is not flexible anymore
            $this->updateJobSchedule($job, null, null);
        }
    }

    public function resetJob(CronJob $job): void
    {
        $this->db->manipulate(
            'UPDATE cron_job' .
            ' SET running_ts = ' . $this->db->quote(0, \ilDBConstants::T_INTEGER) .
            ' , alive_ts = ' . $this->db->quote(0, \ilDBConstants::T_INTEGER) .
            ' , job_result_ts = ' . $this->db->quote(0, \ilDBConstants::T_INTEGER) .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT)
        );
    }

    public function updateJobResult(
        CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        \ILIAS\Cron\Job\JobResult $result,
        bool $was_manual_execution = false
    ): void {
        $user_id = $was_manual_execution ? $actor->getId() : 0;

        $query = 'UPDATE cron_job SET ' .
            ' job_result_status = ' . $this->db->quote($result->getStatus(), \ilDBConstants::T_INTEGER) .
            ' , job_result_user_id = ' . $this->db->quote($user_id, \ilDBConstants::T_INTEGER) .
            ' , job_result_code = ' . $this->db->quote($result->getCode(), \ilDBConstants::T_INTEGER) .
            ' , job_result_message = ' . $this->db->quote($result->getMessage(), \ilDBConstants::T_INTEGER) .
            ' , job_result_type = ' . $this->db->quote((int) $was_manual_execution, \ilDBConstants::T_INTEGER) .
            ' , job_result_ts = ' . $this->db->quote($when->getTimestamp(), \ilDBConstants::T_INTEGER) .
            ' , job_result_dur = ' . $this->db->quote($result->getDuration() * 1000, \ilDBConstants::T_INTEGER) .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    public function updateRunInformation(string $id, int $running_timestamp, int $alive_timestamp): void
    {
        $this->db->manipulate(
            'UPDATE cron_job SET' .
            ' running_ts = ' . $this->db->quote($running_timestamp, \ilDBConstants::T_INTEGER) .
            ' , alive_ts = ' . $this->db->quote($alive_timestamp, \ilDBConstants::T_INTEGER) .
            ' WHERE job_id = ' . $this->db->quote($id, \ilDBConstants::T_TEXT)
        );
    }

    public function updateJobSchedule(CronJob $job, ?JobScheduleType $schedule_type, ?int $schedule_value): void
    {
        if (
            $schedule_type === null ||
            ($job->hasFlexibleSchedule() && \in_array($schedule_type, $job->getValidScheduleTypes(), true))
        ) {
            $query = 'UPDATE cron_job SET ' .
                ' schedule_type = ' . $this->db->quote($schedule_type?->value, \ilDBConstants::T_INTEGER) .
                ' , schedule_value = ' . $this->db->quote($schedule_value, \ilDBConstants::T_INTEGER) .
                ' WHERE job_id = ' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT);
            $this->db->manipulate($query);
        }
    }

    public function activateJob(
        CronJob $job,
        \DateTimeImmutable $when,
        ?\ilObjUser $actor = null,
        bool $was_manually_executed = false
    ): void {
        $usrId = 0;
        if ($was_manually_executed && $actor instanceof \ilObjUser) {
            $usrId = $actor->getId();
        }

        $query = 'UPDATE cron_job SET ' .
            ' job_status = ' . $this->db->quote(1, \ilDBConstants::T_INTEGER) .
            ' , job_status_user_id = ' . $this->db->quote($usrId, \ilDBConstants::T_INTEGER) .
            ' , job_status_type = ' . $this->db->quote($was_manually_executed, \ilDBConstants::T_INTEGER) .
            ' , job_status_ts = ' . $this->db->quote($when->getTimestamp(), \ilDBConstants::T_INTEGER) .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    public function deactivateJob(
        CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        bool $was_manually_executed = false
    ): void {
        $usrId = $was_manually_executed ? $actor->getId() : 0;

        $query = 'UPDATE cron_job SET ' .
            ' job_status = ' . $this->db->quote(0, \ilDBConstants::T_INTEGER) .
            ' , job_result_status = ' . $this->db->quote(null, \ilDBConstants::T_TEXT) .
            ' , job_result_message = ' . $this->db->quote(null, \ilDBConstants::T_TEXT) .
            ' , job_result_type = ' . $this->db->quote(null, \ilDBConstants::T_TEXT) .
            ' , job_result_code = ' . $this->db->quote(null, \ilDBConstants::T_TEXT) .
            ' , job_status_user_id = ' . $this->db->quote($usrId, \ilDBConstants::T_INTEGER) .
            ' , job_status_type = ' . $this->db->quote($was_manually_executed, \ilDBConstants::T_INTEGER) .
            ' , job_status_ts = ' . $this->db->quote($when->getTimestamp(), \ilDBConstants::T_INTEGER) .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), \ilDBConstants::T_TEXT);
        $this->db->manipulate($query);
    }

    public function findAll(): \ILIAS\Cron\Job\JobCollection
    {
        $collection = new \ILIAS\Cron\Job\Collection\JobEntities();

        foreach ($this->registry->getAllJobs() as $job) {
            $job_data = $this->getCronJobData($job->getId());
            $entity = new JobEntity($job, array_shift($job_data));
            $collection->add($entity);
        }

        return $collection;
    }

    public function getEntityById(string $id): ?JobEntity
    {
        $jobs = $this->findAll();

        return $jobs->filter(
            static fn(JobEntity $entity): bool => $entity->getJobId() === $id
        )->toArray()[0] ?? null;
    }
}
