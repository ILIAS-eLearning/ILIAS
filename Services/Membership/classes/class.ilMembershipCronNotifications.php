<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Course/group notifications
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilMembershipCronNotifications extends ilCronJob
{
    /**
     * @var ilMembershipCronNotificationsData
     */
    protected $data;

    public function getId()
    {
        return "mem_notification";
    }
    
    public function getTitle()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("enable_course_group_notifications");
    }
    
    public function getDescription()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        return $lng->txt("enable_course_group_notifications_desc");
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

        $lng = $DIC['lng'];
        $ilDB = $DIC['ilDB'];

        $log = $DIC->logger()->mmbr();
        $log->debug("===Member Notifications=== start");
        
        $status = ilCronJobResult::STATUS_NO_ACTION;
        $status_details = null;
    
        $setting = new ilSetting("cron");
        $last_run = $setting->get(get_class($this));
                
        // no last run?
        if (!$last_run) {
            $last_run = date("Y-m-d H:i:s", strtotime("yesterday"));
            
            $status_details = "No previous run found - starting from yesterday.";
        }
        // migration: used to be date-only value
        elseif (strlen($last_run) == 10) {
            $last_run .= " 00:00:00";
            
            $status_details = "Switched from daily runs to open schedule.";
        }
        //$last_run = date("Y-m-d H:i:s", strtotime("yesterday"));

        $log->debug("Last run: " . $last_run);

        include_once("./Services/Membership/classes/class.ilMembershipCronNotificationsData.php");
        $this->data = new ilMembershipCronNotificationsData($last_run, $this->getId());

        $log->debug("prepare sending mails");

        // send mails (1 max for each user)

        $old_lng = $lng;
        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $user_news_aggr = $this->data->getAggregatedNews();
        if (sizeof($user_news_aggr)) {
            foreach ($user_news_aggr as $user_id => $user_news) {
                $log->debug("sending mails to user " . $user_id . ", nr news: " . count($user_news));

                $this->sendMail($user_id, $user_news, $last_run);

                ilCronManager::ping($this->getId());
            }

            // mails were sent - set cron job status accordingly
            $status = ilCronJobResult::STATUS_OK;
        }

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

        $log->debug("===Member Notifications=== done");

        return $result;
    }

    /**
     * Convert news item to summary html
     *
     * @param int $a_parent_ref_id
     * @param array $a_filter_map
     * @param array $a_item
     * @param bool $a_is_sub
     * @return string
     */
    protected function parseNewsItem($a_parent_ref_id, array &$a_filter_map, array $a_item, $a_is_sub = false, $a_user_id = 0)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $obj_definiton = $DIC["objDefinition"];

        $lng->loadLanguageModule("news");
        
        $wrong_parent = (array_key_exists($a_item["id"], $a_filter_map) &&
                $a_parent_ref_id != $a_filter_map[$a_item["id"]]);
        
        // #18223
        if ($wrong_parent) {
            return;
        }
        
        $item_obj_title = trim(ilObject::_lookupTitle($a_item["context_obj_id"]));
        $item_obj_type = $a_item["context_obj_type"];
        
        // sub-items
        $sub = null;
        if ($a_item["aggregation"]) {
            $do_sub = true;
            if ($item_obj_type == "file" &&
                sizeof($a_item["aggregation"]) == 1) {
                $do_sub = false;
            }
            if ($do_sub) {
                $sub = array();
                foreach ($a_item["aggregation"] as $subitem) {
                    $sub_res = $this->parseNewsItem($a_parent_ref_id, $a_filter_map, $subitem, true, $a_user_id);
                    if ($sub_res) {
                        $sub[md5($sub_res)] = $sub_res;
                    }
                }
            }
        }
        
        if (!$a_is_sub) {
            $title = ilNewsItem::determineNewsTitle(
                $a_item["context_obj_type"],
                $a_item["title"],
                $a_item["content_is_lang_var"],
                $a_item["agg_ref_id"],
                $a_item["aggregation"]
            );
        } else {
            $title = ilNewsItem::determineNewsTitle(
                $a_item["context_obj_type"],
                $a_item["title"],
                $a_item["content_is_lang_var"]
            );
        }
        
        $content = ilNewsItem::determineNewsContent(
            $a_item["context_obj_type"],
            $a_item["content"],
            $a_item["content_text_is_lang_var"]
        );
        
        $title = trim($title);
        
        // #18067 / #18186
        $content = ilUtil::shortenText(trim(strip_tags($content)), 200, true);
        
        $res = "";
        switch ($item_obj_type) {
            case "frm":
                if (!$a_is_sub) {
                    $res =  $lng->txt("obj_" . $item_obj_type) .
                        ' "' . $item_obj_title . '": ' . $title;
                } else {
                    $res .= '"' . $title . '": "' . $content . '"';
                }
                break;
                
            case "file":
                if (!is_array($a_item["aggregation"]) ||
                    sizeof($a_item["aggregation"]) == 1) {
                    $res =  $lng->txt("obj_" . $item_obj_type) .
                        ' "' . $item_obj_title . '" - ' . $title;
                } else {
                    // if files were removed from aggregation update summary count
                    $title = str_replace(
                        " " . sizeof($a_item["aggregation"]) . " ",
                        " " . sizeof($sub) . " ",
                        $title
                    );
                    $res = $title;
                }
                break;
                
            default:
                $type_txt = ($obj_definiton->isPlugin($item_obj_type))
                    ? ilObjectPlugin::lookupTxtById($item_obj_type, "obj_" . $item_obj_type)
                    : $lng->txt("obj_" . $item_obj_type);
                $res = $type_txt .
                    ' "' . $item_obj_title . '"';
                if ($title) {
                    $res .= ': "' . $title . '"';
                }
                if ($content) {
                    $res .= ' - ' . $content;
                }
                break;
        }

        // comments
        $comments = $this->data->getComments($a_item["id"], $a_user_id);
        if (count($comments) > 0) {
            $res .= "\n" . $lng->txt("news_new_comments") . " (" . count($comments) . ")";
        }
        foreach ($comments as $c) {
            $res .= "\n* " .
                ilUserUtil::getNamePresentation($c->getAuthor()) . ", " . ilDatePresentation::formatDate(
                    new ilDateTime($c->getCreationDate(), IL_CAL_DATETIME)
                ) . ": " .
                ilUtil::shortenText(trim(strip_tags($c->getText())), 60, true, true);
        }


        // likes
        include_once("./Services/Like/classes/class.ilLikeGUI.php");
        $likes = $this->data->getLikes($a_item["id"], $a_user_id);
        if (count($likes) > 0) {
            $res .= "\n" . $lng->txt("news_new_reactions") . " (" . count($likes) . ")";
        }
        foreach ($likes as $l) {
            $res .= "\n* " .
                ilUserUtil::getNamePresentation($l["user_id"]) . ", " . ilDatePresentation::formatDate(
                    new ilDateTime($l["timestamp"], IL_CAL_DATETIME)
                ) . ": " .
                ilLikeGUI::getExpressionText((int) $l["expression"]);
        }

        
        $res = $a_is_sub
            ? "- " . $res
            : "# " . $res;
        
        if (is_array($sub) && sizeof($sub)) {
            $res .= "\n" . implode("\n", $sub);
        }
        
        return trim($res);
    }

    /**
     * Filter duplicate news items from structure
     *
     * @param array $a_objects
     * @return array
     */
    protected function filterDuplicateItems(array $a_objects)
    {
        global $DIC;

        $tree = $DIC['tree'];
        
        $parent_map = $news_map = $parsed_map = array();
        
        // gather news ref ids and news parent ref ids
        foreach ($a_objects as $parent_ref_id => $news) {
            foreach ($news as $item) {
                $news_map[$item["id"]] = $item["ref_id"];
                $parent_map[$item["id"]][$parent_ref_id] = $parent_ref_id;
                
                if ($item["aggregation"]) {
                    foreach ($item["aggregation"] as $subitem) {
                        $news_map[$subitem["id"]] = $subitem["ref_id"];
                        $parent_map[$subitem["id"]][$parent_ref_id] = $parent_ref_id;
                    }
                }
            }
        }
        // if news has multiple parents find "lowest" parent in path
        foreach ($parent_map as $news_id => $parents) {
            if (sizeof($parents) > 1 && $news_map[$news_id] > 0) {
                $path = $tree->getPathId($news_map[$news_id]);
                $lookup = array_flip($path);
                
                $level = 0;
                foreach ($parents as $parent_ref_id) {
                    $level = max($level, $lookup[$parent_ref_id]);
                }
                
                $parsed_map[$news_id] = $path[$level];
            }
        }
        
        return $parsed_map;
    }

    /**
     * Send news mail for 1 user and n objects
     *
     * @param int $a_user_id
     * @param array $a_objects
     * @param string $a_last_run
     */
    protected function sendMail($a_user_id, array $a_objects, $a_last_run)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilClientIniFile = $DIC['ilClientIniFile'];
        $tree = $DIC['tree'];
        
        $log = ilLoggerFactory::getLogger("mmbr");

        include_once "./Services/Notification/classes/class.ilSystemNotification.php";
        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("crs", "news"));
        // no single object anymore
        // $ntf->setRefId($a_ref_id);
        // $ntf->setGotoLangId('url');
        // $ntf->setSubjectLangId('crs_subject_course_group_notification');
        
        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);
        
        include_once './Services/Locator/classes/class.ilLocatorGUI.php';
        require_once "./Services/UICore/classes/class.ilTemplate.php";
        require_once "./Services/Link/classes/class.ilLink.php";
            
        $filter_map = $this->filterDuplicateItems($a_objects);
        
        $tmp = array();

        foreach ($a_objects as $parent_ref_id => $items) {
            $parent = array();

            // path
            $path = array();
            foreach ($tree->getPathId($parent_ref_id) as $node) {
                $path[] = $node;
            }
            $path = implode("-", $path);
            
            $parent_obj_id = ilObject::_lookupObjId($parent_ref_id);
            $parent_type = ilObject::_lookupType($parent_obj_id);
            
            $parent["title"] = $lng->txt("obj_" . $parent_type) . ' "' . ilObject::_lookupTitle($parent_obj_id) . '"';
            $parent["url"] = "  " . $lng->txt("crs_course_group_notification_link") . " " . ilLink::_getStaticLink($parent_ref_id);

            $log->debug("ref id: " . $parent_ref_id . ", items: " . count($items));

            // news summary
            $parsed = array();
            if (is_array($items)) {
                foreach ($items as $news_item) {
                    // # Type "<Object Title>": "<News Title>" - News Text
                    $parsed_item = $this->parseNewsItem($parent_ref_id, $filter_map, $news_item, false, $a_user_id);
                    if ($parsed_item) {
                        $parsed[md5($parsed_item)] = $parsed_item;
                    }
                }
            }

            // any news?
            if (sizeof($parsed)) {
                $parent["news"] = implode("\n\n", $parsed);
                $tmp[$path] = $parent;
            }
        }
        
        // any objects?
        if (!sizeof($tmp)) {
            $log->debug("returning");
            return;
        }
        
        ksort($tmp);
        $counter = 0;
        $obj_index = array();
        $txt = "";
        foreach ($tmp as $path => $item) {
            $counter++;
            
            $txt .= "(" . $counter . ") " . $item["title"] . "\n" .
                $item["url"] . "\n\n" .
                $item["news"] . "\n\n";
            
            $obj_index[] = "(" . $counter . ") " . $item["title"];
        }
        
        $ntf->setIntroductionLangId("crs_intro_course_group_notification_for");
        
        // index
        $period = sprintf(
            $lng->txt("crs_intro_course_group_notification_index"),
            ilDatePresentation::formatDate(new ilDateTime($a_last_run, IL_CAL_DATETIME)),
            ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX))
        );
        $ntf->addAdditionalInfo(
            $period,
            trim(implode("\n", $obj_index)),
            true,
            true
        );
        
        // object list
        $ntf->addAdditionalInfo(
            "",
            trim($txt),
            true
        );
        
        // :TODO: does it make sense to add client to subject?
        $client = $ilClientIniFile->readVariable('client', 'name');
        $subject = sprintf($lng->txt("crs_subject_course_group_notification"), $client);
            
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
    
    public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_COURSE:
            case ilAdministrationSettingsFormHandler::FORM_GROUP:
                $a_fields["enable_course_group_notifications"] = $a_is_active ?
                    $lng->txt("enabled") :
                    $lng->txt("disabled");
                break;
        }
    }
    
    public function activationWasToggled($a_currently_active)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
                
        // propagate cron-job setting to object setting
        $ilSetting->set("crsgrp_ntf", (bool) $a_currently_active);
    }
}
