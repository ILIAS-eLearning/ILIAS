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
 * Personal Workspace Settings.
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjPersonalWorkspaceSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjPersonalWorkspaceSettingsGUI: ilAdministrationGUI
 */
class ilObjPersonalWorkspaceSettingsGUI extends ilObjectGUI
{
    protected ilTabsGUI $tabs;
    protected \ILIAS\DI\UIServices $ui;
    protected ilSetting $setting;
    protected ilGlobalTemplateInterface $main_tpl;

    /**
     * @param mixed $a_data
     */
    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = true,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->ctrl = $DIC->ctrl();
        $this->request = $DIC->http()->request();
        $this->tabs = $DIC->tabs();
        $this->ui = $DIC->ui();
        $this->setting = $DIC->settings();
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->type = 'prss';

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("pwsp");
    }

    /**
     * @throws ilCtrlException
     * @throws ilPermissionException
     */
    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $rbacsystem = $this->rbacsystem;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("editSettings");

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt('no_permission'));
        }

        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                $tabs->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ctrl->forwardCommand($perm_gui);
                break;

            default:
                if ($cmd == "view") {
                    $cmd = "editSettings";
                }
                if (in_array($cmd, ["editSettings", "saveSettings"])) {
                    $this->$cmd();
                }
                break;
        }
    }

    public function getAdminTabs() : void
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $tabs->addTab(
                "settings",
                $lng->txt("settings"),
                $ctrl->getLinkTarget($this, "editSettings")
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $tabs->addTab(
                "perm_settings",
                $lng->txt("perm_settings"),
                $ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    public function editSettings() : void
    {
        $form = $this->getSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    public function getSettingsForm() : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $tabs = $this->tabs;

        $tabs->activateTab("settings");

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveWsp"));
        $form->setTitle($lng->txt("obj_prss"));

        // Enable 'Personal Workspace'
        $wsp_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_personal_resources'), 'wsp');
        $wsp_prop->setValue('1');
        $wsp_prop->setChecked((bool) $ilSetting->get('disable_personal_workspace'));
        $form->addItem($wsp_prop);

        // Enable 'Blogs'
        $blog_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_wsp_blogs'), 'blog');
        $blog_prop->setValue('1');
        $blog_prop->setChecked((bool) $ilSetting->get('disable_wsp_blogs'));
        $wsp_prop->addSubItem($blog_prop);

        // Enable 'Files'
        $file_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_wsp_files'), 'file');
        $file_prop->setValue('1');
        $file_prop->setChecked((bool) $ilSetting->get('disable_wsp_files'));
        $wsp_prop->addSubItem($file_prop);

        // Enable 'Links'
        $link_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_wsp_links'), 'link');
        $link_prop->setValue('1');
        $link_prop->setChecked((bool) $ilSetting->get('disable_wsp_links'));
        $wsp_prop->addSubItem($link_prop);

        if ($this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            // command buttons
            $form->addCommandButton("saveSettings", $lng->txt("save"));
            $form->addCommandButton("editSettings", $lng->txt("cancel"));
        }
        return $form;
    }

    public function saveSettings() : void
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $ilCtrl->redirect($this, "view");
        }

        $form = $this->getSettingsForm();
        if ($form->checkInput()) {
            $wsp = $form->getInput("wsp");
            $blog = $form->getInput("blog");
            $file = $form->getInput("file");
            $link = $form->getInput("link");

            // without personal workspace we have to disable to sub-items
            if (!$wsp) {
                $blog = 0;
                $file = 0;
                $link = 0;
            }

            $ilSetting->set('disable_personal_workspace', (string) $wsp);
            $ilSetting->set('disable_wsp_blogs', (string) $blog);
            $ilSetting->set('disable_wsp_files', (string) $file);
            $ilSetting->set('disable_wsp_links', (string) $link);
        }

        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "editSettings");
    }
}
