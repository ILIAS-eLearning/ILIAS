<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Web Resource Administration Settings.
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ilCtrl_Calls ilObjWebResourceAdministrationGUI: ilPermissionGUI
 *
 * @ingroup ModulesWebResource
 */
class ilObjWebResourceAdministrationGUI extends ilObjectGUI
{
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        $this->type = "wbrs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("webr");
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt("no_permission"), $this->ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive("perm_settings");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == "view") {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
        return true;
    }

    public function getAdminTabs()
    {

        if ($this->rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($this->rbacsystem->checkAccess("edit_permission", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                array(),
                "ilpermissiongui"
            );
        }
    }
    
    public function editSettings(ilPropertyFormGUI $a_form = null)
    {
        $this->tabs_gui->setTabActive('settings');
                
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
        return true;
    }

    public function saveSettings()
    {
        
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->settings->set("links_dynamic", $form->getInput("links_dynamic"));
            
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "editSettings");
        }
        
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function initFormSettings()
    {

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
        $form->setTitle($this->lng->txt("settings"));
        
        // dynamic web links
        $cb = new ilCheckboxInputGUI($this->lng->txt("links_dynamic"), "links_dynamic");
        $cb->setInfo($this->lng->txt("links_dynamic_info"));
        $cb->setChecked($this->settings->get("links_dynamic"));
        $form->addItem($cb);
    
        if ($this->access->checkAccess("write", '', $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }

        return $form;
    }
}
