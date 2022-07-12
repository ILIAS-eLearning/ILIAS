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
 * @author Alexander Killing <killing@leifos.de>
 */
class ilProfileChecklistStatus
{
    public const STEP_PROFILE_DATA = 0;
    public const STEP_PUBLISH_OPTIONS = 1;
    public const STEP_VISIBILITY_OPTIONS = 2;

    public const STATUS_NOT_STARTED = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_SUCCESSFUL = 2;
    protected ilPersonalProfileMode $profile_mode;
    protected ilObjUser $user;

    protected ilLanguage $lng;
    protected ilSetting $settings;

    public function __construct(
        ?ilLanguage $lng = null,
        ?ilObjUser $user = null
    ) {
        global $DIC;

        $this->lng = is_null($lng)
            ? $DIC->language()
            : $lng;

        $this->lng->loadLanguageModule('chatroom');
        $this->user = is_null($user)
            ? $DIC->user()
            : $user;

        $this->settings = $DIC->settings();

        $this->profile_mode = new ilPersonalProfileMode($this->user, $DIC->settings());
    }

    private function areOnScreenChatOptionsVisible() : bool
    {
        $chatSettings = new ilSetting('chatroom');

        return (
            $chatSettings->get('chat_enabled', '0') &&
            $chatSettings->get('enable_osc', '0') &&
            !(bool) $this->settings->get('usr_settings_hide_chat_osc_accept_msg', '0')
        );
    }

    private function areChatTypingBroadcastOptionsVisible() : bool
    {
        $chatSettings = new ilSetting('chatroom');

        return (
            $chatSettings->get('chat_enabled', '0') &&
            !(bool) $this->settings->get('usr_settings_hide_chat_broadcast_typing', '0')
        );
    }

    /**
     * @return array<int,string>
     */
    public function getSteps() : array
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
     */
    public function anyVisibilitySettings() : bool
    {
        $awrn_set = new ilSetting("awrn");
        if (
            $awrn_set->get("awrn_enabled", '0') ||
            ilBuddySystem::getInstance()->isEnabled() ||
            $this->areOnScreenChatOptionsVisible() ||
            $this->areChatTypingBroadcastOptionsVisible()
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get status of step
     */
    public function getStatus(int $step) : int
    {
        $status = self::STATUS_NOT_STARTED;
        $user = $this->user;

        switch ($step) {
            case self::STEP_PROFILE_DATA:
                if ($user->getPref("profile_personal_data_saved")) {
                    $status = self::STATUS_SUCCESSFUL;
                }

                if ($user->getProfileIncomplete()) {
                    $status = self::STATUS_IN_PROGRESS;
                }
                break;

            case self::STEP_PUBLISH_OPTIONS:
                if ($user->getPref("profile_publish_opt_saved")) {
                    $status = self::STATUS_SUCCESSFUL;
                }
                break;

            case self::STEP_VISIBILITY_OPTIONS:
                if ($user->getPref("profile_visibility_opt_saved") ||
                    (!$this->anyVisibilitySettings() && $user->getPref("profile_publish_opt_saved"))) {
                    $status = self::STATUS_SUCCESSFUL;
                }
                break;
        }

        return $status;
    }

    /**
     * Get status details
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
                    if ($awrn_set->get("awrn_enabled", '0')) {
                        $show = ($user->getPref("hide_own_online_status") === "n" ||
                            ($user->getPref("hide_own_online_status") == "" && $this->settings->get("hide_own_online_status") === "n"));
                        $status[] = (!$show)
                            ? $lng->txt("hide_own_online_status")
                            : $lng->txt("show_own_online_status");
                    }
                    if (ilBuddySystem::getInstance()->isEnabled()) {
                        $status[] = ($user->getPref("bs_allow_to_contact_me") !== "y")
                            ? $lng->txt("buddy_allow_to_contact_me_no")
                            : $lng->txt("buddy_allow_to_contact_me_yes");
                    }
                    if ($this->areOnScreenChatOptionsVisible()) {
                        $status[] = ilUtil::yn2tf((string) $this->user->getPref('chat_osc_accept_msg'))
                            ? $lng->txt("chat_use_osc")
                            : $lng->txt("chat_not_use_osc");
                    }
                    if ($this->areChatTypingBroadcastOptionsVisible()) {
                        $status[] = ilUtil::yn2tf((string) $this->user->getPref('chat_broadcast_typing'))
                            ? $lng->txt("chat_use_typing_broadcast")
                            : $lng->txt("chat_no_use_typing_broadcast");
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
     */
    public function saveStepSucess(int $step) : void
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
