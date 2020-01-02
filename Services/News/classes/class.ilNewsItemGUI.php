<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/News/classes/class.ilNewsItem.php");

define("IL_FORM_EDIT", 0);
define("IL_FORM_CREATE", 1);
define("IL_FORM_RE_EDIT", 2);
define("IL_FORM_RE_CREATE", 3);

/**
 * User Interface for NewsItem entities.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesNews
 */
class ilNewsItemGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    protected $enable_edit = 0;
    protected $context_obj_id;
    protected $context_obj_type;
    protected $context_sub_obj_id;
    protected $context_sub_obj_type;
    protected $form_edit_mode;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->ctrl = $ilCtrl;

        include_once("Services/News/classes/class.ilNewsItem.php");
        if ($_GET["news_item_id"] > 0) {
            $this->news_item = new ilNewsItem($_GET["news_item_id"]);
        }

        $this->ctrl->saveParameter($this, array("news_item_id"));

        // Init EnableEdit.
        $this->setEnableEdit(false);

        // Init Context.
        $this->setContextObjId($ilCtrl->getContextObjId());
        $this->setContextObjType($ilCtrl->getContextObjType());
        $this->setContextSubObjId($ilCtrl->getContextSubObjId());
        $this->setContextSubObjType($ilCtrl->getContextSubObjType());

        $lng->loadLanguageModule("news");

        $ilCtrl->saveParameter($this, "add_mode");
    }

    /**
     * Get html
     *
     * @return string	html
     */
    public function getHTML()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $lng->LoadLanguageModule("news");
        
        return $this->getNewsForContextBlock();
    }

    /**
     * Execute command.
     *
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        // check, if news item id belongs to context
        if (is_object($this->news_item) && $this->news_item->getId() > 0
            && ilNewsItem::_lookupContextObjId($this->news_item->getId()) != $this->getContextObjId()) {
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

    /**
     * Set EnableEdit.
     *
     * @param	boolean	$a_enable_edit	Edit mode on/off
     */
    public function setEnableEdit($a_enable_edit = 0)
    {
        $this->enable_edit = $a_enable_edit;
    }

    /**
     * Get EnableEdit.
     *
     * @return	boolean	Edit mode on/off
     */
    public function getEnableEdit()
    {
        return $this->enable_edit;
    }

    /**
     * Set ContextObjId.
     *
     * @param	int	$a_context_obj_id
     */
    public function setContextObjId($a_context_obj_id)
    {
        $this->context_obj_id = $a_context_obj_id;
    }

    /**
     * Get ContextObjId.
     *
     * @return	int
     */
    public function getContextObjId()
    {
        return $this->context_obj_id;
    }

    /**
     * Set ContextObjType.
     *
     * @param	int	$a_context_obj_type
     */
    public function setContextObjType($a_context_obj_type)
    {
        $this->context_obj_type = $a_context_obj_type;
    }

    /**
     * Get ContextObjType.
     *
     * @return	int
     */
    public function getContextObjType()
    {
        return $this->context_obj_type;
    }

    /**
     * Set ContextSubObjId.
     *
     * @param	int	$a_context_sub_obj_id
     */
    public function setContextSubObjId($a_context_sub_obj_id)
    {
        $this->context_sub_obj_id = $a_context_sub_obj_id;
    }

    /**
     * Get ContextSubObjId.
     *
     * @return	int
     */
    public function getContextSubObjId()
    {
        return $this->context_sub_obj_id;
    }

    /**
     * Set ContextSubObjType.
     *
     * @param	int	$a_context_sub_obj_type
     */
    public function setContextSubObjType($a_context_sub_obj_type)
    {
        $this->context_sub_obj_type = $a_context_sub_obj_type;
    }

    /**
     * Get ContextSubObjType.
     *
     * @return	int
     */
    public function getContextSubObjType()
    {
        return $this->context_sub_obj_type;
    }

    /**
     * Set FormEditMode.
     *
     * @param	int	$a_form_edit_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_RE_EDIT | IL_FORM_RE_CREATE)
     */
    public function setFormEditMode($a_form_edit_mode)
    {
        $this->form_edit_mode = $a_form_edit_mode;
    }

    /**
     * Get FormEditMode.
     *
     * @return	int	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE | IL_FORM_RE_EDIT | IL_FORM_RE_CREATE)
     */
    public function getFormEditMode()
    {
        return $this->form_edit_mode;
    }

    /**
     * FORM NewsItem: Create NewsItem.
     *
     */
    public function createNewsItem()
    {
        $form = $this->initFormNewsItem(IL_FORM_CREATE);
        return $form->getHtml();
    }

    /**
     * FORM NewsItem: Edit form.
     *
     */
    public function editNewsItem()
    {
        $form = $this->initFormNewsItem(IL_FORM_EDIT);
        $this->getValuesNewsItem($form);
        return $form->getHtml();
    }


    /**
     * FORM NewsItem: Init form.
     *
     * @param int $a_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
     * @return ilPropertyFormGUI form
     */
    protected function initFormNewsItem($a_mode)
    {
        $ilTabs = $this->tabs;

        $ilTabs->clearTargets();
        $form = self::getEditForm($a_mode, (int) $_GET["ref_id"]);
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * FORM NewsItem: Init form.
     *
     * @param	int	$a_mode	Form Edit Mode (IL_FORM_EDIT | IL_FORM_CREATE)
     * @return ilPropertyFormGUI form
     */
    public static function getEditForm($a_mode, $a_ref_id)
    {
        global $DIC;

        $lng = $DIC->language();

        $lng->loadLanguageModule("news");

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

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
        $text_area->setRows("4");
        $text_area->setUseRte(true);
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
        $media->setSuffixes(array("jpeg", "jpg", "png", "gif", "mp4", "mp3"));
        $media->setRequired(false);
        $media->setALlowDeletion(true);
        $media->setValue(" ");
        $form->addItem($media);

        // Property ContentLong
        /*
        $text_area = new ilTextAreaInputGUI($lng->txt("news_news_item_content_long"), "news_content_long");
        $text_area->setInfo($lng->txt("news_news_item_content_long_info"));
        $text_area->setRequired(false);
        $text_area->setCols("40");
        $text_area->setRows("8");
        $text_area->setUseRte(true);
        $form->addItem($text_area);*/


        // save and cancel commands
        if (in_array($a_mode, array(IL_FORM_CREATE,IL_FORM_RE_CREATE))) {
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

    /**
     * FORM NewsItem: Get current values for NewsItem form.
     *
     */
    public function getValuesNewsItem($a_form)
    {
        $values = array();

        $values["news_title"] = $this->news_item->getTitle();
        $values["news_content"] = $this->news_item->getContent() . $this->news_item->getContentLong();
        $values["news_visibility"] = $this->news_item->getVisibility();
        //$values["news_content_long"] = $this->news_item->getContentLong();
        $values["news_content_long"] = "";

        $a_form->setValuesByArray($values);
    }

    /**
     * FORM NewsItem: Save NewsItem.
     *
     */
    public function saveNewsItem()
    {
        $ilUser = $this->user;

        if (!$this->getEnableEdit()) {
            return;
        }

        $form = $this->initFormNewsItem(IL_FORM_CREATE);
        if ($form->checkInput()) {
            $this->news_item = new ilNewsItem();
            $this->news_item->setTitle($form->getInput("news_title"));
            $this->news_item->setContent($form->getInput("news_content"));
            $this->news_item->setVisibility($form->getInput("news_visibility"));

            //			$data = $form->getInput('media');
            //			var_dump($data);



            $this->news_item->setContentLong("");
            if (self::isRteActivated()) {
                $this->news_item->setContentHtml(true);
            }
            //$this->news_item->setContentLong($form->getInput("news_content_long"));

            // changed
            //$this->news_item->setContextObjId($this->ctrl->getContextObjId());
            //$this->news_item->setContextObjType($this->ctrl->getContextObjType());
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
            return $form->getHtml();
        }
    }

    public function exitSaveNewsItem()
    {
        $ilCtrl = $this->ctrl;

        if ($_GET["add_mode"] == "block") {
            $ilCtrl->returnToParent($this);
        } else {
            $ilCtrl->redirect($this, "editNews");
        }
    }

    /**
    * FORM NewsItem: Save NewsItem.
    *
    */
    public function updateNewsItem()
    {
        $ilUser = $this->user;
        
        if (!$this->getEnableEdit()) {
            return "";
        }

        $form = $this->initFormNewsItem(IL_FORM_EDIT);
        if ($form->checkInput()) {
            $this->news_item->setUpdateUserId($ilUser->getId());
            $this->news_item->setTitle($form->getInput("news_title"));
            $this->news_item->setContent($form->getInput("news_content"));
            $this->news_item->setVisibility($form->getInput("news_visibility"));
            //$this->news_item->setContentLong($form->getInput("news_content_long"));
            $this->news_item->setContentLong("");
            if (self::isRteActivated()) {
                $this->news_item->setContentHtml(true);
            }
            $this->news_item->update();
            $this->exitUpdateNewsItem();
        } else {
            $form->setValuesByPost();
            return $form->getHtml();
        }
    }

    public function exitUpdateNewsItem()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "editNews");
    }

    /**
    * FORM NewsItem: Save NewsItem.
    *
    */
    public function cancelUpdateNewsItem()
    {
        return $this->editNews();
    }

    /**
    * FORM NewsItem: Save NewsItem.
    *
    */
    public function cancelSaveNewsItem()
    {
        $ilCtrl = $this->ctrl;

        if ($_GET["add_mode"] == "block") {
            $ilCtrl->returnToParent($this);
        } else {
            return $this->editNews();
        }
    }

    /**
     * Edit news
     *
     * @return html
     */
    public function editNews()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs();

        $ilToolbar->addButton(
            $lng->txt("news_add_news"),
            $ilCtrl->getLinkTarget($this, "createNewsItem")
        );

        if (!$this->getEnableEdit()) {
            return;
        }
        return $this->getNewsForContextTable();
    }

    /**
     * Cancel update
     */
    public function cancelUpdate()
    {
        return $this->editNews();
    }

    /**
    * Confirmation Screen.
    */
    public function confirmDeletionNewsItems()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        if (!$this->getEnableEdit()) {
            return;
        }

        // check whether at least one item is selected
        if (count($_POST["news_id"]) == 0) {
            ilUtil::sendFailure($lng->txt("no_checkbox"));
            return $this->editNews();
        }

        $ilTabs->clearTargets();

        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($ilCtrl->getFormAction($this, "deleteNewsItems"));
        $c_gui->setHeaderText($lng->txt("info_delete_sure"));
        $c_gui->setCancel($lng->txt("cancel"), "editNews");
        $c_gui->setConfirm($lng->txt("confirm"), "deleteNewsItems");

        // add items to delete
        foreach ($_POST["news_id"] as $news_id) {
            $news = new ilNewsItem($news_id);
            $c_gui->addItem("news_id[]", $news_id, $news->getTitle());
        }

        return $c_gui->getHTML();
    }

    /**
    * Delete news items.
    */
    public function deleteNewsItems()
    {
        if (!$this->getEnableEdit()) {
            return;
        }
        // delete all selected news items
        foreach ($_POST["news_id"] as $news_id) {
            $news = new ilNewsItem($news_id);
            $news->delete();
        }

        return $this->editNews();
    }

    /**
     * BLOCK NewsForContext: Get block HTML.
     *
     */
    public function getNewsForContextBlock()
    {
        $lng = $this->lng;

        include_once("Services/News/classes/class.ilNewsForContextBlockGUI.php");
        $block_gui = new ilNewsForContextBlockGUI(get_class($this));

        $block_gui->setParentClass("ilinfoscreengui");
        $block_gui->setParentCmd("showSummary");
        $block_gui->setEnableEdit($this->getEnableEdit());


        $news_item = new ilNewsItem();

        // changed
        //$news_item->setContextObjId($this->ctrl->getContextObjId());
        //$news_item->setContextObjType($this->ctrl->getContextObjType());
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


    /**
     * TABLE NewsForContext: Get table HTML.
     *
     */
    public function getNewsForContextTable()
    {
        $lng = $this->lng;

        $news_item = new ilNewsItem();
        $news_item->setContextObjId($this->getContextObjId());
        $news_item->setContextObjType($this->getContextObjType());
        $news_item->setContextSubObjId($this->getContextSubObjId());
        $news_item->setContextSubObjType($this->getContextSubObjType());

        $perm_ref_id = 0;
        if (in_array($this->getContextObjType(), array("cat", "grp", "crs", "root"))) {
            $data = $news_item->getNewsForRefId(
                $_GET["ref_id"],
                false,
                false,
                0,
                true,
                false,
                true,
                true
            );
        } else {
            $perm_ref_id = $_GET["ref_id"];
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

        include_once("Services/News/classes/class.ilNewsForContextTableGUI.php");
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
    
    /**
     * Set tabs
     *
     * @param
     * @return
     */
    public function setTabs()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getParentReturn($this)
        );
    }

    /**
     * Is Rte activated
     *
     * @return bool
     */
    public static function isRteActivated()
    {
        include_once("./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php");
        if (ilObjAdvancedEditing::_getRichTextEditor() == "") {
            return false;
        }
        return true;
    }
}
