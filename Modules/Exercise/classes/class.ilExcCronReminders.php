<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Cron for exercise reminders
 *
 * @author Jesús López <lopez@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExcCronReminders extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId()
    {
        return "exc_reminders";
    }

    public function getTitle()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("exc");

        return $lng->txt("exc_reminders_cron");
    }

    public function getDescription()
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("exc");

        return $lng->txt("exc_reminders_cron_info");
    }

    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue()
    {
        return;
    }

    public function hasAutoActivation()
    {
        return true;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function run()
    {
        include_once "Modules/Exercise/classes/class.ilExAssignmentReminder.php";

        $log = ilLoggerFactory::getLogger("exc");
        $log->debug("--- Start Exercise Reminders Cron");

        $cron_status = ilCronJobResult::STATUS_NO_ACTION;
        $message = "";
        $reminder = new ilExAssignmentReminder();
        $num_reminders = $reminder->checkReminders();

        $this->lng->loadLanguageModule("exc");

        if ($num_reminders) {
            $cron_status = ilCronJobResult::STATUS_OK;
            $message = $this->lng->txt('exc_reminder_cron_ok');
        }

        $cron_result = new ilCronJobResult();
        $cron_result->setStatus($cron_status);

        if ($message != "") {
            $cron_result->setMessage($message . " " . $num_reminders);
            $cron_result->setCode("#" . $num_reminders);
        }

        return $cron_result;
    }
}
