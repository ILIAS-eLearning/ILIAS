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
 * note: since the last feature (captcha) has been removed from the settings
 * the settings screen is currently not used. If it should be revived, add "wiks" to
 * AdministrationMainBarProvider again.
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjWikiSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjWikiSettingsGUI: ilAdministrationGUI
 */
class ilObjWikiSettingsGUI extends ilObject2GUI
{
    protected ilErrorHandling $error;
    protected ilTabsGUI $tabs;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        global $DIC;

        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];
    }

    public function getType() : string
    {
        return "wiks";
    }

    public function executeCommand() : void
    {
        $ilErr = $this->error;
        $lng = $this->lng;
        
        $lng->loadLanguageModule("wiki");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                if (!$cmd || $cmd === 'view') {
                    $cmd = "editSettings";
                }
                $this->$cmd();
                break;
        }
    }

    protected function editSettings(ilPropertyFormGUI $form = null) : void
    {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;
        
        $ilTabs->activateTab("settings");

        if ($this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            if (!$form) {
                $form = $this->initForm();
                $this->populateWithCurrentSettings($form);
            }
            $tpl->setContent($form->getHTML());
        }
    }

    protected function populateWithCurrentSettings(ilPropertyFormGUI $form) : void
    {
        $form->setValuesByArray([]);
    }

    public function initForm() : ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();


        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton("saveSettings", $lng->txt("save"));
        }

        $form->setTitle($lng->txt("settings"));
        $form->setFormAction($ilCtrl->getFormAction($this));
     
        return $form;
    }
    
    protected function saveSettings() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->checkPermissionBool("write")) {
            $this->editSettings();
            return;
        }

        $form = $this->initForm();
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->editSettings($form);
            return;
        }

        $this->tpl->setOnScreenMessage('success', $lng->txt('msg_obj_modified'), true);
        $ilCtrl->redirect($this, 'editSettings');
    }

    public function getAdminTabs() : void
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
        }
        
        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab(
                "perm_settings",
                $this->lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm")
            );
        }
    }

    public function addToExternalSettingsForm(int $a_form_id) : ?array
    {
        return null;
    }
}
