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

    /**
     * Run job
     *
     * @return ilCronJobResult
     * @throws Exception
     */
    public function run()
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
