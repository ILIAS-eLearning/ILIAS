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
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\HTTP\Response\Sender\ResponseSendingException;

/**
 * Timeline for news
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilNewsTimelineGUI: ilLikeGUI, ilNoteGUI
 */
class ilNewsTimelineGUI
{
    protected \ILIAS\HTTP\Services $http;
    protected int $news_id;
    protected bool $include_auto_entries;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected int $ref_id;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilAccessHandler $access;
    protected static int $items_per_load = 10;
    protected bool $user_edit_all = false;
    protected StandardGUIRequest $std_request;

    protected function __construct(
        int $a_ref_id,
        bool $a_include_auto_entries
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = $a_ref_id;
        $this->lng = $DIC->language();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $this->include_auto_entries = $a_include_auto_entries;
        $this->access = $DIC->access();
        $this->http = $DIC->http();

        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->news_id = $this->std_request->getNewsId();

        $this->lng->loadLanguageModule("news");
    }

    /**
     * Set user can edit other users postings
     */
    public function setUserEditAll(bool $a_val) : void
    {
        $this->user_edit_all = $a_val;
    }

    /**
     * Get user can edit other users postings
     */
    public function getUserEditAll() : bool
    {
        return $this->user_edit_all;
    }

    public static function getInstance(
        int $a_ref_id,
        bool $a_include_auto_entries
    ) : ilNewsTimelineGUI {
        return new self($a_ref_id, $a_include_auto_entries);
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("show");

        switch ($next_class) {
            case "illikegui":
                $i = new ilNewsItem($this->news_id);
                $likef = new ilLikeFactoryGUI();
                $like_gui = $likef->widget([$i->getContextObjId()]);
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
                if (in_array($cmd, ["show", "save", "update", "loadMore", "remove", "updateNewsItem"])) {
                    $this->$cmd();
                }
        }
    }

    public function show(ilPropertyFormGUI $form = null) : void
    {
        // toolbar
        if ($this->access->checkAccess("news_add_news", "", $this->ref_id)) {
            $b = ilLinkButton::getInstance();
            $b->setCaption('news_add_news');
            $b->setOnClick("return il.News.create();");
            $b->setPrimary(true);
            $this->toolbar->addButtonInstance($b);
        }

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

        $timeline = ilTimelineGUI::getInstance();

        // get like widget
        $obj_ids = array_unique(array_map(static function (array $a) : int {
            return (int) $a["context_obj_id"];
        }, $news_data));
        $likef = new ilLikeFactoryGUI();
        $like_gui = $likef->widget($obj_ids);

        $js_items = [];
        foreach ($news_data as $d) {
            $news_item = new ilNewsItem((int) $d["id"]);
            $item = ilNewsTimelineItemGUI::getInstance($news_item, (int) $d["ref_id"], $like_gui);
            $item->setUserEditAll($this->getUserEditAll());
            $timeline->addItem($item);
            $js_items[$d["id"]] = [
                "id" => $d["id"],
                "user_id" => $d["user_id"],
                "title" => $d["title"],
                "content" => $d["content"] . $d["content_long"],
                "content_long" => "",
                "priority" => $d["priority"],
                "visibility" => $d["visibility"],
                "content_type" => $d["content_type"],
                "mob_id" => $d["mob_id"]
            ];
        }

        $this->tpl->addOnLoadCode("il.News.setItems(" . json_encode($js_items, JSON_THROW_ON_ERROR) . ");");
        $this->tpl->addOnLoadCode("il.News.setAjaxUrl('" . $this->ctrl->getLinkTarget($this, "", "", true) . "');");

        if (count($news_data) > 0) {
            $ttpl = new ilTemplate("tpl.news_timeline.html", true, true, "Services/News");
            $ttpl->setVariable("NEWS", $timeline->render());
            $ttpl->setVariable("EDIT_MODAL", $this->getEditModal($form));
            $ttpl->setVariable("DELETE_MODAL", $this->getDeleteModal());
            $ttpl->setVariable("LOADER", ilUtil::getImagePath("loader.svg"));
            $this->tpl->setContent($ttpl->get());
        } else {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("news_timline_add_entries_info"));
            $this->tpl->setContent($this->getEditModal());
        }

        $this->lng->toJS("create");
        $this->lng->toJS("edit");
        $this->lng->toJS("update");
        $this->lng->toJS("save");

        $this->tpl->addJavaScript("./Services/News/js/News.js");
        ilMediaPlayerGUI::initJavascript($this->tpl);
    }

    public function loadMore() : void
    {
        $news_item = new ilNewsItem();
        $news_item->setContextObjId($this->ctrl->getContextObjId());
        $news_item->setContextObjType($this->ctrl->getContextObjType());

        $excluded = $this->std_request->getRenderedNews();

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

        $timeline = ilTimelineGUI::getInstance();

        // get like widget
        $obj_ids = array_unique(array_map(static function ($a) : int {
            return (int) $a["context_obj_id"];
        }, $news_data));
        $likef = new ilLikeFactoryGUI();
        $like_gui = $likef->widget($obj_ids);

        $js_items = [];
        foreach ($news_data as $d) {
            $news_item = new ilNewsItem((int) $d["id"]);
            $item = ilNewsTimelineItemGUI::getInstance($news_item, (int) $d["ref_id"], $like_gui);
            $item->setUserEditAll($this->getUserEditAll());
            $timeline->addItem($item);
            $js_items[$d["id"]] = [
                "id" => $d["id"],
                "user_id" => $d["user_id"],
                "title" => $d["title"],
                "content" => $d["content"] . $d["content_long"],
                "content_long" => "",
                "priority" => $d["priority"],
                "visibility" => $d["visibility"],
                "content_type" => $d["content_type"],
                "mob_id" => $d["mob_id"]
            ];
        }

        $obj = new stdClass();
        $obj->data = $js_items;
        $obj->html = $timeline->render(true);

        $this->send(json_encode($obj, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws ResponseSendingException
     */
    protected function send(string $output) : void
    {
        $this->http->saveResponse($this->http->response()->withBody(
            Streams::ofString($output)
        ));
        $this->http->sendResponse();
        $this->http->close();
    }

    protected function updateNewsItem() : void
    {
        if ($this->std_request->getNewsAction() === "save") {
            $this->save();
        }
        if ($this->std_request->getNewsAction() === "update") {
            $this->update();
        }
    }
    

    // Save (ajax)
    public function save() : void
    {
        $form = ilNewsItemGUI::getEditForm(ilNewsItemGUI::FORM_EDIT, $this->ref_id);
        if ($form->checkInput()) {
            $news_item = new ilNewsItem();
            $news_item->setTitle($form->getInput("news_title"));
            $news_item->setContent($form->getInput("news_content"));
            $news_item->setVisibility($form->getInput("news_visibility"));
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
            $this->ctrl->redirect($this, "show");
        } else {
            $form->setValuesByPost();
            $this->show($form);
            $this->tpl->addOnLoadCode("il.News.create(true);");
        }
    }



    // Update (ajax)
    public function update() : void
    {
        $form = ilNewsItemGUI::getEditForm(ilNewsItemGUI::FORM_EDIT, $this->ref_id);
        if ($form->checkInput()) {
            $news_item = new ilNewsItem($this->std_request->getId());
            $news_item->setTitle($form->getInput("news_title"));
            $news_item->setContent($form->getInput("news_content"));
            $news_item->setVisibility($form->getInput("news_visibility"));
            //$news_item->setContentLong($form->getInput("news_content_long"));
            if (ilNewsItemGUI::isRteActivated()) {
                $news_item->setContentHtml(true);
            }
            $news_item->setContentLong("");

            $media = $_FILES["media"];
            $old_mob_id = 0;

            // delete old media object
            if ($media["name"] != "" || $this->std_request->getDeleteMedia() > 0) {
                if ($news_item->getMobId() > 0 && ilObject::_lookupType($news_item->getMobId()) === "mob") {
                    $old_mob_id = $news_item->getMobId();
                }
                $news_item->setMobId(0);
            }

            if ($media["name"] != "") {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject($media["name"], $media["tmp_name"], true);
                $news_item->setMobId($mob->getId());
            }

            $obj_id = ilObject::_lookupObjectId($this->ref_id);

            if ($news_item->getContextObjId() === $obj_id) {
                $news_item->setUpdateUserId($this->user->getId());
                $news_item->update();

                if ($old_mob_id > 0) {
                    $old_mob = new ilObjMediaObject($old_mob_id);
                    $old_mob->delete();
                }
            }
            $this->ctrl->redirect($this, "show");
        } else {
            $form->setValuesByPost();
            $this->show($form);
            $this->tpl->addOnLoadCode("il.News.edit(" . $this->std_request->getNewsId() . ", true);");
        }
    }

    // Remove (ajax)
    public function remove() : void
    {
        $news_item = new ilNewsItem($this->std_request->getNewsId());
        if ($this->getUserEditAll() || $this->user->getId() === $news_item->getUserId()) {
            $news_item->delete();
        }
        $this->send("");
    }

    protected function getEditModal($form = null) : string
    {
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($this->lng->txt("edit"));
        $modal->setId("ilNewsEditModal");
        $modal->setType(ilModalGUI::TYPE_LARGE);

        if (is_null($form)) {
            $form = ilNewsItemGUI::getEditForm(ilNewsItemGUI::FORM_EDIT, $this->ref_id);
        }
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

    protected function getDeleteModal() : string
    {
        $modal = ilModalGUI::getInstance();
        $modal->setHeading($this->lng->txt("delete"));
        $modal->setId("ilNewsDeleteModal");
        $modal->setType(ilModalGUI::TYPE_LARGE);

        $confirm = ilSubmitButton::getInstance();
        $confirm->setCaption("delete");
        $confirm->setId("news_btn_delete");
        $modal->addButton($confirm);

        $cancel = ilSubmitButton::getInstance();
        $cancel->setCaption("cancel");
        $cancel->setId("news_btn_cancel_delete");
        $modal->addButton($cancel);
        
        $modal->setBody("<p id='news_delete_news_title'></p>" .
            ilUtil::getSystemMessageHTML($this->lng->txt("news_really_delete_news"), "question"));

        return $modal->getHTML();
    }
}
