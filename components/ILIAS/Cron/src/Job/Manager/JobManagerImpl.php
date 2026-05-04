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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobRepository;

/**
 * @phpstan-import-type CronJobRecord from JobRepository
 */
readonly class JobManagerImpl implements \ILIAS\Cron\Job\JobManager
{
    public function __construct(
        private JobRepository $job_repository,
        private \ilDBInterface $db,
        private \ilSetting $settings,
        private \ilLogger $logger,
        private \ILIAS\Data\Clock\ClockFactory $clock_factory
    ) {
    }

    private function getMicrotime(): float
    {
        return ((int) $this->clock_factory->system()->now()->format('Uu')) / 1000000;
    }

    public function runActiveJobs(\ilObjUser $actor): void
    {
        $this->logger->info('CRON - batch start');

        $ts = $this->clock_factory->system()->now()->getTimestamp();
        $this->settings->set('last_cronjob_start_ts', (string) $ts);

        $useRelativeDates = \ilDatePresentation::useRelativeDates();
        \ilDatePresentation::setUseRelativeDates(false);
        $this->logger->info(
            \sprintf(
                'Set last datetime to: %s',
                \ilDatePresentation::formatDate(new \ilDateTime($ts, IL_CAL_UNIX))
            )
        );
        $this->logger->info(
            \sprintf(
                'Verification of last run datetime (read from database): %s',
                \ilDatePresentation::formatDate(
                    new \ilDateTime(\ilSetting::_lookupValue('common', 'last_cronjob_start_ts'), IL_CAL_UNIX)
                )
            )
        );
        \ilDatePresentation::setUseRelativeDates($useRelativeDates);

        // ilLink::_getStaticLink() should work in crons
        if (!\defined('ILIAS_HTTP_PATH')) {
            \define('ILIAS_HTTP_PATH', \ilUtil::_getHttpPath());
        }

        foreach ($this->job_repository->getCronJobData(null, false) as $row) {
            $job = $this->job_repository->getJobInstanceById($row['job_id']);
            if ($job instanceof \ILIAS\Cron\CronJob) {
                // #18411 - we are NOT using the initial job data as it might be outdated at this point
                $this->runJob($job, $actor);
            }
        }

        $this->logger->info('CRON - batch end');
    }

    public function runJobManual(string $id, \ilObjUser $actor): bool
    {
        $result = false;

        $this->logger->info('CRON - manual start (' . $id . ')');

        $job = $this->job_repository->getJobInstanceById($id);
        if ($job instanceof \ILIAS\Cron\CronJob) {
            if ($job->isManuallyExecutable()) {
                $result = $this->runJob($job, $actor, null, true);
            } else {
                $this->logger->info('CRON - job ' . $id . ' is not intended to be executed manually');
            }
        } else {
            $this->logger->info('CRON - job ' . $id . ' seems invalid or is inactive');
        }

        $this->logger->info('CRON - manual end (' . $id . ')');

        return $result;
    }

    /**
     * Run single cron job (internal)
     * @param null|CronJobRecord $job_data
     * @internal
     */
    private function runJob(
        \ILIAS\Cron\CronJob $job,
        \ilObjUser $actor,
        ?array $job_data = null,
        bool $is_manually_executed = false
    ): bool {
        $did_run = false;

        if ($job_data === null) {
            // aquire "fresh" job (status) data
            $jobsData = $this->job_repository->getCronJobData($job->getId());
            $job_data = array_pop($jobsData);
        }

        $job->setDateTimeProvider(fn(): \DateTimeImmutable => $this->clock_factory->system()->now());

        // already running?
        if ($job_data['alive_ts']) {
            $this->logger->info('CRON - job ' . $job_data['job_id'] . ' still running');

            $cut = 60 * 60 * 3;

            // is running (and has not pinged) for 3 hours straight, we assume it crashed
            if ($this->clock_factory->system()->now()->getTimestamp() - ((int) $job_data['alive_ts']) > $cut) {
                $this->job_repository->updateRunInformation($job_data['job_id'], 0, 0);
                $this->deactivateJob($job, $actor); // #13082

                $result = new \ILIAS\Cron\Job\JobResult();
                $result->setStatus(\ILIAS\Cron\Job\JobResult::STATUS_CRASHED);
                $result->setCode(\ILIAS\Cron\Job\JobResult::CODE_SUPPOSED_CRASH);
                $result->setMessage('Cron job deactivated because it has been inactive for 3 hours');

                $this->job_repository->updateJobResult(
                    $job,
                    $this->clock_factory->system()->now(),
                    $actor,
                    $result,
                    $is_manually_executed
                );

                $this->logger->info('CRON - job ' . $job_data['job_id'] . ' deactivated (assumed crash)');
            }
        } // initiate run?
        elseif ($job->isDue(
            $job_data['job_result_ts'] ? new \DateTimeImmutable(
                '@' . $job_data['job_result_ts']
            )->setTimezone($this->clock_factory->system()->now()->getTimezone()) : null,
            is_numeric($job_data['schedule_type']) ? JobScheduleType::tryFrom(
                (int) $job_data['schedule_type']
            ) : null,
            $job_data['schedule_value'] ? (int) $job_data['schedule_value'] : null,
            $is_manually_executed
        )) {
            $this->logger->info('CRON - job ' . $job_data['job_id'] . ' started');

            $this->job_repository->updateRunInformation(
                $job_data['job_id'],
                $this->clock_factory->system()->now()->getTimestamp(),
                $this->clock_factory->system()->now()->getTimestamp()
            );

            $ts_in = $this->getMicrotime();
            try {
                $job->init();
                $result = $job->run();
            } catch (\Throwable $e) {
                $result = new \ILIAS\Cron\Job\JobResult();
                $result->setStatus(\ILIAS\Cron\Job\JobResult::STATUS_CRASHED);
                $result->setMessage(
                    \ilStr::subStr(\sprintf('Exception: %s / %s', $e->getMessage(), $e->getTraceAsString()), 0, 400)
                );

                $this->logger->error($e->getMessage());
                $this->logger->error($e->getTraceAsString());
            } finally {
                $ts_dur = $this->getMicrotime() - $ts_in;
            }

            if ($result->getStatus() === \ILIAS\Cron\Job\JobResult::STATUS_INVALID_CONFIGURATION) {
                $this->deactivateJob($job, $actor);
                $this->logger->info('CRON - job ' . $job_data['job_id'] . ' invalid configuration');
            } else {
                // success!
                $did_run = true;
            }

            $result->setDuration($ts_dur);

            $this->job_repository->updateJobResult(
                $job,
                $this->clock_factory->system()->now(),
                $actor,
                $result,
                $is_manually_executed
            );
            $this->job_repository->updateRunInformation($job_data['job_id'], 0, 0);

            $this->logger->info('CRON - job ' . $job_data['job_id'] . ' finished');
        } else {
            $this->logger->info('CRON - job ' . $job_data['job_id'] . ' returned status inactive');
        }

        return $did_run;
    }

    public function resetJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor): void
    {
        $result = new \ILIAS\Cron\Job\JobResult();
        $result->setStatus(\ILIAS\Cron\Job\JobResult::STATUS_RESET);
        $result->setCode(\ILIAS\Cron\Job\JobResult::CODE_MANUAL_RESET);
        $result->setMessage('Cron job re-activated by admin');

        $this->job_repository->updateJobResult(
            $job,
            $this->clock_factory->system()->now(),
            $actor,
            $result,
            true
        );
        $this->job_repository->resetJob($job);

        $this->activateJob($job, $actor, true);
    }

    public function activateJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor, bool $was_manually_executed = false): void
    {
        $this->job_repository->activateJob($job, $this->clock_factory->system()->now(), $actor, $was_manually_executed);
        $job->activationWasToggled($this->db, $this->settings, true);
    }

    public function deactivateJob(\ILIAS\Cron\CronJob $job, \ilObjUser $actor, bool $was_manually_executed = false): void
    {
        $this->job_repository->deactivateJob($job, $this->clock_factory->system()->now(), $actor, $was_manually_executed);
        $job->activationWasToggled($this->db, $this->settings, false);
    }

    public function isJobActive(string $id): bool
    {
        $jobs_data = $this->job_repository->getCronJobData($id);

        return $jobs_data !== [] && $jobs_data[0]['job_status'];
    }

    public function isJobInactive(string $id): bool
    {
        $jobs_data = $this->job_repository->getCronJobData($id);

        return $jobs_data !== [] && !((bool) $jobs_data[0]['job_status']);
    }

    public function ping(string $id): void
    {
        $this->db->manipulateF(
            'UPDATE cron_job SET alive_ts = %s WHERE job_id = %s',
            ['integer', 'text'],
            [$this->clock_factory->system()->now()->getTimestamp(), $id]
        );
    }
}
