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
 ********************************************************************
 */

use ILIAS\Skill\Service\SkillTreeService;

/**
 * Course/group skill notification
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilSkillNotifications extends ilCronJob
{
    protected ilLanguage $lng;
    protected ilObjUser $user;
    protected ilIniFile $client_ini;
    protected ilTree $tree;
    protected SkillTreeService $tree_service;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        if (isset($DIC["ilUser"])) {
            $this->user = $DIC->user();
        }
        if (isset($DIC["ilClientIniFile"])) {
            $this->client_ini = $DIC["ilClientIniFile"];
        }
        if (isset($DIC["tree"])) {
            $this->tree = $DIC->repositoryTree();
        }
        $this->tree_service = $DIC->skills()->tree();
    }

    public function getId(): string
    {
        return "skll_notification";
    }

    public function getTitle(): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("skll");
        return $lng->txt("skll_skill_notification");
    }

    public function getDescription(): string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("skll");
        return $lng->txt("skll_skill_notification_desc");
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
        return false;
    }

    public function hasFlexibleSchedule(): bool
    {
        return true;
    }

    public function run(): ilCronJobResult
    {
        global $DIC;

        $lng = $DIC->language();

        $log = ilLoggerFactory::getLogger("skll");
        $log->debug("===Skill Notifications=== start");

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $status_details = null;

        $setting = new ilSetting("cron");
        $last_run = $setting->get(get_class($this));

        // no last run?
        if (!$last_run) {
            $last_run = date("Y-m-d H:i:s", strtotime("yesterday"));

            $status_details = "No previous run found - starting from yesterday.";
        } // migration: used to be date-only value
        elseif (strlen($last_run) == 10) {
            $last_run .= " 00:00:00";

            $status_details = "Switched from daily runs to open schedule.";
        }

        // init language/date
        $old_lng = $lng;
        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);


        // get latest course/group skill changes per user
        $achievements = ilBasicSkill::getNewAchievementsPerUser($last_run);

        foreach ($achievements as $user_id => $a) {
            $this->sendMail($user_id, $a, $last_run);
        }

        // mails were sent - set cron job status accordingly
        $status = ilCronJobResult::STATUS_OK;

        // reset language/date
        ilDatePresentation::setUseRelativeDates($old_dt);
        $lng = $old_lng;

        $log->debug("save run");

        // save last run
        $setting->set(get_class($this), date("Y-m-d H:i:s"));

        $result = new ilCronJobResult();
        $result->setStatus($status);

        if ($status_details) {
            $result->setMessage($status_details);
        }

        $log->debug("===Skill Notifications=== done");

        return $result;
    }

    /**
     * Send news mail for 1 user and n objects
     */
    protected function sendMail(int $a_user_id, array $a_achievements, string $a_last_run): void
    {
        $ilClientIniFile = $this->client_ini;
        $tree = $this->tree;

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("skll"));

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);

        $txt = "";
        $last_obj_id = 0;

        // order skill achievements per virtual skill tree
        $vtree = $this->tree_service->getGlobalVirtualSkillTree();
        $a_achievements = $vtree->getOrderedNodeset($a_achievements, "skill_id", "tref_id");

        foreach ($a_achievements as $skill_level) {

            // path
            $path = [];
            foreach ($tree->getPathId($skill_level["trigger_ref_id"]) as $node) {
                $path[] = $node;
            }
            $path = implode("-", $path);

            $ref_id = $skill_level["trigger_ref_id"];
            $obj_id = $skill_level["trigger_obj_id"];
            $type = $skill_level["trigger_obj_type"];
            $title = $skill_level["trigger_title"];

            if ($skill_level["trigger_obj_id"] != $last_obj_id) {
                $last_obj_id = $skill_level["trigger_obj_id"];
                $txt .= "\n\n" . $lng->txt("obj_" . $type) . ": " . $title;
                if ($tree->isInTree($ref_id)) {
                    $txt .= "\n" . ilLink::_getStaticLink($ref_id);
                }
            }

            $date = ilDatePresentation::formatDate(new ilDateTime($skill_level["status_date"], IL_CAL_DATETIME));
            $txt .= "\n  $date, " . ilBasicSkill::_lookupTitle($skill_level["skill_id"], $skill_level["tref_id"]) . ": " .
                ilBasicSkill::lookupLevelTitle($skill_level["level_id"]);
        }

        $ntf->setIntroductionLangId("skll_intro_skill_notification_for");

        // index
        $period = sprintf(
            $lng->txt("skll_new_skill_achievements"),
            ilDatePresentation::formatDate(new ilDateTime($a_last_run, IL_CAL_DATETIME)),
            ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX))
        );

        // text
        $ntf->addAdditionalInfo(
            "",
            trim($txt),
            true
        );

        // :TODO: does it make sense to add client to subject?
        $client = $ilClientIniFile->readVariable('client', 'name');
        $subject = sprintf($lng->txt("skll_competence_achievements"), $client);

        //die($ntf->composeAndGetMessage($a_user_id, null, "read", true));

        // #10044
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_user_id),
            '',
            '',
            $subject,
            $ntf->composeAndGetMessage($a_user_id, null, "read", true),
            []
        );
    }
}
