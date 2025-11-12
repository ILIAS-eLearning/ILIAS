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
 * Cron for survey notifications
 * (reminder to paricipate in the survey)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyCronNotification extends CronJob
{
    protected const MAX_MESSAGE_LENGTH = 397;

    protected ilLanguage $lng;
    protected ilTree $tree;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        if (isset($DIC["tree"])) {
            $this->tree = $DIC->repositoryTree();
        }
    }

    public function getId(): string
    {
        return "survey_notification";
    }

    public function getTitle(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("survey");
        return $lng->txt("survey_reminder_cron");
    }

    public function getDescription(): string
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("survey");
        return $lng->txt("survey_reminder_cron_info");
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

    public function run(): JobResult
    {
        global $tree;

        $log = ilLoggerFactory::getLogger("svy");
        $log->debug("start");

        $status = JobResult::STATUS_NO_ACTION;
        $message = array();

        $root = $tree->getNodeData(ROOT_FOLDER_ID);
        foreach ($tree->getSubTree($root, false, ["svy"]) as $svy_ref_id) {
            $svy = new ilObjSurvey($svy_ref_id);
            $num = $svy->checkReminder();
            if (!is_null($num)) {
                $message[] = $svy_ref_id . "(" . $num . ")";
                $status = JobResult::STATUS_OK;
            }
        }

        $result = new JobResult();
        $result->setStatus($status);

        if (count($message)) {
            $full_msg = "Ref-Ids: " . implode(", ", $message) . ' / ' . "#" . count($message);

            if (mb_strlen($full_msg) > self::MAX_MESSAGE_LENGTH) {
                $short_msg = mb_substr($full_msg, 0, self::MAX_MESSAGE_LENGTH) . '...';
                $log->info("Notification message was truncated to fit DB limit. Full message: \"$full_msg\"");
            } else {
                $short_msg = $full_msg;
            }

            $result->setMessage($short_msg);
        }

        $log->debug("end");
        return $result;
    }
}
