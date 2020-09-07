<?php
include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
include_once("Services/Style/System/classes/Utilities/class.ilSkinXML.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleSkinContainer.php");
include_once("Services/Style/System/classes/class.ilSystemStyleSettings.php");
include_once("Services/Style/System/classes/Exceptions/class.ilSystemStyleException.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessageStack.php");
include_once("Services/Style/System/classes/Utilities/class.ilSystemStyleMessage.php");
include_once("Services/Style/System/classes/Settings/class.ilSubStyleAssignmentGUI.php");


/**
 * @author            Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version           $Id$*
 */
class ilSystemStyleSettingsGUI
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
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();

        $this->tpl = $DIC["tpl"];
    }


    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd() ? $this->ctrl->getCmd():"edit";
        $system_style_conf = new ilSystemStyleConfig();
        $skin = ilSkinXML::parseFromXML($system_style_conf->getCustomizingSkinPath() . $_GET["skin_id"] . "/template.xml");
        $style = $skin->getStyle($_GET["style_id"]);

        if ($style->isSubstyle()) {
            if ($cmd == "edit" || $cmd == "view") {
                $this->setSubStyleSubTabs("edit");
            } else {
                $this->setSubStyleSubTabs("assignStyle");
            }
        }

        switch ($cmd) {
            case "deleteAssignments":
                $assign_gui = new ilSubStyleAssignmentGUI($this);
                $assign_gui->deleteAssignments($skin, $style);
                break;
            case "saveAssignment":
                $assign_gui = new ilSubStyleAssignmentGUI($this);
                $assign_gui->saveAssignment($skin, $style);
                break;
            case "addAssignment":
                $assign_gui = new ilSubStyleAssignmentGUI($this);
                $assign_gui->addAssignment($skin, $style);
                break;
            case "assignStyle":
                $assign_gui = new ilSubStyleAssignmentGUI($this);
                $assign_gui->assignStyle($skin, $style);
                break;
            case "save":
            case "edit":
                $this->$cmd();
                break;
            default:
                $this->edit();
                break;
        }
    }

    /**
     * @param string $active
     */
    protected function setSubStyleSubTabs($active = "")
    {
        $this->tabs->addSubTab('edit', $this->lng->txt('settings'), $this->ctrl->getLinkTargetByClass('ilsystemstylesettingsgui'));
        $this->tabs->addSubTab('assignStyle', $this->lng->txt('assignment'), $this->ctrl->getLinkTargetByClass('ilsystemstylesettingsgui', "assignStyle"));

        $this->tabs->activateSubTab($active);
    }

    protected function edit()
    {
        $form = $this->editSystemStyleForm();
        $this->getPropertiesValues($form);
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Get values for edit properties form
     */
    public function getPropertiesValues($form)
    {
        global $DIC;

        if (!$_GET["skin_id"]) {
            throw new ilSystemStyleException(ilSystemStyleException::NO_SKIN_ID);
        }
        if (!$_GET["style_id"]) {
            throw new ilSystemStyleException(ilSystemStyleException::NO_STYLE_ID);
        }
        $system_style_config = new ilSystemStyleConfig();
        $skin = ilSkinXML::parseFromXML($system_style_config->getCustomizingSkinPath() . $_GET["skin_id"] . "/template.xml");
        $style = $skin->getStyle($_GET["style_id"]);
        $values["skin_id"] = $skin->getId();
        $values["skin_name"] = $skin->getName();
        $values["style_id"] = $style->getId();
        $values["style_name"] = $style->getName();
        $values["image_dir"] = $style->getImageDirectory();
        $values["font_dir"] = $style->getFontDirectory();
        $values["sound_dir"] = $style->getSoundDirectory();


        if ($style->isSubstyle()) {
            $values["parent_style"] = $style->getSubstyleOf();
        } else {
            $values["active"] = ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(), $style->getId());
            $is_personal_style = $DIC->user()->getPref("skin") == $skin->getId() && $DIC->user()->getPref("style") == $style->getId();
            $values["personal"] = $is_personal_style;
            $is_default_style = ilSystemStyleSettings::getCurrentDefaultSkin() == $skin->getId() && ilSystemStyleSettings::getCurrentDefaultStyle() == $style->getId();
            $values["default"] = $is_default_style;
        }

        $form->setValuesByArray($values);
    }


    protected function save()
    {
        $form = $this->editSystemStyleForm();

        $message_stack = new ilSystemStyleMessageStack();
        if ($form->checkInput()) {
            try {
                $system_style_conf = new ilSystemStyleConfig();
                $skin = ilSkinXML::parseFromXML($system_style_conf->getCustomizingSkinPath() . $_GET["skin_id"] . "/template.xml");
                $style = $skin->getStyle($_GET["style_id"]);

                if ($style->isSubstyle()) {
                    $this->saveSubStyle($message_stack);
                } else {
                    $this->saveStyle($message_stack);
                }

                $message_stack->prependMessage(new ilSystemStyleMessage($this->lng->txt("msg_sys_style_update"), ilSystemStyleMessage::TYPE_SUCCESS));
                $message_stack->sendMessages(true);
                $this->ctrl->redirectByClass("ilSystemStyleSettingsGUI");
            } catch (ilSystemStyleException $e) {
                ilUtil::sendFailure($e->getMessage(), true);
            }
        } else {
            $message_stack->sendMessages();
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * @param ilSystemStyleMessageStack $message_stack
     * @throws ilSystemStyleException
     */
    protected function saveStyle(ilSystemStyleMessageStack $message_stack)
    {
        global $DIC;

        $container = ilSystemStyleSkinContainer::generateFromId($_GET['skin_id'], $message_stack);
        $old_skin = clone $container->getSkin();
        $old_style = clone $old_skin->getStyle($_GET["style_id"]);

        $new_skin = $container->getSkin();
        $new_skin->setId($_POST["skin_id"]);
        $new_skin->setName($_POST["skin_name"]);
        $new_skin->getVersionStep($_POST['skin_version']);

        $new_style = $new_skin->getStyle($_GET["style_id"]);
        $new_style->setId($_POST["style_id"]);
        $new_style->setName($_POST["style_name"]);
        $new_style->setCssFile($_POST["style_id"]);
        $new_style->setImageDirectory($_POST["image_dir"]);
        $new_style->setSoundDirectory($_POST["sound_dir"]);
        $new_style->setFontDirectory($_POST["font_dir"]);

        $container->updateSkin($old_skin);
        $container->updateStyle($new_style->getId(), $old_style);


        if ($_POST["active"] == 1) {
            ilSystemStyleSettings::_activateStyle($new_skin->getId(), $new_style->getId());
            if ($_POST["personal"] == 1) {
                ilSystemStyleSettings::setCurrentUserPrefStyle($new_skin->getId(), $new_style->getId());
            }
            if ($_POST["default"] == 1) {
                ilSystemStyleSettings::setCurrentDefaultStyle($new_skin->getId(), $new_style->getId());
            }
        } else {
            ilSystemStyleSettings::_deactivateStyle($new_skin->getId(), $new_style->getId());
            $_POST["personal"] = 0;
            $_POST["default"] = 0;
        }

        $system_style_conf = new ilSystemStyleConfig();

        //If style has been unset as personal style
        if (!$_POST["personal"] && $DIC->user()->getPref("skin") == $new_skin->getId()) {
            //Reset to default if possible, else change to delos
            if (!$_POST["default"]) {
                ilSystemStyleSettings::setCurrentUserPrefStyle(
                    ilSystemStyleSettings::getCurrentDefaultSkin(),
                    ilSystemStyleSettings::getCurrentDefaultStyle()
                );
            } else {
                ilSystemStyleSettings::setCurrentUserPrefStyle(
                    $system_style_conf->getDefaultSkinId(),
                    $system_style_conf->getDefaultStyleId()
                );
            }
            $message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("personal_style_set_to") . " " . ilSystemStyleSettings::getCurrentUserPrefSkin() . ":" . ilSystemStyleSettings::getCurrentUserPrefStyle(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
        if (!$_POST["default"] && ilSystemStyleSettings::getCurrentDefaultSkin() == $new_skin->getId()) {
            ilSystemStyleSettings::setCurrentDefaultStyle(
                $system_style_conf->getDefaultSkinId(),
                $system_style_conf->getDefaultStyleId()
            );
            $message_stack->addMessage(
                new ilSystemStyleMessage(
                    $this->lng->txt("default_style_set_to") . " " . $system_style_conf->getDefaultSkinId() . ": " . $system_style_conf->getDefaultStyleId(),
                    ilSystemStyleMessage::TYPE_SUCCESS
                )
            );
        }
        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $new_skin->getId());
        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $new_style->getId());
    }

    /**
     * @param $message_stack
     * @throws ilSystemStyleException
     */
    protected function saveSubStyle(ilSystemStyleMessageStack $message_stack)
    {
        $container = ilSystemStyleSkinContainer::generateFromId($_GET['skin_id'], $message_stack);
        $skin = $container->getSkin();
        $old_substyle = clone $skin->getStyle($_GET["style_id"]);

        if (array_key_exists($_POST['style_id'], $skin->getSubstylesOfStyle($old_substyle->getSubstyleOf()))) {
            throw new ilSystemStyleException(ilSystemStyleException::SUBSTYLE_ASSIGNMENT_EXISTS, $_POST['style_id']);
        }

        $new_substyle = $skin->getStyle($_GET["style_id"]);
        $new_substyle->setId($_POST["style_id"]);
        $new_substyle->setName($_POST["style_name"]);
        $new_substyle->setCssFile($_POST["style_id"]);
        $new_substyle->setImageDirectory($_POST["image_dir"]);
        $new_substyle->setSoundDirectory($_POST["sound_dir"]);
        $new_substyle->setFontDirectory($_POST["font_dir"]);
        $new_substyle->setSubstyleOf($old_substyle->getSubstyleOf());

        $container->updateSkin($skin);
        $container->updateStyle($new_substyle->getId(), $old_substyle);

        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $skin->getId());
        $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $new_substyle->getId());
    }

    /**
     * @return ilPropertyFormGUI
     * @throws ilSystemStyleException
     */
    protected function editSystemStyleForm()
    {
        $form = new ilPropertyFormGUI();
        $system_style_conf = new ilSystemStyleConfig();

        $skin = ilSkinXML::parseFromXML($system_style_conf->getCustomizingSkinPath() . $_GET["skin_id"] . "/template.xml");
        $style = $skin->getStyle($_GET["style_id"]);

        $form->setFormAction($this->ctrl->getFormActionByClass("ilsystemstylesettingsgui"));


        if (!$style->isSubstyle()) {
            $form->setTitle($this->lng->txt("skin"));

            $ti = new ilTextInputGUI($this->lng->txt("skin_id"), "skin_id");
            $ti->setMaxLength(128);
            $ti->setSize(40);
            $ti->setRequired(true);
            $ti->setInfo($this->lng->txt("skin_id_description"));
            $form->addItem($ti);

            $ti = new ilTextInputGUI($this->lng->txt("skin_name"), "skin_name");
            $ti->setInfo($this->lng->txt("skin_name_description"));
            $ti->setMaxLength(128);
            $ti->setSize(40);
            $ti->setRequired(true);
            $form->addItem($ti);

            if ($skin->isVersionChangeable()) {
                $ti = new ilNonEditableValueGUI($this->lng->txt("skin_version"), "skin_version");
                $ti->setInfo($this->lng->txt("skin_version_description"));
                $ti->setValue($skin->getVersion());
                $form->addItem($ti);
            }

            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt("style"));
            $form->addItem($section);
        } else {
            $form->setTitle($this->lng->txt("sub_style"));
        }

        $ti = new ilTextInputGUI($this->lng->txt("style_id"), "style_id");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setInfo($this->lng->txt("style_id_description"));
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("style_name"), "style_name");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setInfo($this->lng->txt("style_name_description"));
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("image_dir"), "image_dir");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setInfo($this->lng->txt("image_dir_description"));
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("font_dir"), "font_dir");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setInfo($this->lng->txt("font_dir_description"));
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->lng->txt("sound_dir"), "sound_dir");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setInfo($this->lng->txt("sound_dir_description"));
        $form->addItem($ti);

        if (!$style->isSubstyle()) {
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt("system_style_activation"));
            $form->addItem($section);

            $active = new ilCheckboxInputGUI($this->lng->txt("system_style_activation"), "active");
            $active->setInfo($this->lng->txt("system_style_activation_description"));

            $set_default = new ilCheckboxInputGUI($this->lng->txt("default"), "default");
            $set_default->setInfo($this->lng->txt("system_style_default_description"));
            $active->addSubItem($set_default);

            $set_personal = new ilCheckboxInputGUI($this->lng->txt("personal"), "personal");
            $set_personal->setInfo($this->lng->txt("system_style_personal_description"));
            $active->addSubItem($set_personal);


            $form->addItem($active);
        }

        $form->addCommandButton("save", $this->lng->txt("save"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));

        return $form;
    }
}
