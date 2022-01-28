<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronEcsTaskScheduler
 *
 * Start execution of ecs tasks.
 *
 */
class ilCronEcsTaskScheduler extends \ilCronJob
{

    /**
     * @var string
     */
    public const ID = 'ecs_task_handler';

    /**
     * @var int
     */
    public const DEFAULT_SCHEDULE_VALUE = 1;

    /**
     * @var null | \ilLogger
     */
    private $logger = null;

    /**
     * @var null | \ilLanguage
     */
    protected $lng = null;

    /**
     * @var null | \ilCronJobResult
     */
    protected $result = null;

    /**
     * ilCronEcsTaskScheduler constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->wsrv();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('ecs');

        $this->result = new \ilCronJobResult();
    }

    public function getTitle() : string
    {
        return $this->lng->txt('ecs_cron_task_scheduler');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('ecs_cron_task_scheduler_info');
    }

    public function getId() : string
    {
        return self::ID;
    }

    public function hasAutoActivation() : bool
    {
        return false;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return self::DEFAULT_SCHEDULE_VALUE;
    }

    public function run() : ilCronJobResult
    {
        $this->logger->debug('Starting ecs task scheduler...');

        $servers = \ilECSServerSettings::getInstance();

        foreach ($servers->getServers(ilECSServerSettings::ACTIVE_SERVER) as $server) {
            try {
                $this->logger->info('Starting task execution for ecs server: ' . $server->getTitle());
                $scheduler = \ilECSTaskScheduler::_getInstanceByServerId($server->getServerId());
                $scheduler->startTaskExecution();
            } catch (\Exception $e) {
                $this->result->setStatus(\ilCronJobResult::STATUS_CRASHED);
                $this->result->setMessage($e->getMessage());
                $this->logger->warning('ECS task execution failed with message: ' . $e->getMessage());
                return $this->result;
            }
        }
        $this->result->setStatus(\ilCronJobResult::STATUS_OK);
        return $this->result;
    }
}
