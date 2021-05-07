<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);


/**
 This will set progresses to FAILED, if they are passed the deadline (and nit successfull)
 */
class ilPrgUpdateProgressCronJob extends ilCronJob
{
    const ID = 'prg_update_progress';
    const CRON_USER_ID = 6; //TODO: This is root, not cron.

    /**
     * @var ilStudyProgrammeProgressRepository
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

        $this->user_progress_db = ilStudyProgrammeDIC::dic()['model.Progress.ilStudyProgrammeProgressRepository'];
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
        foreach ($this->user_progress_db->readPassedDeadline() as $progress) {
            if ($progress->getStatus() === ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                //TODO: this is a detour...
                $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());
                $programme->markFailed($progress->getId(), self::CRON_USER_ID);
            }
        }
        $result->setStatus(ilCronJobResult::STATUS_OK);

        return $result;
    }
}
