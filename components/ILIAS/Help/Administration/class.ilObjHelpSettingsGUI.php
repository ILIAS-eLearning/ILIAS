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
 * @ilCtrl_Calls ilObjHelpSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjHelpSettingsGUI: ilAdministrationGUI
 */
class ilObjHelpSettingsGUI extends ilObject2GUI
{
    protected \ILIAS\Help\InternalGUIService $gui;
    protected \ILIAS\Help\InternalDomainService $domain;
    protected StandardGUIRequest $help_request;
    protected ilTabsGUI $tabs;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        global $DIC;

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $service = $DIC->help()->internal();

        $this->domain = $domain = $service->domain();
        $this->gui = $gui = $service->gui();

        $this->access = $domain->access();
        $this->lng = $domain->lng();
        $this->settings = $domain->settings();

        $this->ctrl = $gui->ctrl();
        $this->tabs = $gui->tabs();
        $this->toolbar = $gui->toolbar();
        $this->tpl = $gui->ui()->mainTemplate();

        $this->help_request = $gui->standardRequest();
    }

    public function getType(): string
    {
        return "hlps";
    }

    public function executeCommand(): void
    {
        $this->lng->loadLanguageModule("help");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {
            case strtolower(ilPermissionGUI::class):
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
        $this->tabs->activateTab("settings");

        if ($this->domain->module()->isAuthoringMode()) {
            $this->tpl->setOnScreenMessage('info', "This installation is used for online help authoring. Help modules cannot be imported.");
            return;
        }

        if ($this->checkPermissionBool("write")) {
            // help file
            $fi = new ilFileInputGUI($this->lng->txt("help_help_file"), "help_file");
            $fi->setSuffixes(array("zip"));
            $this->toolbar->addInputItem($fi, true);
            $this->toolbar->addFormButton($this->lng->txt("upload"), "uploadHelpFile");
            $this->toolbar->addSeparator();

            // help mode
            $options = array(
                "" => $this->lng->txt("help_tooltips_and_help"),
                "1" => $this->lng->txt("help_help_only"),
                "2" => $this->lng->txt("help_tooltips_only")
                );
            $si = new ilSelectInputGUI($this->lng->txt("help_mode"), "help_mode");
            $si->setOptions($options);
            $si->setValue($this->settings->get("help_mode"));
            $this->toolbar->addInputItem($si);

            $this->toolbar->addFormButton($this->lng->txt("help_set_mode"), "setMode");
        }
        $this->toolbar->setFormAction($this->ctrl->getFormAction($this), true);

        $table = new ilHelpModuleTableGUI($this, "editSettings", $this->checkPermissionBool("write"));

        $this->tpl->setContent($table->getHTML());
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
        if (!isset($_FILES["help_file"]["tmp_name"]) || $_FILES["help_file"]["tmp_name"] === "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("help_select_a_file"), true);
            $this->ctrl->redirect($this, "editSettings");
        }
        if ($this->checkPermissionBool("write")) {
            $this->domain->module()->upload($_FILES["help_file"]);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("help_module_uploaded"), true);
        }

        $this->ctrl->redirect($this, "editSettings");
    }

    public function confirmHelpModulesDeletion(): void
    {
        $this->checkPermission("write");

        $ids = $this->help_request->getIds();

        if (count($ids) === 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "editSettings");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($this->ctrl->getFormAction($this));
            $cgui->setHeaderText($this->lng->txt("help_sure_delete_help_modules"));
            $cgui->setCancel($this->lng->txt("cancel"), "editSettings");
            $cgui->setConfirm($this->lng->txt("delete"), "deleteHelpModules");

            foreach ($ids as $i) {
                $cgui->addItem("id[]", $i, $this->domain->module()->lookupModuleLmId($i));
            }

            $this->tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteHelpModules(): void
    {
        $this->checkPermission("write");
        $ids = $this->help_request->getIds();
        foreach ($ids as $i) {
            $this->domain->module()->deleteModule((int) $i);
        }
        $this->ctrl->redirect($this, "editSettings");
    }

    public function activateModule(): void
    {
        $this->checkPermission("write");
        $this->domain->module()->activate($this->help_request->getHelpModuleId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editSettings");
    }

    public function deactivateModule(): void
    {
        $this->checkPermission("write");
        $this->domain->module()->deactivate($this->help_request->getHelpModuleId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editSettings");
    }

    public function setMode(): void
    {
        $this->checkPermission("write");
        if ($this->checkPermissionBool("write")) {
            $this->settings->set(
                "help_mode",
                $this->help_request->getHelpMode()
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        }

        $this->ctrl->redirect($this, "editSettings");
    }

    public function saveOrdering(): void
    {
        $this->checkPermission("write");
        $this->domain->module()->saveOrder($this->help_request->getOrder());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editSettings");
    }

}
