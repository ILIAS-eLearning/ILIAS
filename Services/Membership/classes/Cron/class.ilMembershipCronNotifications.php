<?php

declare(strict_types=1);



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
 * Course/group notifications
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilMembershipCronNotifications extends ilCronJob
{
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected ilLogger $logger;
    protected ilTree $tree;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->mmbr();
        $this->tree = $DIC->repositoryTree();
    }

    /**
     * @var ilMembershipCronNotificationsData
     */
    protected ilMembershipCronNotificationsData $data;

    public function getId(): string
    {
        return "mem_notification";
    }

    public function getTitle(): string
    {
        return $this->lng->txt("enable_course_group_notifications");
    }

    public function getDescription(): string
    {
        return $this->lng->txt("enable_course_group_notifications_desc");
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

        $this->logger->debug("===Member Notifications=== start");

        $status = ilCronJobResult::STATUS_NO_ACTION;
        $status_details = null;

        $setting = new ilSetting("cron");
        $last_run = $setting->get(get_class($this));

        // no last run?
        if (!$last_run) {
            $last_run = date("Y-m-d H:i:s", strtotime("yesterday"));

            $status_details = "No previous run found - starting from yesterday.";
        } // migration: used to be date-only value
        elseif (strlen($last_run) === 10) {
            $last_run .= " 00:00:00";

            $status_details = "Switched from daily runs to open schedule.";
        }

        // out-comment for debugging purposes
        //$last_run = date("Y-m-d H:i:s", strtotime("yesterday"));

        $last_run_unix = strtotime($last_run);

        $this->logger->debug("Last run: " . $last_run);

        $this->data = new ilMembershipCronNotificationsData($last_run_unix, $this->getId());

        $this->logger->debug("prepare sending mails");

        // send mails (1 max for each user)

        $old_lng = $this->lng;
        $old_dt = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        $user_news_aggr = $this->data->getAggregatedNews();
        if (count($user_news_aggr)) {
            foreach ($user_news_aggr as $user_id => $user_news) {
                $this->logger->debug("sending mails to user " . $user_id . ", nr news: " . count($user_news));
                $this->sendMail($user_id, $user_news, $last_run);
                $DIC->cron()->manager()->ping($this->getId());
            }
            // mails were sent - set cron job status accordingly
            $status = ilCronJobResult::STATUS_OK;
        }

        ilDatePresentation::setUseRelativeDates($old_dt);

        $this->logger->debug("save run");

        // save last run
        $setting->set(get_class($this), date("Y-m-d H:i:s"));
        $result = new ilCronJobResult();
        $result->setStatus($status);
        if ($status_details) {
            $result->setMessage($status_details);
        }
        $this->logger->debug("===Member Notifications=== done");
        return $result;
    }

    /**
     * Convert news item to summary html
     * @throws ilWACException
     * @throws ilDateTimeException
     * @throws ilWACException
     * @throws ilDateTimeException
     */
    protected function parseNewsItem(
        int $a_parent_ref_id,
        array &$a_filter_map,
        array $a_item,
        $a_is_sub = false,
        int $a_user_id = 0
    ): string {
        global $DIC;

        $obj_definiton = $DIC["objDefinition"];

        $this->lng->loadLanguageModule("news");
        $wrong_parent = (array_key_exists($a_item["id"], $a_filter_map) &&
            $a_parent_ref_id != $a_filter_map[$a_item["id"]]);

        // #18223
        if ($wrong_parent) {
            return '';
        }

        $item_obj_title = trim(ilObject::_lookupTitle((int) $a_item["context_obj_id"]));
        $item_obj_type = $a_item["context_obj_type"];

        // sub-items
        $sub = null;
        if ($a_item["aggregation"] ?? false) {
            $do_sub = true;
            if ($item_obj_type === "file" &&
                count($a_item["aggregation"]) === 1) {
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
                (bool) (int) $a_item["content_is_lang_var"],
                (int) ($a_item["agg_ref_id"] ?? 0),
                $a_item["aggregation"] ?? []
            );
        } else {
            $title = ilNewsItem::determineNewsTitle(
                $a_item["context_obj_type"],
                $a_item["title"],
                (bool) (int) $a_item["content_is_lang_var"]
            );
        }

        $content = ilNewsItem::determineNewsContent(
            $a_item["context_obj_type"],
            $a_item["content"],
            (bool) (int) $a_item["content_text_is_lang_var"]
        );

        $title = trim($title);

        // #18067 / #18186
        $content = ilStr::shortenTextExtended(trim(strip_tags($content)), 200, true);

        $res = "";
        switch ($item_obj_type) {
            case "frm":
                if (!$a_is_sub) {
                    $res = $this->lng->txt("obj_" . $item_obj_type) .
                        ' "' . $item_obj_title . '": ' . $title;
                } else {
                    $res .= '"' . $title . '": "' . $content . '"';
                }
                break;

            case "file":
                if (!isset($a_item["aggregation"]) ||
                    count($a_item["aggregation"]) === 1) {
                    $res = $this->lng->txt("obj_" . $item_obj_type) .
                        ' "' . $item_obj_title . '" - ' . $title;
                } else {
                    // if files were removed from aggregation update summary count
                    $title = str_replace(
                        " " . count($a_item["aggregation"]) . " ",
                        " " . count($sub) . " ",
                        $title
                    );
                    $res = $title;
                }
                break;

            default:
                $type_txt = ($obj_definiton->isPlugin($item_obj_type))
                    ? ilObjectPlugin::lookupTxtById($item_obj_type, "obj_" . $item_obj_type)
                    : $this->lng->txt("obj_" . $item_obj_type);
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
        $comments = $this->data->getComments((int) $a_item["id"], $a_user_id);
        if (count($comments) > 0) {
            $res .= "\n" . $this->lng->txt("news_new_comments") . " (" . count($comments) . ")";
        }
        /** @var \ILIAS\Notes\Note $c */
        foreach ($comments as $c) {
            $res .= "\n* " .
                ilUserUtil::getNamePresentation($c->getAuthor()) . ", " . ilDatePresentation::formatDate(
                    new ilDateTime($c->getCreationDate(), IL_CAL_DATETIME)
                ) . ": " .
                ilStr::shortenTextExtended(trim(strip_tags($c->getText())), 60, true, true);
        }

        // likes
        $likes = $this->data->getLikes((int) $a_item["id"], $a_user_id);
        if (count($likes) > 0) {
            $res .= "\n" . $this->lng->txt("news_new_reactions") . " (" . count($likes) . ")";
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

        if (is_array($sub) && count($sub)) {
            $res .= "\n" . implode("\n", $sub);
        }
        // see 29967
        $res = str_replace("<br />", " ", $res);
        $res = strip_tags($res);

        return trim($res);
    }

    /**
     * Filter duplicate news items from structure*
     */
    protected function filterDuplicateItems(array $a_objects): array
    {
        $parent_map = $news_map = $parsed_map = array();

        // gather news ref ids and news parent ref ids
        foreach ($a_objects as $parent_ref_id => $news) {
            foreach ($news as $item) {
                $news_map[$item["id"]] = $item["ref_id"];
                $parent_map[$item["id"]][$parent_ref_id] = $parent_ref_id;

                if ($item["aggregation"] ?? false) {
                    foreach ($item["aggregation"] as $subitem) {
                        $news_map[$subitem["id"]] = $subitem["ref_id"];
                        $parent_map[$subitem["id"]][$parent_ref_id] = $parent_ref_id;
                    }
                }
            }
        }
        // if news has multiple parents find "lowest" parent in path
        foreach ($parent_map as $news_id => $parents) {
            if (count($parents) > 1 && $news_map[$news_id] > 0) {
                $path = $this->tree->getPathId($news_map[$news_id]);
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
     * @throws ilDateTimeException|ilWACException
     */
    protected function sendMail(int $a_user_id, array $a_objects, string $a_last_run): void
    {
        global $DIC;

        $ilClientIniFile = $DIC['ilClientIniFile'];

        $ntf = new ilSystemNotification();
        $ntf->setLangModules(array("crs", "news"));
        // no single object anymore
        // $ntf->setRefId($a_ref_id);
        // $ntf->setGotoLangId('url');
        // $ntf->setSubjectLangId('crs_subject_course_group_notification');

        // user specific language
        $lng = $ntf->getUserLanguage($a_user_id);
        $filter_map = $this->filterDuplicateItems($a_objects);

        $tmp = array();

        foreach ($a_objects as $parent_ref_id => $items) {
            $parent = array();

            // path
            $path = array();
            foreach ($this->tree->getPathId($parent_ref_id) as $node) {
                $path[] = $node;
            }
            $path = implode("-", $path);

            $parent_obj_id = ilObject::_lookupObjId((int) $parent_ref_id);
            $parent_type = ilObject::_lookupType($parent_obj_id);

            $parent["title"] = $lng->txt("obj_" . $parent_type) . ' "' . ilObject::_lookupTitle($parent_obj_id) . '"';
            $parent["url"] = "  " . $lng->txt("crs_course_group_notification_link") . " " . ilLink::_getStaticLink($parent_ref_id);

            $this->logger->debug("ref id: " . $parent_ref_id . ", items: " . count($items));

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
            if (count($parsed)) {
                $parent["news"] = implode("\n\n", $parsed);
                $tmp[$path] = $parent;
            }
        }

        // any objects?
        if (!count($tmp)) {
            $this->logger->debug("returning");
            return;
        }

        ksort($tmp);
        $counter = 0;
        $obj_index = array();
        $txt = "";
        foreach ($tmp as $item) {
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

        $mail_content = $ntf->composeAndGetMessage($a_user_id, null, "read", true);
        $this->logger->debug("sending mail content: " . $mail_content);

        // #10044
        $mail = new ilMail(ANONYMOUS_USER_ID);
        $mail->enqueue(
            ilObjUser::_lookupLogin($a_user_id),
            (string) null,
            (string) null,
            $subject,
            $mail_content,
            []
        );
    }

    public function addToExternalSettingsForm(int $a_form_id, array &$a_fields, bool $a_is_active): void
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_COURSE:
            case ilAdministrationSettingsFormHandler::FORM_GROUP:
                $a_fields["enable_course_group_notifications"] = $a_is_active ?
                    $this->lng->txt("enabled") :
                    $this->lng->txt("disabled");
                break;
        }
    }

    public function activationWasToggled(ilDBInterface $db, ilSetting $setting, bool $a_currently_active): void
    {
        $setting->set("crsgrp_ntf", (string) ((int) $a_currently_active));
    }
}
