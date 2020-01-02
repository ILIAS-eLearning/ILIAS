<?php
include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
include_once("Services/Style/System/classes/class.ilSystemStyleSettings.php");
include_once("Services/Style/System/classes/Overview/class.ilSystemStyleDeleteGUI.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessageStack.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/FileDelivery/classes/class.ilFileDelivery.php");
include_once("Services/Style/System/classes/Overview/class.ilSystemStylesTableGUI.php");
include_once("Services/Form/classes/class.ilSelectInputGUI.php");
include_once("Services/Style/System/classes/class.ilStyleDefinition.php");
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Object/exceptions/class.ilObjectException.php");

/**
 * @author            Alex Killing <alex.killing@gmx.de>
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleOverviewGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ILIAS\DI\Container
     */
    protected $DIC;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var bool
     */
    protected $read_only = true;

    /**
     * @var bool
     */
    protected $management_enabled = false;


    /**
     * Constructor
     */
    public function __construct($read_only, $management_enabled)
    {
        global $DIC;

        $this->ilias = $DIC["ilias"];
        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];

        $this->ref_id = (int) $_GET["ref_id"];

        $this->setReadOnly($read_only);
        $this->setManagementEnabled($management_enabled);
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();

        if ($cmd == "") {
            $cmd = $this->isReadOnly()?"view":"edit";
        }


        switch ($cmd) {
            case "addSystemStyle":
            case "addSubStyle":
            case "saveNewSystemStyle":
            case "saveNewSubStyle":
            case "copyStyle":
            case "importStyle":
            case "deleteStyles":
            case "deleteStyle":
            case "confirmDelete":
                if (!$this->isManagementEnabled()) {
                    throw new ilObjectException($this->lng->txt("permission_denied"));
                }
                $this->$cmd();
                return;
            case "cancel":
            case "edit":
            case "export":
            case "moveUserStyles":
            case "saveStyleSettings":
                if ($this->isReadOnly()) {
                    throw new ilObjectException($this->lng->txt("permission_denied"));
                }
                $this->$cmd();
                return;
            case "view":
                $this->$cmd();
                return;

        }
    }

    protected function view()
    {
        $table = new ilSystemStylesTableGUI($this, "edit", true);
        $this->tpl->setContent($table->getHTML());
    }

    protected function cancel()
    {
        $this->edit();
    }
    /**
     * Edit
     */
    public function edit()
    {
        if ($this->isManagementEnabled()) {
            // Add Button for adding skins
            $add_skin_btn = ilLinkButton::getInstance();
            $add_skin_btn->setCaption($this->lng->txt("add_system_style"), false);
            $add_skin_btn->setUrl($this->ctrl->getLinkTarget($this, 'addSystemStyle'));
            $this->toolbar->addButtonInstance($add_skin_btn);

            // Add Button for adding skins
            $add_substyle_btn = ilLinkButton::getInstance();
            $add_substyle_btn->setCaption($this->lng->txt("add_substyle"), false);
            $add_substyle_btn->setUrl($this->ctrl->getLinkTarget($this, 'addSubStyle'));
            if (count(ilStyleDefinition::getAllSkins()) ==1) {
                $add_substyle_btn->setDisabled(true);
            }
            $this->toolbar->addButtonInstance($add_substyle_btn);

            $this->toolbar->addSeparator();
        }

        // from styles selector
        $si = new ilSelectInputGUI($this->lng->txt("sty_move_user_styles") . ": " . $this->lng->txt("sty_from"), "from_style");

        $options = array();
        foreach (ilStyleDefinition::getAllSkinStyles() as $id => $skin_style) {
            if (!$skin_style['substyle_of']) {
                $options[$id] = $skin_style['title'];
            }
        }
        $si->setOptions($options + array("other" => $this->lng->txt("other")));

        $this->toolbar->addInputItem($si, true);

        // from styles selector
        $si = new ilSelectInputGUI($this->lng->txt("sty_to"), "to_style");
        $si->setOptions($options);
        $this->toolbar->addInputItem($si, true);
        // Add Button for adding skins
        $move_skin_btn = ilSubmitButton::getInstance();
        $move_skin_btn->setCaption($this->lng->txt("sty_move_style"), false);
        $this->toolbar->addButtonInstance($move_skin_btn);
        $this->toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'moveUserStyles'));

        $table = new ilSystemStylesTableGUI($this, "edit");
        $table->addActions($this->isManagementEnabled());
        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Move user styles
     */
    public function moveUserStyles()
    {
        $to = explode(":", $_POST["to_style"]);

        if ($_POST["from_style"] == "other") {
            // get all user assigned styles
            $all_user_styles = ilObjUser::_getAllUserAssignedStyles();

            // move users that are not assigned to
            // currently existing style
            foreach ($all_user_styles as $style) {
                if (!ilStyleDefinition::styleExists($style)) {
                    $style_arr = explode(":", $style);
                    ilObjUser::_moveUsersToStyle($style_arr[0], $style_arr[1], $to[0], $to[1]);
                }
            }
        } else {
            $from = explode(":", $_POST["from_style"]);
            ilObjUser::_moveUsersToStyle($from[0], $from[1], $to[0], $to[1]);
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "edit");
    }


    /**
     * Save skin and style settings
     */
    public function saveStyleSettings()
    {
        $message_stack = new ilSystemStyleMessageStack();

        if ($this->checkStyleSettings($message_stack)) {
            $all_styles = ilStyleDefinition::getAllSkinStyles();
            foreach ($all_styles as $st) {
                if (!isset($_POST["st_act"][$st["id"]])) {
                    ilSystemStyleSettings::_deactivateStyle($st["template_id"], $st["style_id"]);
                } else {
                    ilSystemStyleSettings::_activateStyle($st["template_id"], $st["style_id"]);
                }
            }

            //set default skin and style
            if ($_POST["default_skin_style"] != "") {
                $sknst = explode(":", $_POST["default_skin_style"]);
                ilSystemStyleSettings::setCurrentDefaultStyle($sknst[0], $sknst[1]);
            }
            $message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt("msg_obj_modified"), ilSystemStyleMessage::TYPE_SUCCESS));
        }
        $message_stack->sendMessages(true);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * @param ilSystemStyleMessageStack $message_stack
     * @return bool
     */
    protected function checkStyleSettings(ilSystemStyleMessageStack $message_stack)
    {
        $passed = true;

        if (count($_POST["st_act"]) < 1) {
            $passed = false;
            $message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt("at_least_one_style"), ilSystemStyleMessage::TYPE_ERROR));
        }

        if (!isset($_POST["st_act"][$_POST["default_skin_style"]])) {
            $passed = false;
            $message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt("cant_deactivate_default_style"), ilSystemStyleMessage::TYPE_ERROR));
        }

        // check if a style should be deactivated, that still has
        // a user assigned to
        $all_styles = ilStyleDefinition::getAllSkinStyles();

        foreach ($all_styles as $st) {
            if (!isset($_POST["st_act"][$st["id"]])) {
                if (ilObjUser::_getNumberOfUsersForStyle($st["template_id"], $st["style_id"]) > 0) {
                    $passed = false;
                    $message_stack->addMessage(new ilSystemStyleMessage($st["style_name"] . ": " . $this->lng->txt("cant_deactivate_if_users_assigned"), ilSystemStyleMessage::TYPE_ERROR));
                }
            }
        }
        return $passed;
    }


    /**
     * create
     */
    protected function addSystemStyle()
    {
        $this->addSystemStyleForms();
    }


    protected function saveNewSystemStyle()
    {
        $form = $this->createSystemStyleForm();

        if ($form->checkInput()) {
            $message_stack = new ilSystemStyleMessageStack();
            if (ilStyleDefinition::skinExists($_POST["skin_id"])) {
                ilUtil::sendFailure($this->lng->txt("skin_id_exists"));
            } else {
                try {
                    $skin = new ilSkinXML($_POST["skin_id"], $_POST["skin_name"]);
                    $style = new ilSkinStyleXML($_POST["style_id"], $_POST["style_name"]);
                    $skin->addStyle($style);
                    $container = new ilSystemStyleSkinContainer($skin);
                    $container->create($message_stack);
                    $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $skin->getId());
                    $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $style->getId());
                    if ($message_stack->hasMessages()) {
                        $message_stack->sendMessages(true);
                    } else {
                        ilUtil::sendSuccess($this->lng->txt("msg_sys_style_created"), true);
                    }
                    $this->ctrl->redirectByClass("ilSystemStyleSettingsGUI");
                } catch (ilSystemStyleException $e) {
                    $message_stack->addMessage(new ilSystemStyleMessage($e->getMessage(), ilSystemStyleMessage::TYPE_ERROR));
                }
            }
            $message_stack->sendMessages();
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * create
     */
    protected function addSystemStyleForms()
    {
        global $DIC;

        $DIC->tabs()->clearTargets();
        /**
         * Since clearTargets also clears the help screen ids
         */
        $DIC->help()->setScreenIdComponent("sty");
        $DIC->help()->setScreenId("system_styles");
        $DIC->help()->setSubScreenId("create");

        $forms = array();

        $forms[] = $this->createSystemStyleForm();
        $forms[] = $this->importSystemStyleForm();
        $forms[] = $this->cloneSystemStyleForm();

        $this->tpl->setContent($this->getCreationFormsHTML($forms));
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function createSystemStyleForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_create_new_system_style"));

        $ti = new ilTextInputGUI($this->lng->txt("skin_id"), "skin_id");
        $ti->setInfo($this->lng->txt("skin_id_description"));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("skin_name"), "skin_name");
        $ti->setInfo($this->lng->txt("skin_name_description"));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("style_id"), "style_id");
        $ti->setInfo($this->lng->txt("style_id_description"));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("style_name"), "style_name");
        $ti->setInfo($this->lng->txt("style_name_description"));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $form->addCommandButton("saveNewSystemStyle", $this->lng->txt("save"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function importSystemStyleForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_import_system_style"));

        // title
        $file_input = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
        $file_input->setRequired(true);
        $file_input->setSuffixes(array("zip"));
        $form->addItem($file_input);

        $form->addCommandButton("importStyle", $this->lng->txt("import"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function cloneSystemStyleForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_copy_other_system_style"));

        // source
        $ti = new ilSelectInputGUI($this->lng->txt("sty_source"), "source_style");
        $ti->setRequired(true);
        $styles = ilStyleDefinition::getAllSkinStyles();
        $options = array();
        foreach ($styles as $id => $style) {
            $system_style_conf = new ilSystemStyleConfig();
            if ($style["skin_id"]!=$system_style_conf->getDefaultSkinId()) {
                $options[$id] = $style['title'];
            }
        }
        $ti->setOptions($options);

        $form->addItem($ti);

        $form->addCommandButton("copyStyle", $this->lng->txt("copy"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    /**
     * @param array $a_forms
     * @return string
     */
    protected function getCreationFormsHTML(array $a_forms)
    {
        include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");

        $acc = new ilAccordionGUI();
        $acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
        $cnt = 1;
        foreach ($a_forms as $form_type => $cf) {
            /**
             * @var ilPropertyFormGUI $cf
             */
            $htpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");

            // using custom form titles (used for repository plugins)
            $form_title = "";
            if (method_exists($this, "getCreationFormTitle")) {
                $form_title = $this->getCreationFormTitle($form_type);
            }
            if (!$form_title) {
                $form_title = $cf->getTitle();
            }

            // move title from form to accordion
            $htpl->setVariable("TITLE", $this->lng->txt("option") . " " . $cnt . ": " .
                $form_title);
            $cf->setTitle(null);
            $cf->setTitleIcon(null);
            $cf->setTableWidth("100%");

            $acc->addItem($htpl->get(), $cf->getHTML());

            $cnt++;
        }

        return "<div class='ilCreationFormSection'>" . $acc->getHTML() . "</div>";
    }

    protected function copyStyle()
    {
        $message_stack = new ilSystemStyleMessageStack();

        $imploded_skin_style_id = explode(":", $_POST['source_style']);
        $skin_id = $imploded_skin_style_id[0];
        $style_id = $imploded_skin_style_id[1];

        try {
            $container = ilSystemStyleSkinContainer::generateFromId($skin_id, $message_stack);
            $new_container = $container->copy();
            $message_stack->prependMessage(new ilSystemStyleMessage($this->lng->txt("style_copied"), ilSystemStyleMessage::TYPE_SUCCESS));
            $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $new_container->getSkin()->getId());
            $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $new_container->getSkin()->getStyle($style_id)->getId());
        } catch (Exception $e) {
            $message_stack->addMessage(new ilSystemStyleMessage($e->getMessage(), ilSystemStyleMessage::TYPE_ERROR));
        }


        $message_stack->sendMessages(true);
        $this->ctrl->redirectByClass("ilSystemStyleSettingsGUI");
    }

    protected function deleteStyle()
    {
        $skin_id = $_GET["skin_id"];
        $style_id = $_GET["style_id"];
        $message_stack = new ilSystemStyleMessageStack();

        if ($this->checkDeletable($skin_id, $style_id, $message_stack)) {
            $delete_form_table = new ilSystemStyleDeleteGUI();
            $container = ilSystemStyleSkinContainer::generateFromId($skin_id);
            $delete_form_table->addStyle($container->getSkin(), $container->getSkin()->getStyle($style_id));
            $this->tpl->setContent($delete_form_table->getDeleteStyleFormHTML());
        } else {
            $message_stack->prependMessage(new ilSystemStyleMessage($this->lng->txt("style_not_deleted"), ilSystemStyleMessage::TYPE_ERROR));
            $message_stack->sendMessages(true);
            $this->edit();
        }
    }
    protected function deleteStyles()
    {
        $delete_form_table = new ilSystemStyleDeleteGUI();
        $message_stack = new ilSystemStyleMessageStack();

        $all_deletable = true;
        foreach ($_POST['id'] as $skin_style_id) {
            $imploded_skin_style_id = explode(":", $skin_style_id);
            $skin_id = $imploded_skin_style_id[0];
            $style_id = $imploded_skin_style_id[1];
            if (!$this->checkDeletable($skin_id, $style_id, $message_stack)) {
                $all_deletable = false;
            }
        }
        if ($all_deletable) {
            foreach ($_POST['id'] as $skin_style_id) {
                $imploded_skin_style_id = explode(":", $skin_style_id);
                $skin_id = $imploded_skin_style_id[0];
                $style_id = $imploded_skin_style_id[1];
                $container = ilSystemStyleSkinContainer::generateFromId($skin_id);
                $delete_form_table->addStyle($container->getSkin(), $container->getSkin()->getStyle($style_id));
            }
            $this->tpl->setContent($delete_form_table->getDeleteStyleFormHTML());
        } else {
            $message_stack->prependMessage(new ilSystemStyleMessage($this->lng->txt("styles_not_deleted"), ilSystemStyleMessage::TYPE_ERROR));
            $message_stack->sendMessages(true);
            $this->edit();
        }
    }

    /**
     * @param $skin_id
     * @param $style_id
     * @param ilSystemStyleMessageStack $message_stack
     * @return bool
     * @throws ilSystemStyleException
     */
    protected function checkDeletable($skin_id, $style_id, ilSystemStyleMessageStack $message_stack)
    {
        $passed = true;
        if (ilObjUser::_getNumberOfUsersForStyle($skin_id, $style_id) > 0) {
            $message_stack->addMessage(new ilSystemStyleMessage($style_id . ": " . $this->lng->txt("cant_delete_if_users_assigned"), ilSystemStyleMessage::TYPE_ERROR));
            $passed = false;
        }
        if (ilSystemStyleSettings::_lookupActivatedStyle($skin_id, $style_id) > 0) {
            $message_stack->addMessage(new ilSystemStyleMessage($style_id . ": " . $this->lng->txt("cant_delete_activated_style"), ilSystemStyleMessage::TYPE_ERROR));
            $passed = false;
        }
        if (ilSystemStyleSettings::getCurrentDefaultSkin() == $skin_id && ilSystemStyleSettings::getCurrentDefaultSkin() == $style_id) {
            $message_stack->addMessage(new ilSystemStyleMessage($style_id . ": " . $this->lng->txt("cant_delete_default_style"), ilSystemStyleMessage::TYPE_ERROR));
            $passed = false;
        }

        if (ilSystemStyleSkinContainer::generateFromId($skin_id)->getSkin()->getSubstylesOfStyle($style_id)) {
            $message_stack->addMessage(new ilSystemStyleMessage($style_id . ": " . $this->lng->txt("cant_delete_style_with_substyles"), ilSystemStyleMessage::TYPE_ERROR));
            $passed = false;
        }
        return $passed;
    }

    protected function confirmDelete()
    {
        $message_stack = new ilSystemStyleMessageStack();

        foreach ($_POST as $key => $skin_style_id) {
            if (is_string($skin_style_id) && strpos($key, 'style') !== false) {
                try {
                    $imploded_skin_style_id = explode(":", $skin_style_id);
                    $container = ilSystemStyleSkinContainer::generateFromId($imploded_skin_style_id[0], $message_stack);
                    $syle = $container->getSkin()->getStyle($imploded_skin_style_id[1]);
                    $container->deleteStyle($syle);
                    if (!$container->getSkin()->hasStyles()) {
                        $container->delete();
                    }
                } catch (Exception $e) {
                    $message_stack->addMessage(new ilSystemStyleMessage($e->getMessage(), ilSystemStyleMessage::TYPE_ERROR));
                }
            }
        }
        $message_stack->sendMessages(true);
        $this->ctrl->redirect($this);
    }

    /**
     *
     */
    protected function importStyle()
    {
        $form = $this->importSystemStyleForm();

        if ($form->checkInput()) {
            $message_stack = new ilSystemStyleMessageStack();
            $imported_container = ilSystemStyleSkinContainer::import($_POST['importfile']['tmp_name'], $_POST['importfile']['name'], $message_stack);
            $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $imported_container->getSkin()->getId());
            $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $imported_container->getSkin()->getDefaultStyle()->getId());
            $message_stack->addMessage(new ilSystemStyleMessage($this->lng->txt("style_imported") . " " . $imported_container->getSkinDirectory(), ilSystemStyleMessage::TYPE_SUCCESS));

            $message_stack->sendMessages(true);
            $this->ctrl->redirectByClass("ilSystemStyleSettingsGUI");
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }
    protected function export()
    {
        $skin_id = $_GET["skin_id"];
        $container = ilSystemStyleSkinContainer::generateFromId($skin_id);
        try {
            $container->export();
        } catch (Exception $e) {
            ilUtil::sendFailure($this->lng->txt("zip_export_failed") . " " . $e->getMessage());
        }
    }


    /**
     *
     */
    protected function addSubStyle()
    {
        global $DIC;

        $DIC->tabs()->clearTargets();
        /**
         * Since clearTargets also clears the help screen ids
         */
        $DIC->help()->setScreenIdComponent("sty");
        $DIC->help()->setScreenId("system_styles");
        $DIC->help()->setSubScreenId("create_sub");

        $form = $this->addSubStyleForms();

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function addSubStyleForms()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_create_new_system_sub_style"));


        $ti = new ilTextInputGUI($this->lng->txt("sub_style_id"), "sub_style_id");
        $ti->setInfo($this->lng->txt("sub_style_id_description"));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("sub_style_name"), "sub_style_name");
        $ti->setInfo($this->lng->txt("sub_style_name_description"));
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // source
        $ti = new ilSelectInputGUI($this->lng->txt("parent"), "parent_style");
        $ti->setRequired(true);
        $ti->setInfo($this->lng->txt("sub_style_parent_style_description"));
        $styles = ilStyleDefinition::getAllSkinStyles();
        $options = array();
        foreach ($styles as $id => $style) {
            $system_style_conf = new ilSystemStyleConfig();
            if ($style["skin_id"]!=$system_style_conf->getDefaultSkinId() && !$style["substyle_of"]) {
                $options[$id] = $style['title'];
            }
        }
        $ti->setOptions($options);

        $form->addItem($ti);
        $form->addCommandButton("saveNewSubStyle", $this->lng->txt("save"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }

    protected function saveNewSubStyle()
    {
        $form = $this->addSubStyleForms();

        if ($form->checkInput()) {
            try {
                $imploded_parent_skin_style_id = explode(":", $_POST['parent_style']);
                $parent_skin_id = $imploded_parent_skin_style_id[0];
                $parent_style_id = $imploded_parent_skin_style_id[1];

                $container = ilSystemStyleSkinContainer::generateFromId($parent_skin_id);

                if (array_key_exists($_POST['sub_style_id'], $container->getSkin()->getSubstylesOfStyle($parent_style_id))) {
                    throw new ilSystemStyleException(ilSystemStyleException::SUBSTYLE_ASSIGNMENT_EXISTS, $_POST['sub_style_id']);
                }

                $sub_style_id = $_POST['sub_style_id'];

                $style = new ilSkinStyleXML($_POST['sub_style_id'], $_POST['sub_style_name']);
                $style->setSubstyleOf($parent_style_id);
                $container->addStyle($style);

                $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $parent_skin_id);
                $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $sub_style_id);
                ilUtil::sendSuccess($this->lng->txt("msg_sub_style_created"), true);
                $this->ctrl->redirectByClass("ilSystemStyleSettingsGUI");
            } catch (ilSystemStyleException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
            }
        }

        // display only this form to correct input
        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @return boolean
     */
    public function isReadOnly()
    {
        return $this->read_only;
    }

    /**
     * @param boolean $read_only
     */
    public function setReadOnly($read_only)
    {
        $this->read_only = $read_only;
    }

    /**
     * @return boolean
     */
    public function isManagementEnabled()
    {
        return $this->management_enabled;
    }

    /**
     * @param boolean $management_enabled
     */
    public function setManagementEnabled($management_enabled)
    {
        $this->management_enabled = $management_enabled;
    }
}
