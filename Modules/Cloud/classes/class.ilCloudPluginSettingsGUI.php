<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
include_once("class.ilCloudUtil.php");

/**
 * Class ilCloudPluginSettingsGUI
 *
 * Base class for the settings. Needs to be overwritten if the plugin needs custom settings.
 *
 * @author Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id:

 * @ingroup ModulesCloud
 */

class ilCloudPluginSettingsGUI extends ilCloudPluginGUI
{
    /**
     * @var ilObjCloud
     */
    protected $cloud_object;

    /**
     * @var ilPropertyFormGUI
     */
    protected $form;


    public function setCloudObject(ilObjCloud $object)
    {
        $this->cloud_object = $object;
    }

    /**
     * Edit Settings. This commands uses the form class to display an input form.
     */
    public function editSettings()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];

        $ilTabs->activateTab("settings");

        try {
            $this->initSettingsForm();
            $this->getSettingsValues();
            $tpl->setContent($this->form->getHTML());
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
        }
    }

    public function initSettingsForm()
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];

        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($lng->txt("description"), "desc");
        $this->form->addItem($ta);

        // online
        $cb = new ilCheckboxInputGUI($lng->txt("online"), "online");
        $this->form->addItem($cb);

        $folder = new ilTextInputGUI($lng->txt("cld_root_folder"), "root_folder");
        if (!$this->cloud_object->currentUserIsOwner()) {
            $folder->setDisabled(true);
            $folder->setInfo($lng->txt("cld_only_owner_has_permission_to_change_root_path"));
        }

        $folder->setMaxLength(255);
        $folder->setSize(50);
        $this->form->addItem($folder);

        $this->createPluginSection();
        $this->initPluginSettings();

        $this->form->addCommandButton("updateSettings", $lng->txt("save"));

        $this->form->setTitle($lng->txt("cld_edit_Settings"));
        $this->form->setFormAction($ilCtrl->getFormActionByClass("ilCloudPluginSettingsGUI"));
    }
    protected function createPluginSection()
    {
        if (get_class($this) != "ilCloudPluginSettingsGUI" && $this->getMakeOwnPluginSection()) {
            global $DIC;
            $lng = $DIC['lng'];
            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->cloud_object->getServiceName() . " " . $lng->txt("cld_service_specific_settings"));
            $this->form->addItem($section);
        }
    }

    protected function initPluginSettings()
    {
    }

    protected function getMakeOwnPluginSection()
    {
        return true;
    }

    /**
     * Get values for edit Settings form
     */
    public function getSettingsValues()
    {
        $values["title"]       = $this->cloud_object->getTitle();
        $values["desc"]        = $this->cloud_object->getDescription();
        $values["online"]      = $this->cloud_object->getOnline();
        $values["root_folder"] = $this->cloud_object->getRootFolder();
        $this->getPluginSettingsValues($values);
        $this->form->setValuesByArray($values);
    }


    protected function getPluginSettingsValues(&$values)
    {
    }
    /**
     * Update Settings
     */
    public function updateSettings()
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];

        $ilTabs->activateTab("settings");

        try {
            $this->initSettingsForm();
            if ($this->form->checkInput()) {
                $this->cloud_object->setTitle($this->form->getInput("title"));
                $this->cloud_object->setDescription($this->form->getInput("desc"));
                $this->updatePluginSettings();
                if (ilCloudUtil::normalizePath($this->form->getInput("root_folder")) != $this->cloud_object->getRootFolder()) {
                    $this->cloud_object->setRootFolder($this->form->getInput("root_folder"));
                    $this->cloud_object->setRootId($this->getService()->getRootId($this->cloud_object->getRootFolder()));
                }

                $this->cloud_object->setOnline($this->form->getInput("online"));
                $this->cloud_object->update();
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ilCtrl->redirect($this, 'editSettings');
            }
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }
    protected function updatePluginSettings()
    {
    }
}
