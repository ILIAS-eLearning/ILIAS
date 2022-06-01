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
 * User Interface for NewsItem entities.
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilNewsItemGUI
{
    public const FORM_EDIT = 0;
    public const FORM_CREATE = 1;
    public const FORM_RE_EDIT = 2;
    public const FORM_RE_CREATE = 2;
    protected ?ilNewsItem $news_item;

    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilObjUser $user;
    protected ilToolbarGUI $toolbar;

    protected bool $enable_edit = false;
    protected int $context_obj_id = 0;
    protected string $context_obj_type = "";
    protected int $context_sub_obj_id = 0;
    protected string $context_sub_obj_type = "";
    protected int $form_edit_mode;
    protected int $requested_ref_id;
    protected int $requested_news_item_id;
    protected string $add_mode;
    protected StandardGUIRequest $std_request;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->ctrl = $ilCtrl;

        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);
        $this->requested_news_item_id = (int) ($params["news_item_id"] ?? 0);
        $this->add_mode = (string) ($params["add_mode"] ?? "");

        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        if ($this->requested_news_item_id > 0) {
            $this->news_item = new ilNewsItem($this->requested_news_item_id);
        }

        $this->ctrl->saveParameter($this, ["news_item_id"]);

        // Init EnableEdit.
        $this->setEnableEdit(false);

        // Init Context.
        $this->setContextObjId($ilCtrl->getContextObjId());
        $this->setContextObjType($ilCtrl->getContextObjType());
        //$this->setContextSubObjId($ilCtrl->getContextSubObjId());
        //$this->setContextSubObjType($ilCtrl->getContextSubObjType());

        $lng->loadLanguageModule("news");

        $ilCtrl->saveParameter($this, "add_mode");
    }

    public function getHTML() : string
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("news");
        return $this->getNewsForContextBlock();
    }

    public function executeCommand() : string
    {
        // check, if news item id belongs to context
        if (isset($this->news_item) && $this->news_item->getId() > 0
            && ilNewsItem::_lookupContextObjId($this->news_item->getId()) !== $this->getContextObjId()) {
            throw new ilException("News ID does not match object context.");
        }


        // get next class and command
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            default:
                $html = $this->$cmd();
                break;
        }

        return $html;
    }

    public function setEnableEdit(bool $a_enable_edit = false) : void
    {
        $this->enable_edit = $a_enable_edit;
    }

    public function getEnableEdit() : bool
    {
        return $this->enable_edit;
    }

    public function setContextObjId(int $a_context_obj_id) : void
    {
        $this->context_obj_id = $a_context_obj_id;
    }

    public function getContextObjId() : int
    {
        return $this->context_obj_id;
    }

    public function setContextObjType(string $a_context_obj_type) : void
    {
        $this->context_obj_type = $a_context_obj_type;
    }

    public function getContextObjType() : string
    {
        return $this->context_obj_type;
    }

    public function setContextSubObjId(int $a_context_sub_obj_id) : void
    {
        $this->context_sub_obj_id = $a_context_sub_obj_id;
    }

    public function getContextSubObjId() : int
    {
        return $this->context_sub_obj_id;
    }

    public function setContextSubObjType(string $a_context_sub_obj_type) : void
    {
        $this->context_sub_obj_type = $a_context_sub_obj_type;
    }

    public function getContextSubObjType() : string
    {
        return $this->context_sub_obj_type;
    }

    public function createNewsItem() : string
    {
        $form = $this->initFormNewsItem(self::FORM_CREATE);
        return $form->getHTML();
    }

    public function editNewsItem() : string
    {
        $form = $this->initFormNewsItem(self::FORM_EDIT);
        $this->getValuesNewsItem($form);
        return $form->getHTML();
    }

    protected function initFormNewsItem(int $a_mode) : ilPropertyFormGUI
    {
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();
        $form = self::getEditForm($a_mode, $this->requested_ref_id);
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    public static function getEditForm(
        int $a_mode,
        int $a_ref_id
    ) : ilPropertyFormGUI {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule("news");

        $form = new ilPropertyFormGUI();

        // Property Title
        $text_input = new ilTextInputGUI($lng->txt("news_news_item_title"), "news_title");
        $text_input->setInfo("");
        $text_input->setRequired(true);
        $text_input->setMaxLength(200);
        $form->addItem($text_input);

        // Property Content
        $text_area = new ilTextAreaInputGUI($lng->txt("news_news_item_content"), "news_content");
        $text_area->setInfo("");
        $text_area->setRequired(false);
        $text_area->setRows(4);
        $form->addItem($text_area);

        // Property Visibility
        $radio_group = new ilRadioGroupInputGUI($lng->txt("news_news_item_visibility"), "news_visibility");
        $radio_option = new ilRadioOption($lng->txt("news_visibility_users"), "users");
        $radio_group->addOption($radio_option);
        $radio_option = new ilRadioOption($lng->txt("news_visibility_public"), "public");
        $radio_group->addOption($radio_option);
        $radio_group->setInfo($lng->txt("news_news_item_visibility_info"));
        $radio_group->setRequired(false);
        $radio_group->setValue("users");
        $form->addItem($radio_group);

        // media
        $media = new ilFileInputGUI($lng->txt('news_media'), 'media');
        $media->setSuffixes(["jpeg", "jpg", "png", "gif", "mp4", "mp3"]);
        $media->setRequired(false);
        $media->setALlowDeletion(true);
        $media->setValue(" ");
        $form->addItem($media);
        
        // save and cancel commands
        if (in_array($a_mode, [self::FORM_CREATE, self::FORM_RE_CREATE])) {
            $form->addCommandButton("saveNewsItem", $lng->txt("save"), "news_btn_create");
            $form->addCommandButton("cancelSaveNewsItem", $lng->txt("cancel"), "news_btn_cancel_create");
        } else {
            $form->addCommandButton("updateNewsItem", $lng->txt("save"), "news_btn_update");
            $form->addCommandButton("cancelUpdateNewsItem", $lng->txt("cancel"), "news_btn_cancel_update");
        }

        $form->setTitle($lng->txt("news_news_item_head"));

        $news_set = new ilSetting("news");
        if (!$news_set->get("enable_rss_for_internal")) {
            $form->removeItemByPostVar("news_visibility");
        } else {
            $nv = $form->getItemByPostVar("news_visibility");
            if (is_object($nv)) {
                $nv->setValue(ilNewsItem::_getDefaultVisibilityForRefId($a_ref_id));
            }
        }

        return $form;
    }

    // FORM NewsItem: Get current values for NewsItem form.
    public function getValuesNewsItem(ilPropertyFormGUI $a_form) : void
    {
        $values = [];

        $values["news_title"] = $this->news_item->getTitle();
        $values["news_content"] = $this->news_item->getContent() . $this->news_item->getContentLong();
        $values["news_visibility"] = $this->news_item->getVisibility();
        //$values["news_content_long"] = $this->news_item->getContentLong();
        $values["news_content_long"] = "";

        $a_form->setValuesByArray($values);

        if ($this->news_item->getMobId() > 0) {
            $fi = $a_form->getItemByPostVar("media");
            $fi->setValue(ilObject::_lookupTitle($this->news_item->getMobId()));
        }
    }

    // FORM NewsItem: Save NewsItem.
    public function saveNewsItem() : string
    {
        $ilUser = $this->user;

        if (!$this->getEnableEdit()) {
            return "";
        }

        $form = $this->initFormNewsItem(self::FORM_CREATE);
        if ($form->checkInput()) {
            $this->news_item = new ilNewsItem();
            $this->news_item->setTitle($form->getInput("news_title"));
            $this->news_item->setContent($form->getInput("news_content"));
            $this->news_item->setVisibility($form->getInput("news_visibility"));

            $media = $_FILES["media"];
            if ($media["name"] != "") {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject($media["name"], $media["tmp_name"], true);
                $this->news_item->setMobId($mob->getId());
            }


            $this->news_item->setContentLong("");
            if (self::isRteActivated()) {
                $this->news_item->setContentHtml(true);
            }

            // changed
            $this->news_item->setContextObjId($this->getContextObjId());
            $this->news_item->setContextObjType($this->getContextObjType());
            $this->news_item->setContextSubObjId($this->getContextSubObjId());
            $this->news_item->setContextSubObjType($this->getContextSubObjType());
            $this->news_item->setUserId($ilUser->getId());

            $news_set = new ilSetting("news");
            if (!$news_set->get("enable_rss_for_internal")) {
                $this->news_item->setVisibility("users");
            }

            $this->news_item->create();
            $this->exitSaveNewsItem();
        } else {
            $form->setValuesByPost();
            return $form->getHTML();
        }
        return "";
    }

    public function exitSaveNewsItem() : void
    {
        $ilCtrl = $this->ctrl;

        if ($this->add_mode === "block") {
            $ilCtrl->returnToParent($this);
        } else {
            $ilCtrl->redirect($this, "editNews");
        }
    }

    public function updateNewsItem() : string
    {
        $ilUser = $this->user;
        
        if (!$this->getEnableEdit()) {
            return "";
        }

        $form = $this->initFormNewsItem(self::FORM_EDIT);
        if ($form->checkInput()) {
            $this->news_item->setUpdateUserId($ilUser->getId());
            $this->news_item->setTitle($form->getInput("news_title"));
            $this->news_item->setContent($form->getInput("news_content"));
            $this->news_item->setVisibility($form->getInput("news_visibility"));
            //$this->news_item->setContentLong($form->getInput("news_content_long"));
            $this->news_item->setContentLong("");

            $media = $_FILES["media"];
            $old_mob_id = 0;

            // delete old media object
            $media_delete = $this->std_request->getDeleteMedia();
            if ($media["name"] != "" || $media_delete != "") {
                if ($this->news_item->getMobId() > 0 && ilObject::_lookupType($this->news_item->getMobId()) === "mob") {
                    $old_mob_id = $this->news_item->getMobId();
                }
                $this->news_item->setMobId(0);
            }

            if ($media["name"] != "") {
                $mob = ilObjMediaObject::_saveTempFileAsMediaObject($media["name"], $media["tmp_name"], true);
                $this->news_item->setMobId($mob->getId());
            }

            if (self::isRteActivated()) {
                $this->news_item->setContentHtml(true);
            }
            $this->news_item->update();

            if ($old_mob_id > 0) {
                $old_mob = new ilObjMediaObject($old_mob_id);
                $old_mob->delete();
            }

            $this->exitUpdateNewsItem();
        } else {
            $form->setValuesByPost();
            return $form->getHTML();
        }
        return "";
    }

    public function exitUpdateNewsItem() : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "editNews");
    }

    public function cancelUpdateNewsItem() : string
    {
        return $this->editNews();
    }

    public function cancelSaveNewsItem() : string
    {
        $ilCtrl = $this->ctrl;

        if ($this->add_mode === "block") {
            $ilCtrl->returnToParent($this);
        } else {
            return $this->editNews();
        }
        return "";
    }

    public function editNews() : string
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();

        $ilToolbar->addButton(
            $lng->txt("news_add_news"),
            $ilCtrl->getLinkTarget($this, "createNewsItem")
        );

        if (!$this->getEnableEdit()) {
            return "";
        }
        return $this->getNewsForContextTable();
    }

    public function cancelUpdate() : string
    {
        return $this->editNews();
    }

    public function confirmDeletionNewsItems() : string
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        if (!$this->getEnableEdit()) {
            return "";
        }

        // check whether at least one item is selected
        if (count($this->std_request->getNewsIds()) === 0) {
            $this->main_tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"));
            return $this->editNews();
        }

        $ilTabs->clearTargets();

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($ilCtrl->getFormAction($this, "deleteNewsItems"));
        $c_gui->setHeaderText($lng->txt("info_delete_sure"));
        $c_gui->setCancel($lng->txt("cancel"), "editNews");
        $c_gui->setConfirm($lng->txt("confirm"), "deleteNewsItems");

        // add items to delete
        foreach ($this->std_request->getNewsIds() as $news_id) {
            $news = new ilNewsItem($news_id);
            $c_gui->addItem("news_id[]", $news_id, $news->getTitle());
        }

        return $c_gui->getHTML();
    }

    public function deleteNewsItems() : string
    {
        if (!$this->getEnableEdit()) {
            return "";
        }
        // delete all selected news items
        foreach ($this->std_request->getNewsIds() as $news_id) {
            $news = new ilNewsItem($news_id);
            $news->delete();
        }

        return $this->editNews();
    }

    public function getNewsForContextBlock() : string
    {
        $lng = $this->lng;

        $block_gui = new ilNewsForContextBlockGUI();

        $block_gui->setEnableEdit($this->getEnableEdit());


        $news_item = new ilNewsItem();

        // changed
        $news_item->setContextObjId($this->getContextObjId());
        $news_item->setContextObjType($this->getContextObjType());
        $news_item->setContextSubObjId($this->getContextSubObjId());
        $news_item->setContextSubObjType($this->getContextSubObjType());

        $data = $news_item->queryNewsForContext();

        $block_gui->setTitle($lng->txt("news_block_news_for_context"));
        $block_gui->setRowTemplate("tpl.block_row_news_for_context.html", "Services/News");
        $block_gui->setData($data);

        return $block_gui->getHTML();
    }


    public function getNewsForContextTable() : string
    {
        $lng = $this->lng;

        $news_item = new ilNewsItem();
        $news_item->setContextObjId($this->getContextObjId());
        $news_item->setContextObjType($this->getContextObjType());
        $news_item->setContextSubObjId($this->getContextSubObjId());
        $news_item->setContextSubObjType($this->getContextSubObjType());

        $perm_ref_id = 0;
        if (in_array($this->getContextObjType(), ["cat", "grp", "crs", "root"])) {
            $data = $news_item->getNewsForRefId(
                $this->requested_ref_id,
                false,
                false,
                0,
                true,
                false,
                true,
                true
            );
        } else {
            $perm_ref_id = $this->requested_ref_id;
            if ($this->getContextSubObjId() > 0) {
                $data = $news_item->queryNewsForContext(
                    false,
                    0,
                    "",
                    true,
                    true
                );
            } else {
                $data = $news_item->queryNewsForContext();
            }
        }

        $table_gui = new ilNewsForContextTableGUI($this, "getNewsForContextTable", $perm_ref_id);

        $table_gui->setTitle($lng->txt("news_table_news_for_context"));
        $table_gui->setRowTemplate("tpl.table_row_news_for_context.html", "Services/News");
        $table_gui->setData($data);

        $table_gui->setDefaultOrderField("creation_date");
        $table_gui->setDefaultOrderDirection("desc");
        $table_gui->addMultiCommand("confirmDeletionNewsItems", $lng->txt("delete"));
        $table_gui->setTitle($lng->txt("news"));
        $table_gui->setSelectAllCheckbox("news_id");


        return $table_gui->getHTML();
    }
    
    public function setTabs() : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            (string) $ilCtrl->getParentReturn($this)
        );
    }

    public static function isRteActivated() : bool
    {
        if (ilObjAdvancedEditing::_getRichTextEditor() === "") {
            return false;
        }
        return true;
    }
}
