<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once('./Services/Object/classes/class.ilObjectGUI.php');
include_once('./Modules/StudyProgramme/classes/types/class.ilStudyProgrammeTypeGUI.php');
require_once('./Modules/StudyProgramme/classes/class.ilObjStudyProgrammeAdmin.php');

/**
 * StudyProgramme Administration Settings.
 *
 * @author       Michael Herren <mh@studer-raimann.ch>
 * @author       Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilStudyProgrammeTypeGUI
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilPermissionGUI
 */
class ilObjStudyProgrammeAdminGUI extends ilObjectGUI
{
    /**
     * @param      $a_data
     * @param      $a_id
     * @param bool $a_call_by_reference
     * @param bool $a_prepare_output
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        $ilSetting = $DIC['ilSetting'];
        $this->ctrl = $ilCtrl;
        $this->ilAccess = $ilAccess;
        $this->ilSetting = $ilSetting;
        $this->type = 'prgs';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng->loadLanguageModule('prg');
    }


    /**
     * @return bool|void
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        //Check Permissions globally for all SubGUIs. We only check write permissions
        $this->checkPermission('read');
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                include_once('Services/AccessControl/classes/class.ilPermissionGUI.php');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case 'ilstudyprogrammetypegui':
                $this->tabs_gui->setTabActive('prg_subtypes');
                $type_gui = new ilStudyProgrammeTypeGUI($this);
                $this->ctrl->forwardCommand($type_gui);
                break;
            default:
                if (!$cmd || $cmd == "view") {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
    }

    public function editSettings()
    {
        $this->tabs_gui->setTabActive('settings');

        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
        return true;
    }

    public function initFormSettings(ilPropertyFormGUI $a_form = null)
    {
        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
        $form->setTitle($this->lng->txt("settings"));

        $radio_grp = new ilRadioGroupInputGUI($this->lng->txt("prg_show_programmes"), "visible_on_personal_desktop");
        $radio_grp->addOption(new ilRadioOption($this->lng->txt("prg_show_programmes_on_pd_always"), ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS));
        $radio_grp->addOption(new ilRadioOption($this->lng->txt("prg_show_programmes_on_pd_only_read"), ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ));
        $value = $this->ilSetting->get(ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD);
        $value = ($value) ? $value : ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ;
        $radio_grp->setValue($value);
        $form->addItem($radio_grp);

        if ($this->ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }

        return $form;
    }

    public function saveSettings()
    {
        $this->checkPermission("write");
        
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            if ($this->save($form)) {
                $this->ilSetting->set(
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD,
                    $form->getInput('visible_on_personal_desktop')
                );
                
                ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "editSettings");
            }
        }
        
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    public function getAdminTabs()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        /**
         * @var $rbacsystem ilRbacSystem
         */

        if ($rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('settings', $this->ctrl->getLinkTargetByClass('ilObjStudyProgrammeAdminGUI', 'view'));

            $this->tabs_gui->addTarget('prg_subtypes', $this->ctrl->getLinkTargetByClass(array(
                'ilObjStudyProgrammeAdminGUI',
                'ilStudyProgrammeTypeGUI'
            ), 'listTypes'));
        }
        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget('perm_settings', $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'), array(), 'ilpermissiongui');
        }
    }

    public function _goto($ref_id)
    {
        $this->ctrl->initBaseClass("ilAdministrationGUI");
        $this->ctrl->setParameterByClass("ilObjStudyProgrammeAdminGUI", "ref_id", $ref_id);
        $this->ctrl->setParameterByClass("ilObjStudyProgrammeAdminGUI", "admin_mode", "settings");
        $this->ctrl->redirectByClass(array( "ilAdministrationGUI", "ilObjStudyProgrammeAdminGUI" ), "view");
    }

    protected function save(ilPropertyFormGUI $a_form)
    {
        return true;
    }
}
