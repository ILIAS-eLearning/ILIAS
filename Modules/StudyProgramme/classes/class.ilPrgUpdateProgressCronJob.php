<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

class ilPrgUpdateProgressCronJob extends ilCronJob
{
    const ID = 'prg_update_progress';

    /**
     * @var ilStudyProgrammeUserProgressDB
     */
    protected $user_progress_db;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public function __construct()
    {
        global $DIC;

        $this->user_progress_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserProgressDB'];
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');
    }

    public function getTitle()
    {
        return $this->lng->txt('prg_update_progress_title');
    }

    public function getDescription()
    {
        return $this->lng->txt('prg_update_progress_description');
    }

    public function getId()
    {
        return self::ID;
    }

    public function hasAutoActivation()
    {
        return true;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    public function getDefaultScheduleValue()
    {
        return 1;
    }

    public function run()
    {
        $result = new ilCronJobResult();
        foreach ($this->user_progress_db->getPassedDeadline() as $progress) {
            if ($progress->getStatus() === ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                $progress->markFailed($progress->getUserId());
            }
        }
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
