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

use ILIAS\News\StandardGUIRequest;

/**
 * Personal desktop news table
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPDNewsTableGUI extends ilTable2GUI
{
    protected string $selected_context;
    /**
     * @var array<string,string>
     */
    protected array $contexts;
    protected ilObjUser $user;
    protected StandardGUIRequest $std_request;

    public function __construct(
        ilPDNewsGUI $a_parent_obj,
        string $a_parent_cmd,
        array $a_contexts,
        string $a_selected_context
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->contexts = $a_contexts;
        $this->selected_context = $a_selected_context;
        $this->addColumn("");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.table_row_pd_news.html",
            "Services/News"
        );
        $this->setDefaultOrderField("update_date");
        $this->setDefaultOrderDirection("desc");
        $this->setEnableTitle(false);
        $this->setEnableHeader(false);
        $this->setIsDataTable(false);
        $this->initFilter();
    }
    
    public function initFilter() : void
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        
        // period
        $per = (ilSession::get("news_pd_news_per") != "")
            ? ilSession::get("news_pd_news_per")
            : ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
        $news_set = new ilSetting("news");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $default_per = ilNewsItem::_lookupDefaultPDPeriod();

        $options = [
            2 => sprintf($lng->txt("news_period_x_days"), 2),
            3 => sprintf($lng->txt("news_period_x_days"), 3),
            5 => sprintf($lng->txt("news_period_x_days"), 5),
            7 => $lng->txt("news_period_1_week"),
            14 => sprintf($lng->txt("news_period_x_weeks"), 2),
            30 => $lng->txt("news_period_1_month"),
            60 => sprintf($lng->txt("news_period_x_months"), 2),
            120 => sprintf($lng->txt("news_period_x_months"), 4),
            180 => sprintf($lng->txt("news_period_x_months"), 6),
            366 => $lng->txt("news_period_1_year")
        ];
            
        $unset = [];
        foreach ($options as $k => $opt) {
            if (!$allow_shorter_periods && ($k < $default_per)) {
                $unset[$k] = $k;
            }
            if (!$allow_longer_periods && ($k > $default_per)) {
                $unset[$k] = $k;
            }
        }
        foreach ($unset as $k) {
            unset($options[$k]);
        }

        $si = new ilSelectInputGUI($this->lng->txt("news_time_period"), "news_per");
        $si->setOptions($options);
        $si->setValue((string) $per);
        $this->addFilterItem($si);
        
        // related to...
        $si = new ilSelectInputGUI($this->lng->txt("context"), "news_ref_id");
        $si->setOptions($this->contexts);
        $si->setValue($this->selected_context);
        $this->addFilterItem($si);
    }
    
    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        // context
        $obj_id = ilObject::_lookupObjId((int) $a_set["ref_id"]);
        $obj_type = ilObject::_lookupType($obj_id);
        $obj_title = ilObject::_lookupTitle($obj_id);
            
        // user
        if ($a_set["user_id"] > 0) {
            $this->tpl->setCurrentBlock("user_info");
            if ($obj_type === "frm") {
                if (ilForumProperties::_isAnonymized((int) $a_set["context_obj_id"])) {
                    if ($a_set["context_sub_obj_type"] === "pos" &&
                        $a_set["context_sub_obj_id"] > 0) {
                        $post = new ilForumPost((int) $a_set["context_sub_obj_id"]);
                        if (is_string($post->getUserAlias()) && $post->getUserAlias() !== '') {
                            $this->tpl->setVariable("VAL_AUTHOR", ilUtil::stripSlashes($post->getUserAlias()));
                        } else {
                            $this->tpl->setVariable("VAL_AUTHOR", $lng->txt("forums_anonymous"));
                        }
                    } else {
                        $this->tpl->setVariable("VAL_AUTHOR", $lng->txt("forums_anonymous"));
                    }
                } elseif (ilObject::_exists((int) $a_set["user_id"])) {
                    $user_obj = new ilObjUser((int) $a_set["user_id"]);
                    $this->tpl->setVariable("VAL_AUTHOR", $user_obj->getLogin());
                }
            } elseif (ilObject::_exists((int) $a_set["user_id"])) {
                $this->tpl->setVariable("VAL_AUTHOR", ilObjUser::_lookupLogin((int) $a_set["user_id"]));
            }
            $this->tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
            $this->tpl->parseCurrentBlock();
        }
        
        // media player
        if ($a_set["content_type"] === NEWS_AUDIO &&
            $a_set["mob_id"] > 0 && ilObject::_exists((int) $a_set["mob_id"])) {
            $mob = new ilObjMediaObject((int) $a_set["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            $mpl = new ilMediaPlayerGUI();
            $mpl->setFile(ilObjMediaObject::_getDirectory((int) $a_set["mob_id"]) . "/" .
                $med->getLocation());
            $this->tpl->setCurrentBlock("player");
            $this->tpl->setVariable(
                "PLAYER",
                $mpl->getMp3PlayerHtml()
            );
            $this->tpl->parseCurrentBlock();
        }
        
        // access
        if ($enable_internal_rss) {
            $this->tpl->setCurrentBlock("access");
            $this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
            if ($a_set["visibility"] === NEWS_PUBLIC ||
                ((int) $a_set["priority"] === 0 &&
                ilBlockSetting::_lookup(
                    "news",
                    "public_notifications",
                    0,
                    $obj_id
                ))) {
                $this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
            } else {
                $this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
            }
            $this->tpl->parseCurrentBlock();
        }

        // content
        if ($a_set["content"] != "") {
            $this->tpl->setCurrentBlock("content");
            $this->tpl->setVariable(
                "VAL_CONTENT",
                nl2br($this->makeClickable(
                    ilNewsItem::determineNewsContent(
                        $a_set["context_obj_type"],
                        $a_set["content"],
                        (bool) $a_set["content_text_is_lang_var"]
                    )
                ))
            );
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set["content_long"] != "") {
            $this->tpl->setCurrentBlock("long");
            $this->tpl->setVariable("VAL_LONG_CONTENT", ilUtil::makeClickable($a_set["content_long"], true));
            $this->tpl->parseCurrentBlock();
        }
        if ($a_set["update_date"] != $a_set["creation_date"]) {	// update date
            $this->tpl->setCurrentBlock("ni_update");
            $this->tpl->setVariable("TXT_LAST_UPDATE", $lng->txt("last_update"));
            $this->tpl->setVariable(
                "VAL_LAST_UPDATE",
                ilDatePresentation::formatDate(new ilDateTime($a_set["update_date"], IL_CAL_DATETIME))
            );
            $this->tpl->parseCurrentBlock();
        }

        // forum hack, not nice
        $add = "";
        if ($obj_type === "frm" && $a_set["context_sub_obj_type"] === "pos"
            && $a_set["context_sub_obj_id"] > 0) {
            $pos = $a_set["context_sub_obj_id"];
            $thread = ilObjForumAccess::_getThreadForPosting((int) $pos);
            if ($thread > 0) {
                $add = "_" . $thread . "_" . $pos;
            }
        }

        // file hack, not nice
        if ($obj_type === "file") {
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_set["ref_id"]);
            $url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "sendfile");
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->std_request->getRefId());

            $button = ilLinkButton::getInstance();
            $button->setUrl($url);
            $button->setCaption("download");

            $this->tpl->setCurrentBlock("download");
            $this->tpl->setVariable("BUTTON_DOWNLOAD", $button->render());
            $this->tpl->parseCurrentBlock();
        }

        // wiki hack, not nice
        if ($obj_type === "wiki" && $a_set["context_sub_obj_type"] === "wpg"
            && $a_set["context_sub_obj_id"] > 0) {
            $wptitle = ilWikiPage::lookupTitle((int) $a_set["context_sub_obj_id"]);
            if ($wptitle != "") {
                $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
            }
        }


        $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
            $obj_type . "_" . $a_set["ref_id"] . $add;

        // lm page hack, not nice
        if ($a_set["context_sub_obj_type"] === "pg" &&
            $a_set["context_sub_obj_id"] > 0 &&
            in_array($obj_type, ["dbk", "lm"], true)) {
            $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
                "pg_" . $a_set["context_sub_obj_id"] . "_" . $a_set["ref_id"];
        }


        $this->tpl->setCurrentBlock("context");
        $cont_loc = new ilLocatorGUI();
        $cont_loc->addContextItems($a_set["ref_id"], true);
        $this->tpl->setVariable(
            "CONTEXT_LOCATOR",
            $cont_loc->getHTML()
        );
        $this->tpl->setVariable("HREF_CONTEXT_TITLE", $url_target);
        $this->tpl->setVariable("CONTEXT_TITLE", $obj_title);
        $this->tpl->setVariable(
            "ALT_CONTEXT_TITLE",
            $lng->txt("icon") . " " . $lng->txt("obj_" . $obj_type)
        );
        $this->tpl->setVariable(
            "IMG_CONTEXT_TITLE",
            ilObject::_getIcon((int) $a_set["context_obj_id"])
        );
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable("HREF_TITLE", $url_target);
        
        // title
        $this->tpl->setVariable(
            "VAL_TITLE",
            ilNewsItem::determineNewsTitle($a_set["context_obj_type"], $a_set["title"], $a_set["content_is_lang_var"])
        );

        // creation date
        $this->tpl->setVariable(
            "VAL_CREATION_DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["creation_date"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("TXT_CREATED", $lng->txt("created"));

        $this->tpl->parseCurrentBlock();
    }

    public function makeClickable(string $a_str) : string
    {
        // this fixes bug 8744. We assume that strings that contain < and >
        // already contain html, we do not handle these
        if (is_int(strpos($a_str, ">")) && is_int(strpos($a_str, "<"))) {
            return $a_str;
        }

        return ilUtil::makeClickable($a_str);
    }
}
