<?php declare(strict_types=1);

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
 * StudyProgramme Administration Settings.
 * @author       Michael Herren <mh@studer-raimann.ch>
 * @author       Stefan Hecken <stefan.hecken@concepts-and-training.de>
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilStudyProgrammeTypeGUI
 * @ilCtrl_Calls ilObjStudyProgrammeAdminGUI: ilPermissionGUI
 */
class ilObjStudyProgrammeAdminGUI extends ilObjectGUI
{
    protected ilErrorHandling $error;
    protected ilStudyProgrammeTypeGUI $type_gui;

    public function __construct($data, int $id, bool $call_by_reference = true, bool $prepare_output = true)
    {
        parent::__construct($data, $id, $call_by_reference, $prepare_output);

        $this->type = 'prgs';
        $this->lng->loadLanguageModule('prg');
        $this->type_gui = ilStudyProgrammeDIC::dic()['ilStudyProgrammeTypeGUI'];
    }

    public function executeCommand() : void
    {
        //Check Permissions globally for all SubGUIs. We only check write permissions
        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt("no_permission"), $this->error->WARNING);
        }
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            case 'ilstudyprogrammetypegui':
                $this->tabs_gui->activateTab('prg_subtypes');
                $this->type_gui->setParentGUI($this);
                $this->ctrl->forwardCommand($this->type_gui);
                break;
            default:
                if (!$cmd || $cmd === "view") {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
    }

    public function editSettings(ilPropertyFormGUI $form = null) : bool
    {
        $this->tabs_gui->activateTab('settings');
        if (is_null($form)) {
            $form = $this->initFormSettings();
        }
        $this->tpl->setContent($form->getHTML());
        return true;
    }

    public function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
        $form->setTitle($this->lng->txt("settings"));

        $radio_grp = new ilRadioGroupInputGUI($this->lng->txt("prg_show_programmes"), "visible_on_personal_desktop");
        $radio_grp->addOption(new ilRadioOption(
            $this->lng->txt("prg_show_programmes_on_pd_always"),
            ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_ALLWAYS
        ));
        $radio_grp->addOption(new ilRadioOption(
            $this->lng->txt("prg_show_programmes_on_pd_only_read"),
            ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ
        ));
        $value = $this->settings->get(ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD);
        $value = ($value) ?: ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD_READ;
        $radio_grp->setValue($value);
        $form->addItem($radio_grp);

        if ($this->access->checkAccess("write", "", $this->object->getRefId())) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }

        return $form;
    }

    public function saveSettings() : void
    {
        $this->checkPermission("write");

        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            if ($this->save($form)) {
                $this->settings->set(
                    ilObjStudyProgrammeAdmin::SETTING_VISIBLE_ON_PD,
                    $form->getInput('visible_on_personal_desktop')
                );

                $this->tpl->setOnScreenMessage("success", $this->lng->txt("settings_saved"), true);
                $this->ctrl->redirect($this, "editSettings");
            }
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess('visible,read', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'settings',
                $this->ctrl->getLinkTargetByClass('ilObjStudyProgrammeAdminGUI', 'view')
            );

            $this->tabs_gui->addTarget(
                'prg_subtypes',
                $this->ctrl->getLinkTargetByClass(
                    array('ilObjStudyProgrammeAdminGUI', 'ilStudyProgrammeTypeGUI'),
                    'listTypes'
                )
            );
        }
        if ($this->rbac_system->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'perm_settings',
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
                array(),
                'ilpermissiongui'
            );
        }
    }

    public function _goto($ref_id) : void
    {
        $this->ctrl->setTargetScript('ilias.php');
        $this->ctrl->setParameterByClass("ilObjStudyProgrammeAdminGUI", "ref_id", $ref_id);
        $this->ctrl->setParameterByClass("ilObjStudyProgrammeAdminGUI", "admin_mode", "settings");
        $this->ctrl->redirectByClass(array("ilAdministrationGUI", "ilObjStudyProgrammeAdminGUI"), "view");
    }

    protected function save(ilPropertyFormGUI $form) : bool
    {
        return true;
    }
}
