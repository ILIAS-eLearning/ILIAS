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

use ILIAS\Cron\Job\JobRepository;
use ILIAS\Cron\Job\Schedule\JobScheduleType;

readonly class JobRepositoryImpl implements JobRepository
{
    private const string TYPE_PLUGINS = 'Plugins';

    public function __construct(
        private \ilDBInterface $db,
        private \ilSetting $setting,
        private \ilLogger $logger,
        private \ilComponentRepository $component_repository,
        private \ilComponentFactory $component_factory
    ) {
    }

    public function getJobInstanceById(string $id): ?\ILIAS\Cron\CronJob
    {
        // plugin
        if (str_starts_with($id, 'pl__')) {
            $parts = explode('__', $id);
            $pl_name = $parts[1];
            $job_id = $parts[2];

            foreach ($this->component_repository->getPlugins() as $pl) {
                if ($pl->getName() !== $pl_name || !$pl->isActive()) {
                    continue;
                }

                $plugin = $this->component_factory->getPlugin($pl->getId());
                if (!$plugin instanceof \ILIAS\Cron\Job\JobProvider) {
                    continue;
                }

                try {
                    $job = $plugin->getCronJobInstance($job_id);

                    // should never happen but who knows...
                    $jobs_data = $this->getCronJobData($job_id);
                    if ($jobs_data === []) {
                        // as job is not 'imported' from xml
                        $this->createDefaultEntry($job, $pl_name, self::TYPE_PLUGINS, '');
                    }

                    return $job;
                } catch (\OutOfBoundsException) {
                    // Maybe a job was removed from plugin, renamed etc.
                }
                break;
            }
        } else {
            $jobs_data = $this->getCronJobData($id);
            if ($jobs_data !== [] && $jobs_data[0]['job_id'] === $id) {
                return $this->getJobInstance(
                    $jobs_data[0]['job_id'],
                    $jobs_data[0]['component'],
                    $jobs_data[0]['class']
                );
            }
        }

        $this->logger->info('CRON - job ' . $id . ' seems invalid or is inactive');

        return null;
    }

    public function getJobInstance(
        string $a_id,
        string $a_component,
        string $a_class,
        bool $isCreationContext = false
    ): ?\ILIAS\Cron\CronJob {
        if (class_exists($a_class)) {
            if ($isCreationContext) {
                $refl = new \ReflectionClass($a_class);
                $job = $refl->newInstanceWithoutConstructor();
            } else {
                $job = new $a_class();
            }

            if ($job instanceof \ILIAS\Cron\CronJob && $job->getId() === $a_id) {
                return $job;
            }
        }

        return null;
    }

    public function getCronJobData($id = null, bool $withInactiveJobsIncluded = true): array
    {
        $jobData = [];

        if ($id && !\is_array($id)) {
            $id = [$id];
        }

        $query = 'SELECT * FROM cron_job';
        $where = [];
        if ($id) {
            $where[] = $this->db->in('job_id', $id, false, \ilDBConstants::T_TEXT);
        } else {
            $where[] = 'class != ' . $this->db->quote(self::TYPE_PLUGINS, \ilDBConstants::T_TEXT);
        }
        if (!$withInactiveJobsIncluded) {
            $where[] = 'job_status = ' . $this->db->quote(1, \ilDBConstants::T_INTEGER);
        }
        if ($where !== []) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        // :TODO: discuss job execution order
        $query .= ' ORDER BY job_id';

        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $jobData[] = $row;
        }

        return $jobData;
    }

    public function registerJob(
        string $a_component,
        string $a_id,
        string $a_class,
        ?string $a_path
    ): void {
        if (!$this->db->tableExists('cron_job')) {
            return;
        }

        $job = $this->getJobInstance($a_id, $a_component, $a_class, true);
        if ($job) {
            $this->createDefaultEntry($job, $a_component, $a_class, $a_path);
        }
    }

    public function unregisterJob(string $a_component, array $a_xml_job_ids): void
    {
        if (!$this->db->tableExists('cron_job')) {
            return;
        }

        $jobs = [];
        $query = 'SELECT job_id FROM cron_job WHERE component = ' . $this->db->quote($a_component, 'text');
        $res = $this->db->query($query);
        while ($row = $this->db->fetchAssoc($res)) {
            $jobs[] = $row['job_id'];
        }

        if ($jobs !== []) {
            if ($a_xml_job_ids !== []) {
                foreach ($jobs as $job_id) {
                    if (!\in_array($job_id, $a_xml_job_ids, true)) {
                        $this->db->manipulate(
                            'DELETE FROM cron_job' .
                            ' WHERE component = ' . $this->db->quote($a_component, 'text') .
                            ' AND job_id = ' . $this->db->quote($job_id, 'text')
                        );
                    }
                }
            } else {
                $this->db->manipulate(
                    'DELETE FROM cron_job WHERE component = ' . $this->db->quote($a_component, 'text')
                );
            }
        }
    }

    public function createDefaultEntry(
        \ILIAS\Cron\CronJob $job,
        string $component,
        string $class,
        ?string $path
    ): void {
        $query = 'SELECT job_id, schedule_type, component, class, path FROM cron_job' .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), 'text');
        $res = $this->db->query($query);
        $row = $this->db->fetchAssoc($res);
        $job_id = $row['job_id'] ?? null;
        $job_exists = ($job_id === $job->getId());
        $schedule_type_value = $row['schedule_type'] ?? null;
        $schedule_type = is_numeric($schedule_type_value) ? JobScheduleType::tryFrom(
            (int) $schedule_type_value
        ) : null;

        if (
            $job_exists && (
                $row['component'] !== $component ||
                $row['class'] !== $class ||
                $row['path'] !== $path
            )
        ) {
            $this->db->manipulateF(
                'UPDATE cron_job SET component = %s, class = %s, path = %s WHERE job_id = %s',
                ['text', 'text', 'text', 'text'],
                [$component, $class, $path, $job->getId()]
            );
        }

        // new job
        if (!$job_exists) {
            $query = 'INSERT INTO cron_job (job_id, component, class, path)' .
                ' VALUES (' . $this->db->quote($job->getId(), 'text') . ', ' .
                $this->db->quote($component, 'text') . ', ' .
                $this->db->quote($class, 'text') . ', ' .
                $this->db->quote($path, 'text') . ')';
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

    public function getPluginJobs(bool $withOnlyActive = false): array
    {
        $res = [];
        foreach ($this->component_repository->getPlugins() as $pl) {
            if (!$pl->isActive()) {
                continue;
            }

            $plugin = $this->component_factory->getPlugin($pl->getId());

            if (!$plugin instanceof \ILIAS\Cron\Job\JobProvider) {
                continue;
            }

            foreach ($plugin->getCronJobInstances() as $job) {
                $jobs_data = $this->getCronJobData($job->getId());
                $job_data = $jobs_data[0] ?? null;
                if (!\is_array($job_data) || $job_data === []) {
                    // as job is not "imported" from xml
                    $this->createDefaultEntry($job, $plugin->getPluginName(), self::TYPE_PLUGINS, '');
                }

                $jobs_data = $this->getCronJobData($job->getId());
                $job_data = $jobs_data[0];

                // #17941
                if (!$withOnlyActive || (int) $job_data['job_status'] === 1) {
                    $res[$job->getId()] = [$job, $job_data];
                }
            }
        }

        return $res;
    }

    public function resetJob(\ILIAS\Cron\CronJob $job): void
    {
        $this->db->manipulate(
            'UPDATE cron_job' .
            ' SET running_ts = ' . $this->db->quote(0, 'integer') .
            ' , alive_ts = ' . $this->db->quote(0, 'integer') .
            ' , job_result_ts = ' . $this->db->quote(0, 'integer') .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), 'text')
        );
    }

    public function updateJobResult(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        \ILIAS\Cron\Job\JobResult $result,
        bool $wasManualExecution = false
    ): void {
        $user_id = $wasManualExecution ? $actor->getId() : 0;

        $query = 'UPDATE cron_job SET ' .
            ' job_result_status = ' . $this->db->quote($result->getStatus(), 'integer') .
            ' , job_result_user_id = ' . $this->db->quote($user_id, 'integer') .
            ' , job_result_code = ' . $this->db->quote($result->getCode(), 'text') .
            ' , job_result_message = ' . $this->db->quote($result->getMessage(), 'text') .
            ' , job_result_type = ' . $this->db->quote((int) $wasManualExecution, 'integer') .
            ' , job_result_ts = ' . $this->db->quote($when->getTimestamp(), 'integer') .
            ' , job_result_dur = ' . $this->db->quote($result->getDuration() * 1000, 'integer') .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), 'text');
        $this->db->manipulate($query);
    }

    public function updateRunInformation(string $jobId, int $runningTimestamp, int $aliveTimestamp): void
    {
        $this->db->manipulate(
            'UPDATE cron_job SET' .
            ' running_ts = ' . $this->db->quote($runningTimestamp, 'integer') .
            ' , alive_ts = ' . $this->db->quote($aliveTimestamp, 'integer') .
            ' WHERE job_id = ' . $this->db->quote($jobId, 'text')
        );
    }

    public function updateJobSchedule(\ILIAS\Cron\CronJob $job, ?JobScheduleType $scheduleType, ?int $scheduleValue): void
    {
        if (
            $scheduleType === null ||
            ($job->hasFlexibleSchedule() && \in_array($scheduleType, $job->getValidScheduleTypes(), true))
        ) {
            $query = 'UPDATE cron_job SET ' .
                ' schedule_type = ' . $this->db->quote($scheduleType?->value, 'integer') .
                ' , schedule_value = ' . $this->db->quote($scheduleValue, 'integer') .
                ' WHERE job_id = ' . $this->db->quote($job->getId(), 'text');
            $this->db->manipulate($query);
        }
    }

    public function activateJob(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        ?\ilObjUser $actor = null,
        bool $wasManuallyExecuted = false
    ): void {
        $usrId = 0;
        if ($wasManuallyExecuted && $actor instanceof \ilObjUser) {
            $usrId = $actor->getId();
        }

        $query = 'UPDATE cron_job SET ' .
            ' job_status = ' . $this->db->quote(1, 'integer') .
            ' , job_status_user_id = ' . $this->db->quote($usrId, 'integer') .
            ' , job_status_type = ' . $this->db->quote($wasManuallyExecuted, 'integer') .
            ' , job_status_ts = ' . $this->db->quote($when->getTimestamp(), 'integer') .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), 'text');
        $this->db->manipulate($query);
    }

    public function deactivateJob(
        \ILIAS\Cron\CronJob $job,
        \DateTimeImmutable $when,
        \ilObjUser $actor,
        bool $wasManuallyExecuted = false
    ): void {
        $usrId = $wasManuallyExecuted ? $actor->getId() : 0;

        $query = 'UPDATE cron_job SET ' .
            ' job_status = ' . $this->db->quote(0, 'integer') .
            ' , job_result_status = ' . $this->db->quote(null, 'text') .
            ' , job_result_message = ' . $this->db->quote(null, 'text') .
            ' , job_result_type = ' . $this->db->quote(null, 'text') .
            ' , job_result_code = ' . $this->db->quote(null, 'text') .
            ' , job_status_user_id = ' . $this->db->quote($usrId, 'integer') .
            ' , job_status_type = ' . $this->db->quote($wasManuallyExecuted, 'integer') .
            ' , job_status_ts = ' . $this->db->quote($when->getTimestamp(), 'integer') .
            ' WHERE job_id = ' . $this->db->quote($job->getId(), 'text');
        $this->db->manipulate($query);
    }

    public function findAll(): \ILIAS\Cron\Job\JobCollection
    {
        $collection = new \ILIAS\Cron\Job\Collection\JobEntities();

        foreach ($this->getCronJobData() as $item) {
            $job = $this->getJobInstance(
                $item['job_id'],
                $item['component'],
                $item['class']
            );
            if ($job) {
                $collection->add(new \ILIAS\Cron\Job\JobEntity($job, $item));
            }
        }

        foreach ($this->getPluginJobs() as $item) {
            $collection->add(new \ILIAS\Cron\Job\JobEntity($item[0], $item[1], true));
        }

        return $collection;
    }
}
