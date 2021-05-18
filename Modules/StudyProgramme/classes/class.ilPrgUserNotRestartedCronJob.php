<?php

/* Copyright (c) 2019 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);


class ilPrgUserNotRestartedCronJob extends ilCronJob
{
    const ID = 'prg_user_not_restarted';

    /**
     * @var ilStudyProgrammeAssignmentDBRepository
     */
    protected $user_assignments_db;

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
        $this->events = ilStudyProgrammeDIC::dic()['ilStudyProgrammeEvents'];
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
        foreach ($this->user_assignments_db->getDueToRestartAndMail() as $assignment) {
            try {
                $prg = ilObjStudyProgramme::getInstanceByObjId($assignment->getRootId());
                $validity_of_qualification = $prg->getSettings()->getValidityOfQualificationSettings();
                $auto_re_assign = $validity_of_qualification->getRestartPeriod();
                if ($auto_re_assign == -1) {
                    continue;
                }

                $auto_mail_settings = $prg->getSettings()->getAutoMailSettings();
                $inform_by_days = $auto_mail_settings->getReminderNotRestartedByUserDays();
                if (is_null($inform_by_days)) {
                    continue;
                }
                $restart_date = $assignment->getRestartDate();
                $restart_date->sub(new DateInterval(('P' . $inform_by_days . 'D')));

                $this->events->informUserByMailToRestart($assignment);
            } catch (ilException $e) {
                $this->log->write('an error occured: ' . $e->getMessage());
            }
        }
        return $result;
    }
}
