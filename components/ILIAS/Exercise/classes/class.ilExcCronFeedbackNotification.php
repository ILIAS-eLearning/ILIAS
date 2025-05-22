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
use ILIAS\Cron\CronJob;

/**
 * Cron for exercise feedback notification
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @author Alexander Killing <killing@leifos.de>
 */
class ilExcCronFeedbackNotification extends CronJob
{
    protected ilLanguage $lng;


    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
    }

    public function getId(): string
    {
        return "exc_feedback_notification";
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("exc");
        return $lng->txt("exc_global_feedback_file_cron");
    }

    public function getDescription(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("exc");
        return $lng->txt("exc_global_feedback_file_cron_info");
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
        return false;
    }

    /**
     * @throws ilExcUnknownAssignmentTypeException
     */
    public function run(): JobResult
    {
        $status = JobResult::STATUS_NO_ACTION;

        $count = 0;

        foreach (ilExAssignment::getPendingFeedbackNotifications() as $ass_id) {
            if (ilExAssignment::sendFeedbackNotifications($ass_id)) {
                $count++;
            }
        }

        if ($count !== 0) {
            $status = JobResult::STATUS_OK;
        }

        $result = new JobResult();
        $result->setStatus($status);

        return $result;
    }
}
