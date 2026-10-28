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

use ILIAS\BackgroundTasks\Persistence;
use ILIAS\Cron\CronJob;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\Job\Schedule\JobScheduleType;

/**
 * Garbage collection for BackgroundTasks
 */
class ilBackgroundTaskGarbageCollection extends CronJob
{
    private ilLogger $logger;
    private ilLanguage $lng;
    private JobResult $result;
    private Persistence $persistence;

    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->bgtk();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('background_tasks');
        $this->persistence = $DIC->backgroundTasks()->persistence();

        $this->result = new JobResult();
    }

    public function getTitle(): string
    {
        return $this->lng->txt('background_task_garbage_collection');
    }

    public function getDescription(): string
    {
        return $this->lng->txt('background_task_garbage_collection_info');
    }

    public function getId(): string
    {
        return 'background_task_garbage_collection';
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
        return 1;
    }

    public function run(): JobResult
    {
        $this->logger->debug('Starting background task garbage collector..');

        try {
            $this->persistence->garbageCollection();
        } catch (Exception $e) {
            $this->result->setStatus(JobResult::STATUS_CRASHED);
            $this->result->setMessage(mb_substr("Exc.: {$e->getMessage()} / {$e->getTraceAsString()}", 0, 400));
            $this->logger->error("Background-Task Garbage Collection execution failed with message: {$e->getMessage()}");
            $this->logger->error($e->getTraceAsString());
            return $this->result;
        }

        $this->result->setStatus(JobResult::STATUS_OK);
        return $this->result;
    }
}
