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

namespace ILIAS\User\Presentation;

use ILIAS\User\Settings\PersonalSettingsGUI;
use ILIAS\User\Account\DeleteAccountGUI;
use ILIAS\Authentication\Password\LocalUserPasswordManager;
use ILIAS\Language\Language;

class SettingsTabs
{
    private const TAB_ID_SETTINGS = 'settings';
    private const TAB_ID_CHANGE_PASSWORD = 'change_password';
    private const TAB_ID_DELETE_ACCOUNT = 'delete_account';

    public function __construct(
        private readonly \ilTabsGUI $tabs_gui,
        private readonly Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilSetting $settings,
        private readonly \ilObjUser $current_user
    ) {
    }

    public function initializeTabs(): void
    {
        $this->addSettingsTab();

        if ($this->changePasswordAvailable()) {
            $this->addChangePasswordTab();
        }

        if ($this->deleteAccountAvailable()) {
            $this->addDeleteAccountTab();
        }

        $this->activateTab();
    }

    private function addSettingsTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_SETTINGS,
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass(
                [\ilDashboardGUI::class, PersonalSettingsGUI::class],
                'show'
            ),
        );
    }

    private function addChangePasswordTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_CHANGE_PASSWORD,
            $this->lng->txt('chg_password'),
            $this->ctrl->getLinkTargetByClass(
                [
                    \ilDashboardGUI::class,
                    PersonalSettingsGUI::class,
                    \ilLocalUserPasswordSettingsGUI::class
                ],
                \ilLocalUserPasswordSettingsGUI::CMD_SHOW_PASSWORD
            )
        );
    }

    private function addDeleteAccountTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_DELETE_ACCOUNT,
            $this->lng->txt('user_delete_own_account'),
            $this->ctrl->getLinkTargetByClass(
                [\ilDashboardGUI::class, PersonalSettingsGUI::class, DeleteAccountGUI::class],
                'deleteOwnAccountStep1'
            )
        );
    }

    private function activateTab(): void
    {
        $next_class = $this->ctrl->getNextClass();
        if ($next_class === strtolower(DeleteAccountGUI::class)) {
            $this->tabs_gui->activateTab(self::TAB_ID_DELETE_ACCOUNT);
            return;
        }

        if ($next_class === strtolower(\ilLocalUserPasswordSettingsGUI::class)) {
            $this->tabs_gui->activateTab(self::TAB_ID_CHANGE_PASSWORD);
            return;
        }

        $this->tabs_gui->activateTab(self::TAB_ID_SETTINGS);
    }

    private function changePasswordAvailable(): bool
    {
        return LocalUserPasswordManager::getInstance()->allowPasswordChange($this->current_user);
    }

    private function deleteAccountAvailable(): bool
    {
        return $this->settings->get('user_delete_own_account') === '1'
            && $this->current_user->getId() !== SYSTEM_USER_ID;
    }
}
