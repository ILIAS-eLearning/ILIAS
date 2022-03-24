<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Cron for exercise reminders
 *
 * @author Jesús López <lopez@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCronReminders extends ilCronJob
{
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId() : string
    {
        return "exc_reminders";
    }

    public function getTitle() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("exc");

        return $lng->txt("exc_reminders_cron");
    }

    public function getDescription() : string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("exc");

        return $lng->txt("exc_reminders_cron_info");
    }

    public function getDefaultScheduleType() : int
    {
        return self::SCHEDULE_TYPE_DAILY;
    }

    public function getDefaultScheduleValue() : ?int
    {
        return null;
    }

    public function hasAutoActivation() : bool
    {
        return true;
    }

    public function hasFlexibleSchedule() : bool
    {
        return true;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function run() : ilCronJobResult
    {
        $log = ilLoggerFactory::getLogger("exc");
        $log->debug("--- Start Exercise Reminders Cron");

        $cron_status = ilCronJobResult::STATUS_NO_ACTION;
        $message = "";
        $reminder = new ilExAssignmentReminder();
        $num_reminders = $reminder->checkReminders();

        $this->lng->loadLanguageModule("exc");

        if ($num_reminders !== 0) {
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
