<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\AbstractCronJob;

/**
 * Cron for exercise reminders
 *
 * @author Jesús López <lopez@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCronReminders extends AbstractCronJob
{
    public function getId(): string
    {
        return "exc_reminders";
    }

    public function init(): void
    {
        $this->language->loadLanguageModule("exc");
    }

    public function getTitle(): string
    {
        return $this->language->txt("exc_reminders_cron");
    }

    public function getDescription(): string
    {
        return $this->language->txt("exc_reminders_cron_info");
    }

    public function getDefaultScheduleType(): JobScheduleType
    {
        return JobScheduleType::DAILY;
    }

    public function getDefaultScheduleValue(): ?int
    {
        return null;
    }

    public function hasAutoActivation(): bool
    {
        return true;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function run(): JobResult
    {
        $log = ilLoggerFactory::getLogger("exc");
        $log->debug("--- Start Exercise Reminders Cron");

        $cron_status = JobResult::STATUS_NO_ACTION;
        $message = "";
        $reminder = new ilExAssignmentReminder();
        $num_reminders = $reminder->checkReminders();

        $this->lng->loadLanguageModule("exc");

        if ($num_reminders !== 0) {
            $cron_status = JobResult::STATUS_OK;
            $message = $this->lng->txt('exc_reminder_cron_ok');
        }

        $cron_result = new JobResult();
        $cron_result->setStatus($cron_status);

        if ($message != "") {
            $cron_result->setMessage($message . " " . $num_reminders . ' / ' . "#" . $num_reminders);
        }

        return $cron_result;
    }
}
