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
 * Web Resource Administration Settings.
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ilCtrl_Calls ilObjWebResourceAdministrationGUI: ilPermissionGUI
 * @ingroup      ModulesWebResource
 */
class ilObjWebResourceAdministrationGUI extends ilObjectGUI
{
    /**
     * ilObjWebResourceAdministrationGUI constructor.
     * @inheritDoc
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        $this->type = "wbrs";
        parent::__construct(
            $a_data,
            $a_id,
            $a_call_by_reference,
            $a_prepare_output
        );
        $this->lng->loadLanguageModule("webr");
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        if (!$this->rbac_system->checkAccess(
            "visible,read",
            $this->object->getRefId()
        )) {
            $this->error->raiseError(
                $this->lng->txt("no_permission"),
                $this->error->WARNING
            );
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
    }

    public function getAdminTabs() : void
    {
        if ($this->rbac_system->checkAccess(
            "visible,read",
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings", "view")
            );
        }

        if ($this->rbac_system->checkAccess(
            "edit_permission",
            $this->object->getRefId()
        )) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm"),
                array(),
                "ilpermissiongui"
            );
        }
    }

    public function editSettings(?ilPropertyFormGUI $a_form = null) : bool
    {
        $this->tabs_gui->setTabActive('settings');
        if (!$a_form) {
            $a_form = $this->initFormSettings();
        }
        $this->tpl->setContent($a_form->getHTML());
        return true;
    }

    public function saveSettings() : void
    {
        $this->checkPermission("write");
        $form = $this->initFormSettings();
        if ($form->checkInput()) {
            $this->settings->set(
                "links_dynamic",
                $form->getInput("links_dynamic")
            );
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt("settings_saved"),
                true
            );
            $this->ctrl->redirect($this, "editSettings");
        }
        $form->setValuesByPost();
        $this->editSettings($form);
    }

    protected function initFormSettings() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
        $form->setTitle($this->lng->txt("settings"));

        // dynamic web links
        $cb = new ilCheckboxInputGUI(
            $this->lng->txt("links_dynamic"),
            "links_dynamic"
        );
        $cb->setInfo($this->lng->txt("links_dynamic_info"));
        $cb->setChecked((bool) $this->settings->get("links_dynamic"));
        $form->addItem($cb);

        if ($this->access->checkAccess(
            "write",
            '',
            $this->object->getRefId()
        )) {
            $form->addCommandButton("saveSettings", $this->lng->txt("save"));
            $form->addCommandButton("view", $this->lng->txt("cancel"));
        }
        return $form;
    }
}
