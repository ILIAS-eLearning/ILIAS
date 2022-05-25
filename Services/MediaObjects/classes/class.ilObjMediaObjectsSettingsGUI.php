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

/**
 * Media Objects/Pools Settings.
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjMediaObjectsSettingsGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjMediaObjectsSettingsGUI: ilAdministrationGUI
 */
class ilObjMediaObjectsSettingsGUI extends ilObjectGUI
{
    protected ilPropertyFormGUI $form;
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->type = 'mobs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('mob');
        $this->lng->loadLanguageModule('mep');
        $this->lng->loadLanguageModule('content');
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
    }

    public function getAdminTabs() : void
    {
        $ilAccess = $this->access;
        $ilTabs = $this->tabs;

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
            $ilTabs->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm"),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function editSettings(
        bool $a_omit_init = false
    ) : void {
        $tpl = $this->tpl;
        
        if (!$a_omit_init) {
            $this->initMediaObjectsSettingsForm();
            $this->getSettingsValues();
        }
        $tpl->setContent($this->form->getHTML());
    }
        
    public function saveSettings() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
    
        $this->checkPermission("write");
        
        $this->initMediaObjectsSettingsForm();
        if ($this->form->checkInput()) {
            // perform save
            $mset = new ilSetting("mobs");
            $mset->set("mep_activate_pages", $this->form->getInput("activate_pages"));
            $mset->set("file_manager_always", $this->form->getInput("file_manager_always"));
            $mset->set("restricted_file_types", $this->form->getInput("restricted_file_types"));
            $mset->set("black_list_file_types", $this->form->getInput("black_list_file_types"));

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
        
        $this->form->setValuesByPost();
        $this->editSettings(true);
    }
    
    /**
     * Init media objects settings form.
     */
    public function initMediaObjectsSettingsForm() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        
    
        $this->form = new ilPropertyFormGUI();
    
        // activate page in media pool
        $cb = new ilCheckboxInputGUI($lng->txt("mobs_activate_pages"), "activate_pages");
        $cb->setInfo($lng->txt("mobs_activate_pages_info"));
        $this->form->addItem($cb);
    
        // activate page in media pool
        $cb = new ilCheckboxInputGUI($lng->txt("mobs_always_show_file_manager"), "file_manager_always");
        $cb->setInfo($lng->txt("mobs_always_show_file_manager_info"));
        $this->form->addItem($cb);
        
        // allowed file types
        $ta = new ilTextAreaInputGUI($this->lng->txt("mobs_restrict_file_types"), "restricted_file_types");
        //$ta->setCols();
        //$ta->setRows();
        $ta->setInfo($this->lng->txt("mobs_restrict_file_types_info"));
        $this->form->addItem($ta);

        // black lis file types
        $ta = new ilTextAreaInputGUI($this->lng->txt("mobs_black_list_file_types"), "black_list_file_types");
        $ta->setInfo($this->lng->txt("mobs_black_list_file_types_info"));
        $this->form->addItem($ta);

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->form->addCommandButton("saveSettings", $lng->txt("save"));
        }

        $this->form->setTitle($lng->txt("settings"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function getSettingsValues() : void
    {
        $values = array();
    
        $mset = new ilSetting("mobs");
        $values["activate_pages"] = $mset->get("mep_activate_pages");
        $values["file_manager_always"] = $mset->get("file_manager_always");
        $values["restricted_file_types"] = $mset->get("restricted_file_types");
        $values["black_list_file_types"] = $mset->get("black_list_file_types");

        $this->form->setValuesByArray($values);
    }
}
