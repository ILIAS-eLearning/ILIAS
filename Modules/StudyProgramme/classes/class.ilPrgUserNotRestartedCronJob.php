<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);


class ilPrgUserNotRestartedCronJob extends ilCronJob
{
    const ID = 'prg_user_not_restarted';

    /**
     * @var ilStudyProgrammeUserAssignmentDB
     */
    protected $user_assignments_db;

    /**
     * @var ilObjUser
     */
    protected $usr;

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

        $this->user_assignments_db = ilStudyProgrammeDIC::dic()['ilStudyProgrammeUserAssignmentDB'];
        $this->usr = $DIC['ilUser'];
        $this->log = $DIC['ilLog'];
        $this->lng = $DIC['lng'];
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->lng->txt('prg_user_not_restarted_title');
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->lng->txt('prg_user_not_restarted_desc');
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
        $result->setStatus(ilCronJobResult::STATUS_OK);
        $now = date('Y-m-d');
        foreach ($this->user_assignments_db->getDueToRestartAndMail() as $assignment) {
            try {
                $prg_settings = $assignment->getStudyProgramme()->getRawSettings();
                $auto_re_assign = $prg_settings->getRestartPeriod();
                if ($auto_re_assign == -1) {
                    continue;
                }

                $inform_by_days = $prg_settings->getReminderNotRestartedByUserDays();
                if (is_null($inform_by_days)) {
                    continue;
                }

                $restart_date = $assignment->getRestartDate();
                $restart_date->sub(new DateInterval(('P' . $inform_by_days . 'D')));

                if ($restart_date->format("Y-m-d") > $now) {
                    $assignment->informUserByMailToRestart();
                }
            } catch (ilException $e) {
                $this->log->write('an error occured: ' . $e->getMessage());
            }
        }
        return $result;
    }
}
