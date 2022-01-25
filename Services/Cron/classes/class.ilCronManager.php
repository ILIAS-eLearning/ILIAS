<?php declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Cron management
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCron
 */
class ilCronManager implements ilCronManagerInterface
{
    protected ilSetting $settings;
    protected ilLogger $logger;

    public function __construct(ilSetting $settings, ilLogger $logger)
    {
        $this->settings = $settings;
        $this->logger = $logger;
    }

    public function runActiveJobs() : void
    {
        $this->logger->info('CRON - batch start');

        $ts = time();
        $this->settings->set('last_cronjob_start_ts', (string) $ts);

        $useRelativeDates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);
        $this->logger->info(sprintf(
            'Set last datetime to: %s',
            ilDatePresentation::formatDate(new ilDateTime($ts, IL_CAL_UNIX))
        ));
        $this->logger->info(sprintf(
            'Verification of last run datetime (read from database): %s',
            ilDatePresentation::formatDate(
                new ilDateTime(ilSetting::_lookupValue('common', 'last_cronjob_start_ts'), IL_CAL_UNIX)
            )
        ));
        ilDatePresentation::setUseRelativeDates((bool) $useRelativeDates);

        // ilLink::_getStaticLink() should work in crons
        if (!defined('ILIAS_HTTP_PATH')) {
            define('ILIAS_HTTP_PATH', ilUtil::_getHttpPath());
        }

        // system
        foreach (self::getCronJobData(null, false) as $row) {
            $job = self::getJobInstanceById($row['job_id']);
            if ($job instanceof ilCronJob) {
                // #18411 - we are NOT using the initial job data as it might be outdated at this point
                self::runJob($job);
            }
        }

        // plugins
        foreach (self::getPluginJobs(true) as $item) {
            // #18411 - we are NOT using the initial job data as it might be outdated at this point
            self::runJob($item[0]);
        }

        $this->logger->info('CRON - batch end');
    }

    public static function runJobManual(string $a_job_id) : bool
    {
        global $DIC;

        $ilLog = $DIC->logger()->root();

        $result = false;

        $ilLog->write('CRON - manual start (' . $a_job_id . ')');

        $job = self::getJobInstanceById($a_job_id);
        if ($job instanceof ilCronJob) {
            if ($job->isManuallyExecutable()) {
                $result = self::runJob($job, null, true);
            } else {
                $ilLog->write('CRON - job ' . $a_job_id . ' is not intended to be executed manually');
            }
        } else {
            $ilLog->write('CRON - job ' . $a_job_id . ' seems invalid or is inactive');
        }

        $ilLog->write('CRON - manual end (' . $a_job_id . ')');

        return $result;
    }

    /**
     * Run single cron job (internal)
     * @param ilCronJob $a_job
     * @param array|null $a_job_data
     * @param bool $a_manual
     * @return bool
     * @internal
     */
    protected static function runJob(ilCronJob $a_job, ?array $a_job_data = null, bool $a_manual = false) : bool
    {
        global $DIC;

        $ilLog = $DIC->logger()->root();
        $ilDB = $DIC->database();

        $did_run = false;

        if (null === $a_job_data) {
            // aquire "fresh" job (status) data
            $jobData = self::getCronJobData($a_job->getId());
            $a_job_data = array_pop($jobData);
        }

        // already running?
        if ($a_job_data['alive_ts']) {
            $ilLog->write('CRON - job ' . $a_job_data['job_id'] . ' still running');

            $cut = 60 * 60 * 3; // 3h

            // is running (and has not pinged) for 3 hours straight, we assume it crashed
            if (time() - ((int) $a_job_data['alive_ts']) > $cut) {
                $ilDB->manipulate('UPDATE cron_job SET' .
                    ' running_ts = ' . $ilDB->quote(0, 'integer') .
                    ' , alive_ts = ' . $ilDB->quote(0, 'integer') .
                    ' WHERE job_id = ' . $ilDB->quote($a_job_data['job_id'], 'text'));

                self::deactivateJob($a_job); // #13082

                $result = new ilCronJobResult();
                $result->setStatus(ilCronJobResult::STATUS_CRASHED);
                $result->setCode(ilCronJobResult::CODE_SUPPOSED_CRASH);
                $result->setMessage('Cron job deactivated because it has been inactive for 3 hours');

                self::updateJobResult($a_job, $result, $a_manual);

                $ilLog->write('CRON - job ' . $a_job_data['job_id'] . ' deactivated (assumed crash)');
            }
        } // initiate run?
        elseif ($a_job->isDue(
            $a_job_data['job_result_ts'] ? (int) $a_job_data['job_result_ts'] : null,
            $a_job_data['schedule_type'] ? (int) $a_job_data['schedule_type'] : null,
            $a_job_data['schedule_value'] ? (int) $a_job_data['schedule_value'] : null,
            $a_manual
        )) {
            $ilLog->write('CRON - job ' . $a_job_data['job_id'] . ' started');

            $ilDB->manipulate('UPDATE cron_job SET' .
                ' running_ts = ' . $ilDB->quote(time(), 'integer') .
                ' , alive_ts = ' . $ilDB->quote(time(), 'integer') .
                ' WHERE job_id = ' . $ilDB->quote($a_job_data['job_id'], 'text'));

            $ts_in = self::getMicrotime();
            try {
                $result = $a_job->run();
            } catch (Throwable $e) {
                $result = new ilCronJobResult();
                $result->setStatus(ilCronJobResult::STATUS_CRASHED);
                $result->setMessage(sprintf('Exception: %s', $e->getMessage()));

                $ilLog->error($e->getMessage());
                $ilLog->error($e->getTraceAsString());
            }
            $ts_dur = self::getMicrotime() - $ts_in;

            if ($result->getStatus() === ilCronJobResult::STATUS_INVALID_CONFIGURATION) {
                self::deactivateJob($a_job);
                $ilLog->write('CRON - job ' . $a_job_data['job_id'] . ' invalid configuration');
            } else {
                // success!
                $did_run = true;
            }

            $result->setDuration($ts_dur);

            self::updateJobResult($a_job, $result, $a_manual);

            $ilDB->manipulate('UPDATE cron_job SET' .
                ' running_ts = ' . $ilDB->quote(0, 'integer') .
                ' , alive_ts = ' . $ilDB->quote(0, 'integer') .
                ' WHERE job_id = ' . $ilDB->quote($a_job_data['job_id'], 'text'));

            $ilLog->write('CRON - job ' . $a_job_data['job_id'] . ' finished');
        } else {
            $ilLog->write('CRON - job ' . $a_job_data['job_id'] . ' returned status inactive');
        }

        return $did_run;
    }

    public static function getJobInstanceById(string $a_job_id) : ?ilCronJob
    {
        global $DIC;

        $ilLog = $DIC->logger()->root();
        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC['component.repository'];
        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC['component.factory'];

        // plugin
        if (strpos($a_job_id, 'pl__') === 0) {
            $parts = explode('__', $a_job_id);
            $pl_name = $parts[1];
            $job_id = $parts[2];

            foreach ($component_repository->getPlugins() as $pl) {
                if ($pl->getName() !== $pl_name || !$pl->isActive()) {
                    continue;
                }

                $plugin = $component_factory->getPlugin($pl->getId());

                if (!$plugin instanceof ilCronJobProvider) {
                    continue;
                }

                try {
                    $job = $plugin->getCronJobInstance($job_id);

                    // should never happen but who knows...
                    $jobs_data = self::getCronJobData($job_id);
                    if ($jobs_data === []) {
                        // as job is not 'imported' from xml
                        self::createDefaultEntry($job, $pl_name, IL_COMP_PLUGIN, '');
                    }

                    return $job;
                } catch (OutOfBoundsException $e) {
                    // Maybe a job was removed from plugin, renamed etc.
                }

                break;
            }
        } else {
            $jobs_data = self::getCronJobData($a_job_id);
            if ($jobs_data !== [] && $jobs_data[0]['job_id'] === $a_job_id) {
                return self::getJobInstance(
                    $jobs_data[0]['job_id'],
                    $jobs_data[0]['component'],
                    $jobs_data[0]['class'],
                    $jobs_data[0]['path']
                );
            }
        }

        $ilLog->write('CRON - job ' . $a_job_id . ' seems invalid or is inactive');

        return null;
    }

    public static function getJobInstance(
        string $a_id,
        string $a_component,
        string $a_class,
        ?string $a_path,
        bool $isCreationContext = false
    ) : ?ilCronJob {
        if (!$a_path) {
            $a_path = $a_component . '/classes/';
        }

        $class_file = $a_path . 'class.' . $a_class . '.php';

        if (is_file($class_file)) {
            include_once $class_file;
            if (class_exists($a_class)) {
                if ($isCreationContext) {
                    $refl = new ReflectionClass($a_class);
                    $job = $refl->newInstanceWithoutConstructor();
                } else {
                    $job = new $a_class;
                }

                if ($job instanceof ilCronJob && $job->getId() === $a_id) {
                    return $job;
                }
            }
        }

        return null;
    }

    public static function createDefaultEntry(
        ilCronJob $a_job,
        string $a_component,
        string $a_class,
        ?string $a_path
    ) : void {
        global $DIC;

        $ilLog = $DIC->logger()->root();
        $ilDB = $DIC->database();

        if (!isset($DIC['ilSetting'])) {
            $DIC['ilSetting'] = static function (\ILIAS\DI\Container $c) : ilSetting {
                return new ilSetting();
            };
        }

        $ilSetting = $DIC->settings();

        $sql = "SELECT job_id, schedule_type, component, class, path FROM cron_job" .
            " WHERE job_id = " . $ilDB->quote($a_job->getId(), "text");
        $set = $ilDB->query($sql);
        $row = $ilDB->fetchAssoc($set);
        $job_id = $row['job_id'] ?? null;
        $job_exists = ($job_id === $a_job->getId());
        $schedule_type = $row["schedule_type"] ?? null;

        if ($job_exists && (
            $row['component'] !== $a_component ||
                $row['class'] !== $a_class ||
                $row['path'] !== $a_path
        )) {
            $ilDB->manipulateF(
                'UPDATE cron_job SET component = %s, class = %s, path = %s WHERE job_id = %s',
                ['text', 'text', 'text', 'text'],
                [$a_component, $a_class, $a_path, $a_job->getId()]
            );
        }

        // new job
        if (!$job_exists) {
            $sql = 'INSERT INTO cron_job (job_id, component, class, path)' .
                ' VALUES (' . $ilDB->quote($a_job->getId(), 'text') . ', ' .
                $ilDB->quote($a_component, 'text') . ', ' .
                $ilDB->quote($a_class, 'text') . ', ' .
                $ilDB->quote($a_path, 'text') . ')';
            $ilDB->manipulate($sql);

            $ilLog->write('Cron XML - Job ' . $a_job->getId() . ' in class ' . $a_class . ' added.');

            // only if flexible
            self::updateJobSchedule(
                $a_job,
                $a_job->getDefaultScheduleType(),
                $a_job->getDefaultScheduleValue()
            );

            // #12221
            if (!is_object($ilSetting)) {
                $ilSetting = new ilSetting();
            }

            if ($a_job->hasAutoActivation()) {
                self::activateJob($a_job);
            } else {
                // to overwrite dependent settings
                $a_job->activationWasToggled(false);
            }
        } // existing job - but schedule is flexible now
        elseif (!$schedule_type && $a_job->hasFlexibleSchedule()) {
            self::updateJobSchedule(
                $a_job,
                $a_job->getDefaultScheduleType(),
                $a_job->getDefaultScheduleValue()
            );
        } // existing job - but schedule is static now
        elseif ($schedule_type && !$a_job->hasFlexibleSchedule()) {
            self::updateJobSchedule($a_job, null, null);
        }
    }

    public static function updateFromXML(
        string $a_component,
        string $a_id,
        string $a_class,
        ?string $a_path
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$ilDB->tableExists('cron_job')) {
            return;
        }

        $job = self::getJobInstance($a_id, $a_component, $a_class, $a_path, true);
        if ($job) {
            self::createDefaultEntry($job, $a_component, $a_class, $a_path);
        }
    }

    /**
     * Clear job data
     * @param string $a_component
     * @param string[] $a_xml_job_ids
     */
    public static function clearFromXML(string $a_component, array $a_xml_job_ids) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$ilDB->tableExists('cron_job')) {
            return;
        }

        $all_jobs = [];
        $sql = 'SELECT job_id FROM cron_job WHERE component = ' . $ilDB->quote($a_component, 'text');
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $all_jobs[] = $row['job_id'];
        }

        if ($all_jobs !== []) {
            if ($a_xml_job_ids !== []) {
                foreach ($all_jobs as $job_id) {
                    if (!in_array($job_id, $a_xml_job_ids, true)) {
                        $ilDB->manipulate('DELETE FROM cron_job' .
                            ' WHERE component = ' . $ilDB->quote($a_component, 'text') .
                            ' AND job_id = ' . $ilDB->quote($job_id, 'text'));
                    }
                }
            } else {
                $ilDB->manipulate('DELETE FROM cron_job WHERE component = ' . $ilDB->quote($a_component, 'text'));
            }
        }
    }

    /**
     * @param bool $a_only_active
     * @return array<int, array{0: ilCronJob, 1: array}>
     */
    public static function getPluginJobs(bool $a_only_active = false) : array
    {
        global $DIC;

        /** @var ilComponentRepository $component_repository */
        $component_repository = $DIC['component.repository'];
        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC['component.factory'];

        $res = [];

        foreach ($component_repository->getPlugins() as $pl) {
            if (!$pl->isActive()) {
                continue;
            }

            $plugin = $component_factory->getPlugin($pl->getId());

            if (!$plugin instanceof ilCronJobProvider) {
                continue;
            }

            foreach ($plugin->getCronJobInstances() as $job) {
                $jobs_data = self::getCronJobData($job->getId());
                $job_data = $jobs_data[0] ?? null;
                if (!is_array($job_data) || $job_data === []) {
                    // as job is not "imported" from xml
                    self::createDefaultEntry($job, $plugin->getPluginName(), IL_COMP_PLUGIN, '');
                }

                $jobs_data = self::getCronJobData($job->getId());
                $job_data = $jobs_data[0];

                // #17941
                if (!$a_only_active || (int) $job_data['job_status'] === 1) {
                    $res[$job->getId()] = [$job, $job_data];
                }
            }
        }

        return $res;
    }

    /**
     * Get cron job configuration/execution data
     * @param array|string|null $a_id
     * @param array $a_include_inactive
     * @return array
     */
    public static function getCronJobData($a_id = null, bool $a_include_inactive = true) : array
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = [];

        if ($a_id && !is_array($a_id)) {
            $a_id = [$a_id];
        }

        $sql = "SELECT * FROM cron_job";

        $where = [];
        if ($a_id) {
            $where[] = $ilDB->in('job_id', $a_id, false, 'text');
        } else {
            $where[] = 'class != ' . $ilDB->quote(IL_COMP_PLUGIN, 'text');
        }
        if (!$a_include_inactive) {
            $where[] = 'job_status = ' . $ilDB->quote(1, 'integer');
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        // :TODO: discuss job execution order
        $sql .= ' ORDER BY job_id';

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }

        return $res;
    }

    public static function resetJob(ilCronJob $a_job) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_RESET);
        $result->setCode(ilCronJobResult::CODE_MANUAL_RESET);
        $result->setMessage('Cron job re-activated by admin');

        self::updateJobResult($a_job, $result, true);

        $ilDB->manipulate('UPDATE cron_job' .
            ' SET running_ts = ' . $ilDB->quote(0, 'integer') .
            ' , alive_ts = ' . $ilDB->quote(0, 'integer') .
            ' , job_result_ts = ' . $ilDB->quote(0, 'integer') .
            ' WHERE job_id = ' . $ilDB->quote($a_job->getId(), 'text'));

        self::activateJob($a_job, true);
    }

    public static function activateJob(ilCronJob $a_job, bool $a_manual = false) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $user_id = 0;
        if ($DIC->isDependencyAvailable('user')) {
            $user = $DIC->user();
            $user_id = $a_manual ? $user->getId() : 0;
        }

        $sql = 'UPDATE cron_job SET ' .
            ' job_status = ' . $ilDB->quote(1, 'integer') .
            ' , job_status_user_id = ' . $ilDB->quote($user_id, 'integer') .
            ' , job_status_type = ' . $ilDB->quote($a_manual, 'integer') .
            ' , job_status_ts = ' . $ilDB->quote(time(), 'integer') .
            ' WHERE job_id = ' . $ilDB->quote($a_job->getId(), 'text');
        $ilDB->manipulate($sql);

        $a_job->activationWasToggled(true);
    }

    public static function deactivateJob(ilCronJob $a_job, bool $a_manual = false) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $user_id = $a_manual ? $ilUser->getId() : 0;

        $sql = 'UPDATE cron_job SET ' .
            ' job_status = ' . $ilDB->quote(0, 'integer') .
            ' , job_status_user_id = ' . $ilDB->quote($user_id, 'integer') .
            ' , job_status_type = ' . $ilDB->quote($a_manual, 'integer') .
            ' , job_status_ts = ' . $ilDB->quote(time(), 'integer') .
            ' WHERE job_id = ' . $ilDB->quote($a_job->getId(), 'text');
        $ilDB->manipulate($sql);

        $a_job->activationWasToggled(false);
    }

    public static function isJobActive(string $a_job_id) : bool
    {
        $jobs_data = self::getCronJobData($a_job_id);

        return $jobs_data !== [] && $jobs_data[0]['job_status'];
    }

    public static function isJobInactive(string $a_job_id) : bool
    {
        $jobs_data = self::getCronJobData($a_job_id);

        return $jobs_data !== [] && !((bool) $jobs_data[0]['job_status']);
    }

    protected static function updateJobResult(
        ilCronJob $a_job,
        ilCronJobResult $a_result,
        bool $a_manual = false
    ) : void {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        $user_id = $a_manual ? $ilUser->getId() : 0;

        $sql = 'UPDATE cron_job SET ' .
            ' job_result_status = ' . $ilDB->quote($a_result->getStatus(), 'integer') .
            ' , job_result_user_id = ' . $ilDB->quote($user_id, 'integer') .
            ' , job_result_code = ' . $ilDB->quote($a_result->getCode(), 'text') .
            ' , job_result_message = ' . $ilDB->quote($a_result->getMessage(), 'text') .
            ' , job_result_type = ' . $ilDB->quote($a_manual, 'integer') .
            ' , job_result_ts = ' . $ilDB->quote(time(), 'integer') .
            ' , job_result_dur = ' . $ilDB->quote($a_result->getDuration() * 1000, 'integer') .
            ' WHERE job_id = ' . $ilDB->quote($a_job->getId(), 'text');
        $ilDB->manipulate($sql);
    }

    public static function updateJobSchedule(ilCronJob $a_job, ?int $a_schedule_type, ?int $a_schedule_value) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (
            $a_schedule_type === null ||
            ($a_job->hasFlexibleSchedule() && in_array($a_schedule_type, $a_job->getValidScheduleTypes(), true))
        ) {
            $sql = 'UPDATE cron_job SET ' .
                ' schedule_type = ' . $ilDB->quote($a_schedule_type, 'integer') .
                ' , schedule_value = ' . $ilDB->quote($a_schedule_value, 'integer') .
                ' WHERE job_id = ' . $ilDB->quote($a_job->getId(), 'text');
            $ilDB->manipulate($sql);
        }
    }

    protected static function getMicrotime() : float
    {
        [$usec, $sec] = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * Keep cron job alive
     * @param string $a_job_id
     */
    public static function ping(string $a_job_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulate('UPDATE cron_job SET alive_ts = ' . $ilDB->quote(time(), 'integer') .
            ' WHERE job_id = ' . $ilDB->quote($a_job_id, 'text'));
    }
}
