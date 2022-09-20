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

/**
 * Cron for survey notifications
 * (reminder to paricipate in the survey)
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilSurveyCronNotification extends ilCronJob
{
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

    public function getDefaultScheduleType(): int
    {
        return self::SCHEDULE_TYPE_DAILY;
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

    public function run(): ilCronJobResult
    {
        global $tree;

        $log = ilLoggerFactory::getLogger("svy");
        $log->debug("start");

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $message = array();

        $root = $tree->getNodeData(ROOT_FOLDER_ID);
        foreach ($tree->getSubTree($root, false, ["svy"]) as $svy_ref_id) {
            $svy = new ilObjSurvey($svy_ref_id);
            $num = $svy->checkReminder();
            if (!is_null($num)) {
                $message[] = $svy_ref_id . "(" . $num . ")";
                $status = ilCronJobResult::STATUS_OK;
            }
        }

        $result = new ilCronJobResult();
        $result->setStatus($status);

        if (count($message)) {
            $result->setMessage("Ref-Ids: " . implode(", ", $message) . ' / ' . "#" . count($message));
        }
        $log->debug("end");
        return $result;
    }
}
