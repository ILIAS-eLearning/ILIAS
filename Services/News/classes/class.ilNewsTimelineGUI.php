<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Timeline for news
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilNewsTimelineGUI: ilLikeGUI, ilNoteGUI
 *
 * @ingroup ServicesNews
 */
class ilNewsTimelineGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var int
     */
    protected static $items_per_load = 10;

    /**
     * @var bool
     */
    protected $user_edit_all = false;

    /**
     * Constructor
     *
     * @param int $a_ref_id reference id
     */
    protected function __construct($a_ref_id, $a_include_auto_entries)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = $a_ref_id;
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->include_auto_entries = $a_include_auto_entries;
        $this->access = $DIC->access();

        $this->news_id = (int) $_GET["news_id"];


        $this->lng->loadLanguageModule("news");
    }

    /**
     * Set user can edit other users postings
     *
     * @param bool $a_val user can edit all postings
     */
    public function setUserEditAll($a_val)
    {
        $this->user_edit_all = $a_val;
    }

    /**
     * Get user can edit other users postings
     *
     * @return bool user can edit all postings
     */
    public function getUserEditAll()
    {
        return $this->user_edit_all;
    }

    /**
     * Get instance
     *
     * @param int $a_ref_id reference id
     * @return ilNewsTimelineGUI
     */
    public static function getInstance($a_ref_id, $a_include_auto_entries)
    {
        return new self($a_ref_id, $a_include_auto_entries);
    }

    /**
     * Execute command
     *
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            case "illikegui":
                $i = new ilNewsItem($this->news_id);
                include_once("./Services/Like/classes/class.ilLikeFactoryGUI.php");
                $likef = new ilLikeFactoryGUI();
                $like_gui = $likef->widget(array($i->getContextObjId()));
                $ctrl->saveParameter($this, "news_id");
                $like_gui->setObject(
                    $i->getContextObjId(),
                    $i->getContextObjType(),
                    $i->getContextSubObjId(),
                    $i->getContextSubObjType(),
                    $this->news_id
                );
                $ret = $ctrl->forwardCommand($like_gui);
                break;

            case "ilnotegui":
                $i = new ilNewsItem($this->news_id);
                $ctrl->saveParameter($this, "news_id");
                $notes_obj_type = ($i->getContextSubObjType() == "")
                    ? $i->getContextObjType()
                    : $i->getContextSubObjType();
                $note_gui = new ilNoteGUI(
                    $i->getContextObjId(),
                    $i->getContextSubObjId(),
                    $notes_obj_type,
                    false,
                    $i->getId()
                );
                $ret = $ctrl->forwardCommand($note_gui);
                break;

            default:
                if (in_array($cmd, array("show", "save", "update", "loadMore", "remove", "updateNewsItem"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Show
     *
     * @param
     * @return
     */
    public function show()
    {
        // toolbar
        if ($this->access->checkAccess("news_add_news", "", $this->ref_id)) {
            $b = ilLinkButton::getInstance();
            $b->setCaption('news_add_news');
            $b->setOnClick("return il.News.create();");
            $b->setPrimary(true);
            $this->toolbar->addButtonInstance($b);
        }

        include_once("./Services/News/classes/class.ilNewsItem.php");
        $news_item = new ilNewsItem();
        $news_item->setContextObjId($this->ctrl->getContextObjId());
        $news_item->setContextObjType($this->ctrl->getContextObjType());

        $news_data = $news_item->getNewsForRefId(
            $this->ref_id,
            false,
            false,
            0,
            true,
            false,
            !$this->include_auto_entries,
            false,
            null,
            self::$items_per_load
        );

        include_once("./Services/News/Timeline/classes/class.ilTimelineGUI.php");
        include_once("./Services/News/classes/class.ilNewsTimelineItemGUI.php");
        $timeline = ilTimelineGUI::getInstance();

        // get like widget
        $obj_ids = array_unique(array_map(function ($a) {
            return $a["context_obj_id"];
        }, $news_data));
        include_once("./Services/Like/classes/class.ilLikeFactoryGUI.php");
        $likef = new ilLikeFactoryGUI();
        $like_gui = $likef->widget($obj_ids);

        $js_items = array();
        foreach ($news_data as $d) {
            $news_item = new ilNewsItem($d["id"]);
            $item = ilNewsTimelineItemGUI::getInstance($news_item, $d["ref_id"], $like_gui);
            $item->setUserEditAll($this->getUserEditAll());
            $timeline->addItem($item);
            $js_items[$d["id"]] = array(
                "id" => $d["id"],
                "user_id" => $d["user_id"],
                "title" => $d["title"],
                "content" => $d["content"] . $d["content_long"],
                "content_long" => "",
                "priority" => $d["priority"],
                "visibility" => $d["visibility"],
                "content_type" => $d["content_type"],
                "mob_id" => $d["mob_id"]
            );
        }

        include_once("./Services/JSON/classes/class.ilJsonUtil.php");
        $this->tpl->addOnloadCode("il.News.setItems(" . ilJsonUtil::encode($js_items) . ");");
        $this->tpl->addOnloadCode("il.News.setAjaxUrl('" . $this->ctrl->getLinkTarget($this, "", "", true) . "');");

        if (count($news_data) > 0) {
            $ttpl = new ilTemplate("tpl.news_timeline.html", true, true, "Services/News");
            $ttpl->setVariable("NEWS", $timeline->render());
            $ttpl->setVariable("EDIT_MODAL", $this->getEditModal());
            $ttpl->setVariable("DELETE_MODAL", $this->getDeleteModal());
            $ttpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));
            $this->tpl->setContent($ttpl->get());
        } else {
            ilUtil::sendInfo($this->lng->txt("news_timline_add_entries_info"));
            $this->tpl->setContent($this->getEditModal());
        }

        $this->lng->toJS("create");
        $this->lng->toJS("edit");
        $this->lng->toJS("update");
        $this->lng->toJS("save");

        $this->tpl->addJavaScript("./Services/News/js/News.js");
        include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
        ilMediaPlayerGUI::initJavascript($this->tpl);
    }

    /**
     * Load more
     *
     * @param
     * @return
     */
    public function loadMore()
    {
        include_once("./Services/News/classes/class.ilNewsItem.php");
        $news_item = new ilNewsItem();
        $news_item->setContextObjId($this->ctrl->getContextObjId());
        $news_item->setContextObjType($this->ctrl->getContextObjType());

        $excluded = $_POST["rendered_news"];

        $news_data = $news_item->getNewsForRefId(
            $this->ref_id,
            false,
            false,
            0,
            true,
            false,
            !$this->include_auto_entries,
            false,
            null,
            self::$items_per_load,
            $excluded
        );

        include_once("./Services/News/Timeline/classes/class.ilTimelineGUI.php");
        include_once("./Services/News/classes/class.ilNewsTimelineItemGUI.php");
        $timeline = ilTimelineGUI::getInstance();

        // get like widget
        $obj_ids = array_unique(array_map(function ($a) {
            return $a["context_obj_id"];
        }, $news_data));
        include_once("./Services/Like/classes/class.ilLikeFactoryGUI.php");
        $likef = new ilLikeFactoryGUI();
        $like_gui = $likef->widget($obj_ids);

        $js_items = array();
        foreach ($news_data as $d) {
            $news_item = new ilNewsItem($d["id"]);
            $item = ilNewsTimelineItemGUI::getInstance($news_item, $d["ref_id"], $like_gui);
            $item->setUserEditAll($this->getUserEditAll());
            $timeline->addItem($item);
            $js_items[$d["id"]] = array(
                "id" => $d["id"],
                "user_id" => $d["user_id"],
                "title" => $d["title"],
                "content" => $d["content"] . $d["content_long"],
                "content_long" => "",
                "priority" => $d["priority"],
                "visibility" => $d["visibility"],
                "content_type" => $d["content_type"],
                "mob_id" => $d["mob_id"]
            );
        }

        include_once("./Services/JSON/classes/class.ilJsonUtil.php");
        $obj = new stdClass();
        $obj->data = $js_items;
        $obj->html = $timeline->render(true);

        echo ilJsonUtil::encode($obj);
        exit;
    }

    
    /**
     *
     *
     * @param
     */
    protected function updateNewsItem()
    {
        if ($_POST["news_action"] == "save") {
            $this->save();
            $this->ctrl->redirect($this, "show");
        }
        if ($_POST["news_action"] == "update") {
            $this->update();
            $this->ctrl->redirect($this, "show");
        }
    }
    

    /**
     * Save (ajax)
     */
    public function save()
    {
        include_once("./Services/News/classes/class.ilNewsItemGUI.php");
        $form = ilNewsItemGUI::getEditForm(IL_FORM_CREATE, $this->ref_id);
        if ($form->checkInput()) {
            $news_item = new ilNewsItem();
            $news_item->setTitle($form->getInput("news_title"));
            $news_item->setContent($form->getInput("news_content"));
            $news_item->setVisibility($form->getInput("news_visibility"));
            include_once("./Services/News/classes/class.ilNewsItemGUI.php");
            if (ilNewsItemGUI::isRteActivated()) {
                $news_item->setContentHtml(true);
            }
            //$news_item->setContentLong($form->getInput("news_content_long"));
            $news_item->setContentLong("");

            $obj_id = ilObject::_lookupObjectId($this->ref_id);
            $obj_type = ilObject::_lookupType($obj_id);
            $news_item->setContextObjId($obj_id);
            $news_item->setContextObjType($obj_type);
            $news_item->setUserId($this->user->getId());

            $media = $_FILES["media"];
            if ($media["name"] != "") {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject($media["name"], $media["tmp_name"], true);
                $news_item->setMobId($mob->getId());
            }

            $news_set = new ilSetting("news");
            if (!$news_set->get("enable_rss_for_internal")) {
                $news_item->setVisibility("users");
            }

            $news_item->create();
        }
    }



    /**
     * Update (ajax)
     */
    public function update()
    {
        include_once("./Services/News/classes/class.ilNewsItemGUI.php");
        $form = ilNewsItemGUI::getEditForm(IL_FORM_EDIT, $this->ref_id);
        if ($form->checkInput()) {
            $news_item = new ilNewsItem((int) $_POST["id"]);
            $news_item->setTitle($form->getInput("news_title"));
            $news_item->setContent($form->getInput("news_content"));
            $news_item->setVisibility($form->getInput("news_visibility"));
            //$news_item->setContentLong($form->getInput("news_content_long"));
            include_once("./Services/News/classes/class.ilNewsItemGUI.php");
            if (ilNewsItemGUI::isRteActivated()) {
                $news_item->setContentHtml(true);
            }
            $news_item->setContentLong("");

            $media = $_FILES["media"];
            $old_mob_id = 0;

            // delete old media object
            if ($media["name"] != "" || $_POST["media_delete"] != "") {
                if ($news_item->getMobId() > 0 && ilObject::_lookupType($news_item->getMobId()) == "mob") {
                    $old_mob_id = $news_item->getMobId();
                }
                $news_item->setMobId(0);
            }

            if ($media["name"] != "") {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject($media["name"], $media["tmp_name"], true);
                $news_item->setMobId($mob->getId());
            }

            $obj_id = ilObject::_lookupObjectId($this->ref_id);

            if ($news_item->getContextObjId() == $obj_id) {
                $news_item->setUpdateUserId($this->user->getId());
                $news_item->update();

                if ($old_mob_id > 0) {
                    include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
                    $old_mob = new ilObjMediaObject($old_mob_id);
                    $old_mob->delete();
                }
            }
        }
    }

    /**
     * Remove (ajax)
     */
    public function remove()
    {
        include_once("./Services/News/classes/class.ilNewsItemGUI.php");
        $news_item = new ilNewsItem((int) $_POST["id"]);
        if ($this->user->getId() == $news_item->getUserId() || $this->getUserEditAll()) {
            $news_item->delete();
        }
        exit;
    }

    /**
     * Get edit modal
     *
     * @return string modal html
     */
    protected function getEditModal()
    {
        include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($this->lng->txt("edit"));
        $modal->setId("ilNewsEditModal");
        $modal->setType(ilModalGUI::TYPE_LARGE);

        include_once("./Services/News/classes/class.ilNewsItemGUI.php");
        $form = ilNewsItemGUI::getEditForm(IL_FORM_EDIT, $this->ref_id);
        $form->setShowTopButtons(false);
        $form->setFormAction($this->ctrl->getFormAction($this));


        //
        $hi = new ilHiddenInputGUI("id");
        $form->addItem($hi);
        $act = new ilHiddenInputGUI("news_action");
        $form->addItem($act);
        $form->setId("news_edit_form");

        $modal->setBody($form->getHTML());

        return $modal->getHTML();
    }

    /**
     * Get delete modal
     *
     * @return string modal html
     */
    protected function getDeleteModal()
    {
        include_once("./Services/UIComponent/Modal/classes/class.ilModalGUI.php");
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($this->lng->txt("delete"));
        $modal->setId("ilNewsDeleteModal");
        $modal->setType(ilModalGUI::TYPE_LARGE);

        require_once 'Services/UIComponent/Button/classes/class.ilSubmitButton.php';
        $confirm = ilSubmitButton::getInstance();
        $confirm->setCaption("delete");
        $confirm->setId("news_btn_delete");
        $modal->addButton($confirm);

        $cancel = ilSubmitButton::getInstance();
        $cancel->setCaption("cancel");
        $cancel->setId("news_btn_cancel_delete");
        $modal->addButton($cancel);
        
        $modal->setBody("<p id='news_delete_news_title'></p>" .
            $this->tpl->getMessageHTML($this->lng->txt("news_really_delete_news"), "question"));

        return $modal->getHTML();
    }
}
