<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Comments Settings.
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjCommentsSettingsGUI: ilPermissionGUI
 * @ilCtrl_isCalledBy ilObjCommentsSettingsGUI: ilAdministrationGUI
 */
class ilObjCommentsSettingsGUI extends ilObjectGUI
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

        $this->type = 'coms';

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule("note");
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

        $subfields["comm_del_user"] = $f->input()->field()->checkbox(
            $lng->txt("note_enable_comments_del_user")
        )
            ->withValue((bool) $setting->get("comments_del_user", 0));
        $subfields["comm_del_tutor"] = $f->input()->field()->checkbox(
            $lng->txt("note_enable_comments_del_tutor"),
            $lng->txt("note_enable_comments_del_tutor_info")
        )
            ->withValue((bool) $setting->get("comments_del_tutor", 1));
        $subfields["comments_noti_recip"] = $f->input()->field()->text(
            $lng->txt("note_comments_notification"),
            $lng->txt("note_comments_notification_info")
        )
            ->withValue((string) $setting->get("comments_noti_recip"));

        $privacy = ilPrivacySettings::_getInstance();
        $subfields["enable_comments_export"] = $f->input()->field()->checkbox(
            $lng->txt("enable_comments_export"),
            $lng->txt("note_enable_comments_export_info")
        )
            ->withValue((bool) $privacy->enabledCommentsExport());


        $fields["enable_comments"] = $f->input()->field()->optionalGroup(
            $subfields,
            $lng->txt("note_enable_comments"),
            $lng->txt("")
        );
        if ($setting->get("disable_comments")) {
            $fields["enable_comments"] = $fields["enable_comments"]->withValue(null);
        }

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
                $data = $data["sec"]["enable_comments"];
                $setting->set("disable_comments", (is_array($data) ? 0 : 1));
                $setting->set("comments_del_user", ($data["comm_del_user"] ? 1 : 0));
                $setting->set("comments_del_tutor", ($data["comm_del_tutor"] ? 1 : 0));
                $setting->set("comments_noti_recip", $data["comments_noti_recip"]);

                $privacy = ilPrivacySettings::_getInstance();
                $privacy->enableCommentsExport((bool) $data['enable_comments_export']);
                $privacy->save();

                ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            }
        }
        $ctrl->redirect($this, "editSettings");
    }

    public function addToExternalSettingsForm($a_form_id)
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_PRIVACY:

                $privacy = ilPrivacySettings::_getInstance();

                $fields = array('enable_comments_export' => array($privacy->enabledCommentsExport(), ilAdministrationSettingsFormHandler::VALUE_BOOL));

                return array(array("editSettings", $fields));
        }
    }
}
