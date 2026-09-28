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
 * @ilCtrl_Calls ilObjHelpSettingsGUI: ilPermissionGUI, ilGuidedTourAdminGUI
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

        if (!$this->rbac_system->checkAccess("read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        switch ($next_class) {

            case strtolower(ilGuidedTourAdminGUI::class):
                $this->tabs_gui->setTabActive('guided_tour');
                $gui = $this->gui->guidedTour()->adminGUI(
                    $this->checkPermissionBool("write")
                );
                $this->ctrl->forwardCommand($gui);
                break;

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

        $table = $this->gui->moduleTableBuilder(
            $this->checkPermissionBool("write"),
            $this,
            "editSettings"
        )->getTable();

        if ($table->handleCommand()) {
            return;
        }

        $this->tpl->setContent($table->render());
    }

    public function getAdminTabs(): void
    {
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );
            $this->tabs_gui->addTab(
                "guided_tour",
                $this->lng->txt("guided_tour"),
                $this->ctrl->getLinkTargetByClass(ilGuidedTourAdminGUI::class)
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

    public function confirmHelpModuleDeletion(int $module_id): void
    {
        $this->checkPermission("write");

        $this->ctrl->setParameterByClass(self::class, "hm_id", $module_id);
        $this->gui->moduleTableBuilder(
            true,
            $this,
            "editSettings"
        )->getTable()->renderDeletionConfirmation(
            $this->lng->txt("help_sure_delete_help_modules"),
            $this->lng->txt("help_sure_delete_help_modules"),
            "deleteHelpModule",
            [
                $module_id => $this->domain->module()->lookupModuleTitle($module_id)
            ]
        );
    }

    public function deleteHelpModule(): void
    {
        $this->checkPermission("write");
        $this->domain->module()->deleteModule($this->help_request->getHelpModuleId());
        $this->ctrl->redirect($this, "editSettings");
    }

    public function activateModule(int $module_id): void
    {
        $this->checkPermission("write");
        $this->domain->module()->activate($module_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editSettings");
    }

    public function deactivateModule(int $module_id): void
    {
        $this->checkPermission("write");
        $this->domain->module()->deactivate($module_id);
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
        $table = $this->gui->moduleTableBuilder(true, $this, "editSettings")->getTable();
        $data = $table->getData();
        $order = [];
        if (is_array($data)) {
            foreach ($data as $position => $module_id) {
                $order[(int) $module_id] = ($position + 1) * 10;
            }
        }
        $this->domain->module()->saveOrder($order);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->redirect($this, "editSettings");
    }

}
