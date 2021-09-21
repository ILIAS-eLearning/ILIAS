<?php

/* Copyright (c) 2019 Denis Klöpfer <denis.kloepfer@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);


class ilPrgUserRiskyToFailCronJob extends ilCronJob
{
    const ID = 'prg_user_risky_to_fail';

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
        return $this->lng->txt('prg_user_risky_to_fail_title');
    }

    public function getDescription() : string
    {
        return $this->lng->txt('prg_user_risky_to_fail_desc');
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

    public function getDefaultScheduleValue() : int
    {
        return 1;
    }

    public function run() : ilCronJobResult
    {
        $result = new ilCronJobResult();
        foreach ($this->user_progress_db->getRiskyToFailInstances() as $progress) {
            try {
                $auto_mail_settings = $progress->getStudyProgramme()->getAutoMailSettings();
                $remind_days = $auto_mail_settings->getProcessingEndsNotSuccessfulDays();

                if (is_null($remind_days)) {
                    continue;
                }

                $check_date = new DateTime();
                $check_date->sub(new DateInterval('P' . $remind_days . 'D'));
                if ($progress->getDeadline()->format('Y-m-d') < $check_date->format('Y-m-d')) {
                    continue;
                }

                $progress->informUserForRiskToFail();
            } catch (ilException $e) {
                $this->log->write('an error occured: ' . $e->getMessage());
            }
        }
        $result->setStatus(ilCronJobResult::STATUS_OK);
        return $result;
    }
}
