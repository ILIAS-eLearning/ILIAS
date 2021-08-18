<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Learning History Settings.
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjLearningHistorySettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjLearningHistorySettingsGUI: ilAdministrationGUI
 */
class ilObjLearningHistorySettingsGUI extends ilObjectGUI
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

        $this->type = 'lhts';

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("lhist");
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
     * Edit settings
     */
    public function editSettings()
    {
        $main_tpl = $this->main_tpl;
        $ui = $this->ui;
        $tabs = $this->tabs;

        $tabs->activateTab("settings");

        $form = $this->initForm();
        $main_tpl->setContent($ui->renderer()->render($form));
    }

    /**
     * Init settings form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initForm()
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $setting = $this->setting;

        $fields["enable_learning_history"] = $f->input()->field()->checkbox(
            $lng->txt("lhist_enable_learning_history"),
            $lng->txt("lhist_enable_learning_history_info")
        )
            ->withValue((bool) $setting->get("enable_learning_history"));

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("settings"));


        $form_action = $ctrl->getLinkTarget($this, "saveSettings");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Save settings
     */
    public function saveSettings()
    {
        $request = $this->request;
        $form = $this->initForm();
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $setting = $this->setting;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_array($data["sec"])) {
                $setting->set("enable_learning_history", (int) ($data["sec"]["enable_learning_history"]));
                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            }
        }
        $ctrl->redirect($this, "editSettings");
    }
}
