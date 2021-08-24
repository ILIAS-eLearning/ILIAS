<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

/**
 This will set progresses to FAILED,
 if they are past the deadline (and not successful, yet)
 */
class ilPrgUpdateProgressCronJob extends ilCronJob
{
    const ID = 'prg_update_progress';
 
    /**
     * @var Pimple\Container;
     */
    protected $dic;

    /**
     * @var ilLanguage
     */
    protected $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');
        $this->dic = ilStudyProgrammeDIC::dic();
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
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);
        $acting_user = $this->getActingUserId();
        foreach ($this->getProgressRepository()->getPassedDeadline() as $progress) {
            if ($progress->getStatus() === ilStudyProgrammeProgress::STATUS_IN_PROGRESS) {
                $programme = ilObjStudyProgramme::getInstanceByObjId($progress->getNodeId());
                $programme->markFailed($progress->getId(), $acting_user);
                $result->setStatus(ilCronJobResult::STATUS_OK);
            }
        }
        return $result;
    }

    protected function getProgressRepository() : ilStudyProgrammeProgressDBRepository
    {
        return $this->dic['ilStudyProgrammeUserProgressDB'];
    }
    protected function getActingUserId() : int
    {
        return $this->dic['current_user']->getId();
    }
}
