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

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;

/**
 * Class ilCronEcsTaskScheduler
 *
 * Start execution of ecs tasks.
 *
 */
class ilCronEcsTaskScheduler extends \ILIAS\Cron\CronJob
{
    public const ID = 'ecs_task_handler';
    public const DEFAULT_SCHEDULE_VALUE = 1;

    private ilLogger $logger;
    private ilLanguage $lng;
    private JobResult $result;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ecs');

        $this->result = new \ILIAS\Cron\Job\JobResult();
    }

    public function getTitle(): string
    {
        return $this->lng->txt('ecs_cron_task_scheduler');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('ecs_cron_task_scheduler_info');
    }

    public function getId(): string
    {
        return self::ID;
    }

    public function hasAutoActivation(): bool
    {
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::IN_HOURS;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return self::DEFAULT_SCHEDULE_VALUE;
    }

    public function run(): JobResult
    {
        $this->logger->debug('Starting ecs task scheduler...');

        $servers = \ilECSServerSettings::getInstance();

        foreach ($servers->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
            try {
                $this->logger->info('Starting task execution for ecs server: ' . $server->getTitle());
                $scheduler = \ilECSTaskScheduler::_getInstanceByServerId($server->getServerId());
                $scheduler->startTaskExecution();
            } catch (\Exception $e) {
                $this->result->setStatus(\ILIAS\Cron\Job\JobResult::STATUS_CRASHED);
                $this->result->setMessage($e->getMessage());
                $this->logger->warning('ECS task execution failed with message: ' . $e->getMessage());
                return $this->result;
            }
        }
        $this->result->setStatus(\ILIAS\Cron\Job\JobResult::STATUS_OK);
        return $this->result;
    }
}
