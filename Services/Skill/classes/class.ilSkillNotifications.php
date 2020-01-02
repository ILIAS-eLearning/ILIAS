<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Course/group skill notification
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilSkillNotifications extends ilCronJob
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilIniFile
     */
    protected $client_ini;

    /**
     * @var ilTree
     */
    protected $tree;


    /**
     * Constructor
     */
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
    }

    public function getId()
    {
        return "skll_notification";
    }

    public function getTitle()
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("skll");
        return $lng->txt("skll_skill_notification");
    }

    public function getDescription()
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("skll");
        return $lng->txt("skll_skill_notification_desc");
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
        return false;
    }

    public function hasFlexibleSchedule()
    {
        return true;
    }

    public function run()
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


        // $last_run = "2017-07-20 12:00:00";

        // get latest course/group skill changes per user
        include_once("./Services/Skill/classes/class.ilBasicSkill.php");
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
     *
     * @param int $a_user_id
     * @param array $a_objects
     * @param string $a_last_run
     */
    protected function sendMail($a_user_id, array $a_achievements, $a_last_run)
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilClientIniFile = $this->client_ini;
        $tree = $this->tree;

        include_once "./Services/Notification/classes/class.ilSystemNotification.php";
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("skll"));

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);

        include_once './Services/Locator/classes/class.ilLocatorGUI.php';
        require_once "./Services/UICore/classes/class.ilTemplate.php";
        require_once "./Services/Link/classes/class.ilLink.php";


        $tmp = array();
        $txt = "";
        $last_obj_id = 0;

        // order skill achievements per virtual skill tree
        include_once("./Services/Skill/classes/class.ilVirtualSkillTree.php");
        $vtree = new ilVirtualSkillTree();
        $a_achievements = $vtree->getOrderedNodeset($a_achievements, "skill_id", "tref_id");

        foreach ($a_achievements as $skill_level) {
            $parent = array();

            // path
            $path = array();
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
            $txt.= "\n  $date, " . ilBasicSkill::_lookupTitle($skill_level["skill_id"], $skill_level["tref_id"]) . ": " .
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
        $mail->enableSOAP(false); // #10410
        $mail->sendMail(
            ilObjUser::_lookupLogin($a_user_id),
            null,
            null,
            $subject,
            $ntf->composeAndGetMessage($a_user_id, null, "read", true),
            null,
            array("system")
        );
    }
}
