<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* Personal desktop news table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilPDNewsTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilObjUser
     */
    protected $user;


    public function __construct(
        $a_parent_obj,
        $a_parent_cmd = "",
        $a_contexts,
        $a_selected_context
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();

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
    
    /**
    * Init Filter
    */
    public function initFilter()
    {
        $lng = $this->lng;
        $ilUser = $this->user;
        
        // period
        $per = ($_SESSION["news_pd_news_per"] != "")
            ? $_SESSION["news_pd_news_per"]
            : ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
        $news_set = new ilSetting("news");
        $allow_shorter_periods = $news_set->get("allow_shorter_periods");
        $allow_longer_periods = $news_set->get("allow_longer_periods");
        $default_per = ilNewsItem::_lookupDefaultPDPeriod();

        $options = array(
            2 => sprintf($lng->txt("news_period_x_days"), 2),
            3 => sprintf($lng->txt("news_period_x_days"), 3),
            5 => sprintf($lng->txt("news_period_x_days"), 5),
            7 => $lng->txt("news_period_1_week"),
            14 => sprintf($lng->txt("news_period_x_weeks"), 2),
            30 => $lng->txt("news_period_1_month"),
            60 => sprintf($lng->txt("news_period_x_months"), 2),
            120 => sprintf($lng->txt("news_period_x_months"), 4),
            180 => sprintf($lng->txt("news_period_x_months"), 6),
            366 => $lng->txt("news_period_1_year"));
            
        $unset = array();
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

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($this->lng->txt("news_time_period"), "news_per");
        $si->setOptions($options);
        $si->setValue($per);
        $this->addFilterItem($si);
        
        // related to...
        $si = new ilSelectInputGUI($this->lng->txt("context"), "news_ref_id");
        $si->setOptions($this->contexts);
        $si->setValue($this->selected_context);
        $this->addFilterItem($si);
    }
    
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $news_set = new ilSetting("news");
        $enable_internal_rss = $news_set->get("enable_rss_for_internal");

        // context
        $obj_id = ilObject::_lookupObjId($a_set["ref_id"]);
        $obj_type = ilObject::_lookupType($obj_id);
        $obj_title = ilObject::_lookupTitle($obj_id);
            
        // user
        if ($a_set["user_id"] > 0) {
            $this->tpl->setCurrentBlock("user_info");
            if ($obj_type == "frm") {
                include_once("./Modules/Forum/classes/class.ilForumProperties.php");
                if (ilForumProperties::_isAnonymized($a_set["context_obj_id"])) {
                    if ($a_set["context_sub_obj_type"] == "pos" &&
                        $a_set["context_sub_obj_id"] > 0) {
                        include_once("./Modules/Forum/classes/class.ilForumPost.php");
                        $post = new ilForumPost($a_set["context_sub_obj_id"]);
                        if ($post->getUserAlias() != "") {
                            $this->tpl->setVariable("VAL_AUTHOR", ilUtil::stripSlashes($post->getUserAlias()));
                        } else {
                            $this->tpl->setVariable("VAL_AUTHOR", $lng->txt("forums_anonymous"));
                        }
                    } else {
                        $this->tpl->setVariable("VAL_AUTHOR", $lng->txt("forums_anonymous"));
                    }
                } else {
                    if (ilObject::_exists($a_set["user_id"])) {
                        $user_obj = new ilObjUser($a_set["user_id"]);
                        $this->tpl->setVariable("VAL_AUTHOR", $user_obj->getLogin());
                    }
                }
            } else {
                if (ilObject::_exists($a_set["user_id"])) {
                    $this->tpl->setVariable("VAL_AUTHOR", ilObjUser::_lookupLogin($a_set["user_id"]));
                }
            }
            $this->tpl->setVariable("TXT_AUTHOR", $lng->txt("author"));
            $this->tpl->parseCurrentBlock();
        }
        
        // media player
        if ($a_set["content_type"] == NEWS_AUDIO &&
            $a_set["mob_id"] > 0 && ilObject::_exists($a_set["mob_id"])) {
            include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
            include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
            $mob = new ilObjMediaObject($a_set["mob_id"]);
            $med = $mob->getMediaItem("Standard");
            $mpl = new ilMediaPlayerGUI();
            $mpl->setFile(ilObjMediaObject::_getDirectory($a_set["mob_id"]) . "/" .
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
            include_once("./Services/Block/classes/class.ilBlockSetting.php");
            $this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
            if ($a_set["visibility"] == NEWS_PUBLIC ||
                ($a_set["priority"] == 0 &&
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
                    ilNewsItem::determineNewsContent($a_set["context_obj_type"], $a_set["content"], $a_set["content_text_is_lang_var"])
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
        if ($obj_type == "frm" && $a_set["context_sub_obj_type"] == "pos"
            && $a_set["context_sub_obj_id"] > 0) {
            include_once("./Modules/Forum/classes/class.ilObjForumAccess.php");
            $pos = $a_set["context_sub_obj_id"];
            $thread = ilObjForumAccess::_getThreadForPosting($pos);
            if ($thread > 0) {
                $add = "_" . $thread . "_" . $pos;
            }
        }

        // file hack, not nice
        if ($obj_type == "file") {
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $a_set["ref_id"]);
            $url = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "sendfile");
            $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setUrl($url);
            $button->setCaption("download");

            $this->tpl->setCurrentBlock("download");
            $this->tpl->setVariable("BUTTON_DOWNLOAD", $button->render());
            $this->tpl->parseCurrentBlock();
        }

        // wiki hack, not nice
        if ($obj_type == "wiki" && $a_set["context_sub_obj_type"] == "wpg"
            && $a_set["context_sub_obj_id"] > 0) {
            include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
            $wptitle = ilWikiPage::lookupTitle($a_set["context_sub_obj_id"]);
            if ($wptitle != "") {
                $add = "_" . ilWikiUtil::makeUrlTitle($wptitle);
            }
        }


        $url_target = "./goto.php?client_id=" . rawurlencode(CLIENT_ID) . "&target=" .
            $obj_type . "_" . $a_set["ref_id"] . $add;

        // lm page hack, not nice
        if (in_array($obj_type, array("dbk", "lm")) && $a_set["context_sub_obj_type"] == "pg"
            && $a_set["context_sub_obj_id"] > 0) {
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
            ilObject::_getIcon($a_set["context_obj_id"])
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

    /**
     * Make clickable
     *
     * @param
     * @return
     */
    public function makeClickable($a_str)
    {
        // this fixes bug 8744. We assume that strings that contain < and >
        // already contain html, we do not handle these
        if (is_int(strpos($a_str, ">")) && is_int(strpos($a_str, "<"))) {
            return $a_str;
        }

        return ilUtil::makeClickable($a_str);
    }
}
