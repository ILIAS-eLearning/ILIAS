<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * User privacy settings (currently located under "Profile and Privacy")
 *
 * @author killing@leifos.de
 */
class ilUserPrivacySettingsGUI
{

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $main_tpl;

    /**
     * @var ilUserSettingsConfig
     */
    protected $user_settings_config;

    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @var ilProfileChecklistStatus
     */
    protected $checklist_status;

    /**
     * @var ilPersonalProfileMode
     */
    protected $profile_mode;

    /**
     * constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->lng->loadLanguageModule("user");
        $this->ui = $DIC->ui();
        $this->user = $DIC->user();

        $this->request = $DIC->http()->request();

        $this->user_settings_config = new ilUserSettingsConfig();
        $this->settings = $DIC->settings();
        $this->checklist_status = new ilProfileChecklistStatus();
        $this->profile_mode = new ilPersonalProfileMode($this->user, $this->settings);
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            default:
                $cmd = $this->ctrl->getCmd("showPrivacySettings");
                $this->$cmd();
                break;
        }
        $this->main_tpl->printToStdout();
    }


    //
    //
    //	GENERAL SETTINGS FORM
    //
    //

    /**
     * @param string $setting
     * @return bool
     */
    public function workWithUserSetting(string $setting) : bool
    {
        return $this->user_settings_config->isVisibleAndChangeable($setting);
    }

    /**
     * @param string $setting
     * @return bool
     */
    public function userSettingVisible(string $setting) : bool
    {
        return $this->user_settings_config->isVisible($setting);
    }

    /**
     * General settings form.
     */
    public function showPrivacySettings($form = null)
    {
        $main_tpl = $this->main_tpl;
        $ui = $this->ui;
        $user = $this->user;
        $lng = $this->lng;

        $html = "";
        if ($this->checklist_status->anyVisibilitySettings()) {
            if (is_null($form)) {
                $form = $this->initPrivacySettingsForm();
            }
            $html = $ui->renderer()->render([$form]);
        }

        $pub_profile = new ilPublicUserProfileGUI($user->getId());
        if ($this->profile_mode->isEnabled()) {
            $html .= $pub_profile->getEmbeddable();
        } else {
            if (!$this->checklist_status->anyVisibilitySettings()) {
                $html .= $ui->renderer()->render([$ui->factory()->messageBox()->info($lng->txt("usr_public_profile_disabled"))]);
            }
        }

        $main_tpl->setContent($html);
    }

    /**
     * Is awareness tool setting visible
     *
     * @return bool
     */
    protected function isAwarnessSettingVisible() : bool
    {
        $awrn_set = new ilSetting("awrn");
        if ($awrn_set->get("awrn_enabled", false) && $this->userSettingVisible("hide_own_online_status")) {
            return true;
        }
        return false;
    }

    /**
     * Is contact setting visible
     *
     * @return bool
     */
    protected function isContactSettingVisible() : bool
    {
        if (ilBuddySystem::getInstance()->isEnabled() && $this->userSettingVisible('bs_allow_to_contact_me')) {
            return true;
        }
        return false;
    }


    /**
     * Init  form.
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function initPrivacySettingsForm()
    {
        $ui = $this->ui;
        $f = $ui->factory();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $user = $this->user;
        $settings = $this->settings;

        $fields = [];

        // hide_own_online_status
        if ($this->isAwarnessSettingVisible()) {
            $lng->loadLanguageModule("awrn");

            $default = ($this->settings->get('hide_own_online_status') == "n")
                ? $this->lng->txt("user_awrn_show")
                : $this->lng->txt("user_awrn_hide");

            $options = array(
                "x" => $this->lng->txt("user_awrn_default")." (".$default.")",
                "n" => $this->lng->txt("user_awrn_show"),
                "y" => $this->lng->txt("user_awrn_hide"));
            $val = $user->prefs["hide_own_online_status"];
            if ($val == "") {
                $val = "x";
            }
            $fields["hide_own_online_status"] = $f->input()->field()->select(
                $lng->txt("awrn_user_show"),
                $options,
                $lng->txt("awrn_hide_from_awareness_info"))
                ->withValue($val)
                ->withRequired(true)
                ->withDisabled($settings->get("usr_settings_disable_hide_own_online_status")
                );
        }

        // allow to contact me
        if ($this->isContactSettingVisible()) {
            $lng->loadLanguageModule('buddysystem');
            $fields["bs_allow_to_contact_me"] = $f->input()->field()->checkbox(
                $lng->txt("buddy_allow_to_contact_me"),
                $lng->txt("buddy_allow_to_contact_me_info")
            )
                ->withValue($user->prefs['bs_allow_to_contact_me'] == 'y')
                ->withDisabled($settings->get('usr_settings_disable_bs_allow_to_contact_me'));
        }

        // section
        $section1 = $f->input()->field()->section($fields, $lng->txt("user_visibility_settings"));

        $form_action = $ctrl->getLinkTarget($this, "savePrivacySettings");
        return $f->input()->container()->form()->standard($form_action, ["sec" => $section1]);
    }

    /**
     * Save privacy settings
     */
    public function savePrivacySettings()
    {
        $request = $this->request;
        $form = $this->initPrivacySettingsForm();
        $lng = $this->lng;
        $user = $this->user;
        $ctrl = $this->ctrl;

        if ($request->getMethod() == "POST") {
            $form = $form->withRequest($request);
            $data = $form->getData();
            if (is_array($data["sec"])) {
                if ($this->isAwarnessSettingVisible() && $this->workWithUserSetting("hide_own_online_status")) {
                    $val = $data["sec"]["hide_own_online_status"];
                    if ($val == "x") {
                        $val = "";
                    }
                    $user->setPref("hide_own_online_status",
                        $val);
                }
                if ($this->isContactSettingVisible() && $this->workWithUserSetting("bs_allow_to_contact_me")) {
                    if ($data["sec"]["bs_allow_to_contact_me"]) {
                        $user->setPref("bs_allow_to_contact_me", "y");
                    } else {
                        $user->setPref("bs_allow_to_contact_me", "n");
                    }
                }
                $user->update();
                $this->checklist_status->saveStepSucess(ilProfileChecklistStatus::STEP_VISIBILITY_OPTIONS);
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ctrl->redirect($this, "");
            }
        }
        $this->showPrivacySettings($form);
    }
}
