<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * @author       Lukas Zehnder <lz@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy   ilObjWebDAVGUI: ilAdministrationGUI
 * @ilCtrl_Calls        ilObjWebDAVGUI: ilPermissionGUI
 * @package             webdav
 */
class ilObjWebDAVGUI extends ilObjectGUI
{
    const CMD_EDIT_SETTINGS = 'editSettings';
    const CMD_SAVE_SETTINGS = 'saveSettings';
    
    public ilErrorHandling $error_handling;
    
    public function __construct(?array $a_data, int $a_id, bool $a_call_by_reference)
    {
        global $DIC;
        $this->webdav_dic = new ilWebDAVDIC();
        $this->webdav_dic->init($DIC);

        $this->type = "wbdv";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
    }
    
    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->access->checkAccess('read', '', $this->object->getRefId())) {
            $this->error_handling->raiseError(
                $this->lng->txt('no_permission'),
                $this->error_handling->MESSAGE
            );
        }

        switch ($next_class) {
            case strtolower(ilWebDAVMountInstructionsUploadGUI::class):
                $document_gui = $this->webdav_dic->mountinstructions_upload();
                $document_gui->setRefId($this->object->getRefId());
                $this->tabs_gui->activateTab('webdav_upload_instructions');
                $this->ctrl->forwardCommand($document_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = self::CMD_EDIT_SETTINGS;
                }
                $this->$cmd();
                break;
        }
    }
    
    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                'webdav_general_settings',
                $this->lng->txt("webdav_general_settings"),
                $this->ctrl->getLinkTarget($this, self::CMD_EDIT_SETTINGS)
            );
            $this->tabs_gui->addTab(
                'webdav_upload_instructions',
                $this->lng->txt("webdav_upload_instructions"),
                $this->ctrl->getLinkTargetByClass(ilWebDAVMountInstructionsUploadGUI::class)
            );
        }
    }
    
    public function setTitleAndDescription() : void
    {
        parent::setTitleAndDescription();
        $this->tpl->setDescription($this->object->getDescription());
    }


    protected function initSettingsForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("settings"));
        
        $cb_prop = new ilCheckboxInputGUI($this->lng->txt("enable_webdav"), "enable_webdav");
        $cb_prop->setValue('1');
        $cb_prop->setChecked($this->object->isWebdavEnabled());
        $form->addItem($cb_prop);
        
        $cb_prop = new ilCheckboxInputGUI($this->lng->txt("webdav_enable_versioning"), "enable_versioning_webdav");
        $cb_prop->setValue('1');
        $cb_prop->setInfo($this->lng->txt("webdav_versioning_info"));
        $cb_prop->setChecked($this->object->isWebdavVersioningEnabled());
        $form->addItem($cb_prop);
        
        $form->addCommandButton(self::CMD_SAVE_SETTINGS, $this->lng->txt('save'));

        return $form;
    }
    
    public function editSettings() : void
    {
        $this->tabs_gui->activateTab('webdav_general_settings');

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error_handling->raiseError(
                $this->lng->txt("no_permission"),
                $this->error_handling->WARNING
            );
        }

        $form = $this->initSettingsForm();

        $this->tpl->setContent($form->getHTML());
    }
    
    public function saveSettings() : void
    {
        if (!$this->rbac_system->checkAccess("write", $this->object->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_permission'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        }

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $this->object->setWebdavEnabled($_POST['enable_webdav'] == '1');
            $this->object->setWebdavVersioningEnabled($_POST['enable_versioning_webdav'] == '1');
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, self::CMD_EDIT_SETTINGS);
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
