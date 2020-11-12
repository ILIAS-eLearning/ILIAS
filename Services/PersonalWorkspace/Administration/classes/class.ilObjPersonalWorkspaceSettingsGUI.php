<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Personal Workspace Settings.
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjPersonalWorkspaceSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjPersonalWorkspaceSettingsGUI: ilAdministrationGUI
 */
class ilObjPersonalWorkspaceSettingsGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;


    /**
     * @var \ilSetting
     */
    protected $setting;

    /**
     * @var \ilTemplate
     */
    protected $main_tpl;


    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
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
     * Execute command
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $rbacsystem = $this->rbacsystem;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("editSettings");

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->error->raiseError($this->lng->txt('no_permission'), $this->error->WARNING);
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

    /**
     * Get tabs
     */
    public function getAdminTabs()
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

    /**
     * Edit personal workspace settings.
     */
    public function editSettings()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $tabs = $this->tabs;

        $tabs->activateTab("settings");

        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveWsp"));
        $form->setTitle($lng->txt("obj_prss"));

        // Enable 'Personal Workspace'
        $wsp_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_personal_resources'), 'wsp');
        $wsp_prop->setValue('1');
        $wsp_prop->setChecked(($ilSetting->get('disable_personal_workspace') ? '0' : '1'));
        $form->addItem($wsp_prop);

        // Enable 'Blogs'
        $blog_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_wsp_blogs'), 'blog');
        $blog_prop->setValue('1');
        $blog_prop->setChecked(($ilSetting->get('disable_wsp_blogs') ? '0' : '1'));
        $wsp_prop->addSubItem($blog_prop);

        // Enable 'Files'
        $file_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_wsp_files'), 'file');
        $file_prop->setValue('1');
        $file_prop->setChecked(($ilSetting->get('disable_wsp_files') ? '0' : '1'));
        $wsp_prop->addSubItem($file_prop);

        // Enable 'Links'
        $link_prop = new ilCheckboxInputGUI($lng->txt('pwsp_enable_wsp_links'), 'link');
        $link_prop->setValue('1');
        $link_prop->setChecked(($ilSetting->get('disable_wsp_links') ? '0' : '1'));
        $wsp_prop->addSubItem($link_prop);

        if ($this->rbacsystem->checkAccess('write', $this->object->getRefId())) {
            // command buttons
            $form->addCommandButton("saveSettings", $lng->txt("save"));
            $form->addCommandButton("editSettings", $lng->txt("cancel"));
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Save personal desktop settings
     */
    public function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $ilAccess = $this->access;

        if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $ilCtrl->redirect($this, "view");
        }

        // without personal workspace we have to disable to sub-items
        if (!$_POST["wsp"]) {
            $_POST["blog"] = 0;
            $_POST["file"] = 0;
            $_POST["link"] = 0;
        }

        $ilSetting->set('disable_personal_workspace', (int) ($_POST['wsp'] ? 0 : 1));
        $ilSetting->set('disable_wsp_blogs', (int) ($_POST['blog'] ? 0 : 1));
        $ilSetting->set('disable_wsp_files', (int) ($_POST['file'] ? 0 : 1));
        $ilSetting->set('disable_wsp_links', (int) ($_POST['link'] ? 0 : 1));
        // $ilSetting->set('user_portfolios', (int)($_POST['prtf'] ? 1 : 0));

        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        $ilCtrl->redirect($this, "editSettings");
    }
}
