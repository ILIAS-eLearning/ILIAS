<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);


class ilPrgUserRiskyToFailCronJob extends ilCronJob
{
    const ID = 'prg_user_risky_to_fail';

    /**
     * @var ilStudyProgrammeSettingsRepository
     */
    protected $programme_settings_db;

    /**
     * @var ilStudyProgrammeProgressRepository
     */
    protected $user_progress_db;

    /**
     * @var ilStudyProgrammeEvents
     */
    protected $events;
    
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
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('prg');

        $this->dic = ilStudyProgrammeDIC::dic();
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->lng->txt('prg_user_risky_to_fail_title');
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->lng->txt('prg_user_risky_to_fail_desc');
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
        return true;
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
        return self::SCHEDULE_TYPE_IN_DAYS;
    }

    /**
     * Get schedule value
     *
     * @return int|array
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }

    public function run()
    {
        $result = new ilCronJobResult();
        $result->setStatus(ilCronJobResult::STATUS_NO_ACTION);

        $programmes_to_send = $this->getSettingsRepository()
            ->getProgrammeIdsWithRiskyToFailSettings();

        if (count($programmes_to_send) == 0) {
            return $result;
        }

        $today = $this->getNow();
        $programmes_and_due = [];
        foreach ($programmes_to_send as $programme_obj_id => $days_offset_mail) {
            $interval = new DateInterval('P' . $days_offset_mail . 'D');
            $due = $today->add($interval);
            $programmes_and_due[$programme_obj_id] = $due;
        }

        $progresses = $this->getProgressRepository()
            ->getRiskyToFail($programmes_and_due);
        
        if (count($progresses) == 0) {
            return $result;
        }

        $events = $this->getEvents();
        foreach ($progresses as $progress) {
            $this->log(
                sprintf(
                    'PRG, RiskyToFail: user %s at progress %s (prg obj_id %s)',
                    $progress->getUserId(),
                    $progress->getId(),
                    $progress->getNodeId()
                )
            );
            $events->userRiskyToFail($progress);
        }
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }

    protected function getNow() : \DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    protected function getSettingsRepository() : ilStudyProgrammeSettingsDBRepository
    {
        return $this->dic['model.Settings.ilStudyProgrammeSettingsRepository'];
    }

    protected function getProgressRepository() : ilStudyProgrammeProgressDBRepository
    {
        return $this->dic['ilStudyProgrammeUserProgressDB'];
    }

    protected function getEvents()
    {
        return $this->dic['ilStudyProgrammeEvents'];
    }

    protected function log(string $msg) : void
    {
        $this->log->write($msg);
    }
}
