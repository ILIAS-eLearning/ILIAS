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

use ILIAS\Help\StandardGUIRequest;

/**
 * Help settings gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilObjHelpSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjHelpSettingsGUI: ilAdministrationGUI
 */
class ilObjHelpSettingsGUI extends ilObject2GUI
{
    protected StandardGUIRequest $help_request;
    protected ilTabsGUI $tabs;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        global $DIC;
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->access = $DIC->access();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC["tpl"];

        $this->help_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    public function getType(): string
    {
        return "hlps";
    }

    public function executeCommand(): void
    {
        $lng = $this->lng;
        $lng->loadLanguageModule("help");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
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

    public function editSettings(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        $ilTabs->activateTab("settings");

        if ((int) OH_REF_ID > 0) {
            $this->tpl->setOnScreenMessage('info', "This installation is used for online help authoring. Help modules cannot be imported.");
            return;
        }

        if ($this->checkPermissionBool("write")) {
            // help file
            $fi = new ilFileInputGUI($lng->txt("help_help_file"), "help_file");
            $fi->setSuffixes(array("zip"));
            $ilToolbar->addInputItem($fi, true);
            $ilToolbar->addFormButton($lng->txt("upload"), "uploadHelpFile");
            $ilToolbar->addSeparator();

            // help mode
            $options = array(
                "" => $lng->txt("help_tooltips_and_help"),
                "1" => $lng->txt("help_help_only"),
                "2" => $lng->txt("help_tooltips_only")
                );
            $si = new ilSelectInputGUI($this->lng->txt("help_mode"), "help_mode");
            $si->setOptions($options);
            $si->setValue($ilSetting->get("help_mode"));
            $ilToolbar->addInputItem($si);

            $ilToolbar->addFormButton($lng->txt("help_set_mode"), "setMode");
        }
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);

        $tab = new ilHelpModuleTableGUI($this, "editSettings", $this->checkPermissionBool("write"));

        $this->tpl->setContent($tab->getHTML());
    }

    public function getAdminTabs(): void
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

    public function uploadHelpFile(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!isset($_FILES["help_file"]["tmp_name"]) || $_FILES["help_file"]["tmp_name"] === "") {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("help_select_a_file"), true);
            $ilCtrl->redirect($this, "editSettings");
        }
        if ($this->checkPermissionBool("write")) {
            $this->object->uploadHelpModule($_FILES["help_file"]);
            $this->tpl->setOnScreenMessage('success', $lng->txt("help_module_uploaded"), true);
        }

        $ilCtrl->redirect($this, "editSettings");
    }

    public function confirmHelpModulesDeletion(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $this->checkPermission("write");

        $ids = $this->help_request->getIds();

        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editSettings");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("help_sure_delete_help_modules"));
            $cgui->setCancel($lng->txt("cancel"), "editSettings");
            $cgui->setConfirm($lng->txt("delete"), "deleteHelpModules");

            foreach ($ids as $i) {
                $cgui->addItem("id[]", $i, $this->object::lookupModuleTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteHelpModules(): void
    {
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $ids = $this->help_request->getIds();
        foreach ($ids as $i) {
            $this->object->deleteModule((int) $i);
        }

        $ilCtrl->redirect($this, "editSettings");
    }

    public function activateModule(): void
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        $ilSetting->set("help_module", $this->help_request->getHelpModuleId());
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "editSettings");
    }

    public function deactivateModule(): void
    {
        $ilSetting = $this->settings;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("write");

        if ((int) $ilSetting->get("help_module") === $this->help_request->getHelpModuleId()) {
            $ilSetting->set("help_module", "");
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "editSettings");
    }

    public function setMode(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        $this->checkPermission("write");

        if ($this->checkPermissionBool("write")) {
            $ilSetting->set(
                "help_mode",
                $this->help_request->getHelpMode()
            );
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "editSettings");
    }
}
