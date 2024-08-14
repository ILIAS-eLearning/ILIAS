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

declare(strict_types=1);

namespace ILIAS\User\Profile;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ChecklistStatus
{
    public const STEP_PROFILE_DATA = 0;
    public const STEP_PUBLISH_OPTIONS = 1;
    public const STEP_VISIBILITY_OPTIONS = 2;

    public const STATUS_NOT_STARTED = 0;
    public const STATUS_IN_PROGRESS = 1;
    public const STATUS_SUCCESSFUL = 2;

    private \ilSetting $settings_chat;
    private \ilSetting $settings_awareness;

    public function __construct(
        private readonly \ilLanguage $lng,
        private readonly \ilSetting $settings,
        private readonly \ilObjUser $user,
        private readonly Mode $profile_mode
    ) {
        $this->settings_chat = new \ilSetting('chatroom');
        $this->settings_awareness = new \ilSetting('awrn');

        $this->lng->loadLanguageModule('chatroom');
    }
    /**
     * @return array<int,string>
     */
    public function getSteps(): array
    {
        $txt_visibility = $this->anyVisibilitySettings()
            ? $this->lng->txt('user_visibility_settings')
            : $this->lng->txt('preview');

        return [
            self::STEP_PROFILE_DATA => $this->lng->txt('user_profile_data'),
            self::STEP_PUBLISH_OPTIONS => $this->lng->txt('user_publish_options'),
            self::STEP_VISIBILITY_OPTIONS => $txt_visibility
        ];
    }

    public function anyVisibilitySettings(): bool
    {

        return $this->settings_awareness->get('awrn_enabled', '0') !== '0'
            || \ilBuddySystem::getInstance()->isEnabled()
            || $this->areOnScreenChatOptionsVisible()
            || $this->areChatTypingBroadcastOptionsVisible();
    }

    public function getStatus(int $step): int
    {
        switch ($step) {
            case self::STEP_PROFILE_DATA:
                if ($this->user->getProfileIncomplete()) {
                    return self::STATUS_IN_PROGRESS;
                }
                if ($this->user->getPref('profile_personal_data_saved')) {
                    return self::STATUS_SUCCESSFUL;
                }
                break;

            case self::STEP_PUBLISH_OPTIONS:
                if ($this->user->getPref('profile_publish_opt_saved')) {
                    return self::STATUS_SUCCESSFUL;
                }
                break;

            case self::STEP_VISIBILITY_OPTIONS:
                if ($this->user->getPref('profile_visibility_opt_saved')
                        || !$this->anyVisibilitySettings()
                            && $this->user->getPref('profile_publish_opt_saved')) {
                    return self::STATUS_SUCCESSFUL;
                }
                break;
        }

        return self::STATUS_NOT_STARTED;
    }

    public function getStatusDetails(int $step): string
    {
        $status = $this->getStatus($step);
        switch ($step) {
            case self::STEP_PROFILE_DATA:
                if ($status === self::STATUS_SUCCESSFUL) {
                    return $this->lng->txt('user_profile_data_checked');
                }
                return $this->lng->txt('user_check_profile_data');

            case self::STEP_PUBLISH_OPTIONS:
                if ($status === self::STATUS_SUCCESSFUL) {
                    return $this->profile_mode->getModeInfo();
                }
                return $this->lng->txt('user_set_publishing_options');

            case self::STEP_VISIBILITY_OPTIONS:
                if ($status === self::STATUS_SUCCESSFUL) {
                    return $this->buildStatusArrayForVisibilityOnSuccess();
                }
                if ($this->anyVisibilitySettings()) {
                    return $this->lng->txt('user_set_visibilty_options');
                }
                break;
        }
        return '';
    }

    public function saveStepSucess(int $step): void
    {
        switch ($step) {
            case self::STEP_PROFILE_DATA:
                $this->user->setPref('profile_personal_data_saved', '1');
                break;
            case self::STEP_PUBLISH_OPTIONS:
                $this->user->setPref('profile_publish_opt_saved', '1');
                break;
            case self::STEP_VISIBILITY_OPTIONS:
                $this->user->setPref('profile_visibility_opt_saved', '1');
                break;
        }
        $this->user->update();
    }

    private function buildStatusArrayForVisibilityOnSuccess(): string
    {
        $status = [];
        if ($this->settings_awareness->get('awrn_enabled', '0') !== '0') {
            $show = $this->user->getPref('hide_own_online_status') === 'n'
                || $this->user->getPref('hide_own_online_status') ?? '' === ''
                    && $this->settings->get('hide_own_online_status') === 'n';
            $status[] = !$show
                ? $this->lng->txt('hide_own_online_status')
                : $this->lng->txt('show_own_online_status');
        }
        if (\ilBuddySystem::getInstance()->isEnabled()) {
            $status[] = $this->user->getPref('bs_allow_to_contact_me') !== 'y'
                ? $this->lng->txt('buddy_allow_to_contact_me_no')
                : $this->lng->txt('buddy_allow_to_contact_me_yes');
        }
        if ($this->areOnScreenChatOptionsVisible()) {
            $status[] = $this->user->getPref('chat_osc_accept_msg') === 'y'
                ? $this->lng->txt('chat_use_osc')
                : $this->lng->txt('chat_not_use_osc');
        }
        if ($this->areChatTypingBroadcastOptionsVisible()) {
            $status[] = $this->user->getPref('chat_broadcast_typing') === 'y'
                ? $this->lng->txt('chat_use_typing_broadcast')
                : $this->lng->txt('chat_no_use_typing_broadcast');
        }
        return implode(',<br>', $status);
    }

    private function areOnScreenChatOptionsVisible(): bool
    {
        return $this->settings_chat->get('chat_enabled', '0') !== '0'
            && $this->settings_chat->get('enable_osc', '0') !== '0'
            && $this->settings->get('usr_settings_hide_chat_osc_accept_msg', '0') === '0';
    }

    private function areChatTypingBroadcastOptionsVisible(): bool
    {
        return $this->settings_chat->get('chat_enabled', '0')
            && $this->settings->get('usr_settings_hide_chat_broadcast_typing', '0') === '0';
    }
}
