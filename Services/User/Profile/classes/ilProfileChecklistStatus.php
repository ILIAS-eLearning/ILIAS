<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * @author killing@leifos.de
 */
class ilProfileChecklistStatus
{
    const STEP_PROFILE_DATA = 0;
    const STEP_PUBLISH_OPTIONS = 1;
    const STEP_VISIBILITY_OPTIONS = 2;

    const STATUS_NOT_STARTED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_SUCCESSFUL = 2;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * Constructor
     */
    public function __construct($lng = null, $user = null)
    {
        global $DIC;

        $this->lng = is_null($lng)
            ? $DIC->language()
            : $lng;

        $this->user = is_null($user)
            ? $DIC->user()
            : $user;

        $this->settings = $DIC->settings();

        $this->profile_mode = new ilPersonalProfileMode($this->user, $DIC->settings());
    }

    /**
     * Get steps
     *
     * @param
     * @return
     */
    public function getSteps()
    {
        $lng = $this->lng;

        $txt_visibility = $this->anyVisibilitySettings()
            ? $lng->txt("user_visibility_settings")
            : $lng->txt("preview");

        return [
            self::STEP_PROFILE_DATA => $lng->txt("user_profile_data"),
            self::STEP_PUBLISH_OPTIONS => $lng->txt("user_publish_options"),
            self::STEP_VISIBILITY_OPTIONS => $txt_visibility
        ];
    }

    /**
     * Any visibility settings?
     *
     * @return bool
     */
    public function anyVisibilitySettings() : bool
    {
        $awrn_set = new ilSetting("awrn");
        if ($awrn_set->get("awrn_enabled", false) ||
            ilBuddySystem::getInstance()->isEnabled()) {
            return true;
        }

        return false;
    }

    /**
     * Get status of step
     *
     * @param int
     * @return int
     */
    public function getStatus(int $step)
    {
        $status = self::STATUS_NOT_STARTED;
        $user = $this->user;

        switch ($step) {
            case self::STEP_PROFILE_DATA:
                if ($user->getPref("profile_personal_data_saved")) {
                    $status = self::STATUS_SUCCESSFUL;
                };
                if ($user->getProfileIncomplete()) {
                    $status = self::STATUS_IN_PROGRESS;
                }
                break;
            case self::STEP_PUBLISH_OPTIONS:
                if ($user->getPref("profile_publish_opt_saved")) {
                    $status = self::STATUS_SUCCESSFUL;
                };
                break;
            case self::STEP_VISIBILITY_OPTIONS:
                if ($user->getPref("profile_visibility_opt_saved") ||
                    (!$this->anyVisibilitySettings() && $user->getPref("profile_publish_opt_saved"))) {
                    $status = self::STATUS_SUCCESSFUL;
                };
                break;
        }

        return $status;
    }

    /**
     * Get status details
     *
     * @param int $step
     * @return string
     */
    public function getStatusDetails(int $step) : string
    {
        $lng = $this->lng;
        $user = $this->user;
        $status = $this->getStatus($step);
        $details = "";
        switch ($step) {
            case self::STEP_PROFILE_DATA:
                if ($status == self::STATUS_SUCCESSFUL) {
                    $details = $lng->txt("user_profile_data_checked");
                } else {
                    $details = $lng->txt("user_check_profile_data");
                }
                break;

            case self::STEP_PUBLISH_OPTIONS:
                if ($status == self::STATUS_SUCCESSFUL) {
                    $details = $this->profile_mode->getModeInfo();
                } else {
                    $details = $lng->txt("user_set_publishing_options");
                }
                break;

            case self::STEP_VISIBILITY_OPTIONS:
                if ($status == self::STATUS_SUCCESSFUL) {
                    $awrn_set = new ilSetting("awrn");
                    $status = [];
                    if ($awrn_set->get("awrn_enabled", false)) {
                        $show = ($user->getPref("hide_own_online_status") == "n" ||
                            ($user->getPref("hide_own_online_status") == "" && $this->settings->get("hide_own_online_status") == "n"));
                        $status[] = (!$show)
                            ? $lng->txt("hide_own_online_status")
                            : $lng->txt("show_own_online_status");
                    }
                    if (ilBuddySystem::getInstance()->isEnabled()) {
                        $status[] = ($user->getPref("bs_allow_to_contact_me") != "y")
                            ? $lng->txt("buddy_allow_to_contact_me_no")
                            : $lng->txt("buddy_allow_to_contact_me_yes");
                    }
                    $details = implode(",<br>", $status);
                } else {
                    if ($this->anyVisibilitySettings()) {
                        $details = $lng->txt("user_set_visibilty_options");
                    }
                }
                break;
        }
        return $details;
    }

    
    /**
     * Save step success
     *
     * @param $step
     */
    public function saveStepSucess($step)
    {
        $user = $this->user;
        switch ($step) {
            case self::STEP_PROFILE_DATA:
                $user->setPref("profile_personal_data_saved", "1");
                break;
            case self::STEP_PUBLISH_OPTIONS:
                $user->setPref("profile_publish_opt_saved", "1");
                break;
            case self::STEP_VISIBILITY_OPTIONS:
                $user->setPref("profile_visibility_opt_saved", "1");
                break;
        }
        $user->update();
    }
}
