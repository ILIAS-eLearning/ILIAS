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

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->lng->txt('ecs_cron_task_scheduler');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->lng->txt('ecs_cron_task_scheduler_info');
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return self::ID;
    }

    /**
     * Is to be activated on "installation"
     *
     * @return boolean
     */
    public function hasAutoActivation()
    {
        return false;
    }

    /**
     * Can the schedule be configured?
     *
     * @return boolean
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * Get schedule type
     *
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_HOURS;
    }



    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return self::DEFAULT_SCHEDULE_VALUE;
    }

    /**
     * Run job
     *
     * @return \ilCronJobResult
     */
    public function run()
    {
        $this->logger->debug('Starting ecs task scheduler...');

        $servers = \ilECSServerSettings::getInstance();

        foreach ($servers->getServers() as $server) {
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
