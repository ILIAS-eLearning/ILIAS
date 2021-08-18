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

    public function getTitle() : string
    {
        return $this->lng->txt('prg_update_progress_title');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('prg_update_progress_description');
    }

    public function getId() : string
    {
        return self::ID;
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return 1;
    }

    public function run() : ilCronJobResult
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
