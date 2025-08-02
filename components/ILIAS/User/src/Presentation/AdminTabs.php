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

use ILIAS\User\Settings\Administration\SettingsGUI as AdminSettingsGUI;
use ILIAS\User\Settings\User\ConfigurationGUI as UserSettingsConfigurationGUI;
use ILIAS\User\Settings\NewAccountMail\SettingsGUI as NewAccountMailSettingsGUI;
use ILIAS\User\Settings\StartingPoint\SettingsGUI as StartingPointSettingsGUI;
use ILIAS\User\Profile\Fields\StandardFieldsGUI;
use ILIAS\User\Profile\Fields\CustomFieldsGUI;
use ILIAS\User\Profile\Prompt\SettingsGUI as ProfileSettingsGUI;
use ILIAS\Language\Language;

class AdminTabs
{
    private const TAB_ID_ACCOUNTS = 'accounts';
    private const TAB_ID_EXTENDED_SEARCH = 'search_user_extended';
    private const TAB_ID_SETTINGS = 'settings';
    private const TAB_ID_PROFILE = 'profile';
    private const TAB_ID_EXPORT = 'export';
    private const TAB_ID_PERMISSIONS = 'perm_settings';

    private const SUBTAB_ID_SETTINGS_AMIN = 'admin_settings';
    private const SUBTAB_ID_SETTINGS_USER = 'user_settings';
    private const SUBTAB_ID_SETTINGS_NEW_ACCOUNT_MAIL = 'new_account_mail';
    private const SUBTAB_ID_SETTINGS_STARTING_POINTS = 'starting_points';

    private const SUBTAB_ID_PROFILE_STANDARD_FIELDS = 'standard_fields';
    private const SUBTAB_ID_PROFILE_CUSTOM_FIELDS = 'custom_fields';
    private const SUBTAB_ID_PROFILE_INFO = 'profile_info';

    private const CMD_CLASSES_WITH_SETTINGS_SUBTABS = [
        AdminSettingsGUI::class,
        UserSettingsConfigurationGUI::class,
        NewAccountMailSettingsGUI::class,
        StartingPointSettingsGUI::class
    ];

    private const CMD_CLASSES_WITH_PROFILE_SUBTABS = [
        StandardFieldsGUI::class,
        CustomFieldsGUI::class,
        ProfileSettingsGUI::class
    ];

    public function __construct(
        private readonly \ilTabsGUI $tabs_gui,
        private readonly Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilAccess $access,
        private readonly int $ref_id
    ) {
    }

    public function initializeTabs(): void
    {
        if ($this->readAccessToAccountsGranted()) {
            $this->addAccountsTab();
            $this->addExtendedSearchTab();
        }

        if ($this->editSettingsAccessGranted()) {
            $this->addSettingsTab();
            $this->addProfileTab();
            $this->addExportTab();
            $this->initializeSubTabs();
        }

        if ($this->accessToPermissionsGranted()) {
            $this->addPermissionsTab();
        }

        $this->activateTab();
    }

    private function addAccountsTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_ACCOUNTS,
            $this->lng->txt('usrf'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class],
                'view'
            )
        );
    }

    private function addSettingsTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_SETTINGS,
            $this->lng->txt('settings'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, AdminSettingsGUI::class],
                'show'
            )
        );
    }

    private function addProfileTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_PROFILE,
            $this->lng->txt('profile'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, StandardFieldsGUI::class],
                'show'
            )
        );
    }

    private function addExportTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_EXPORT,
            $this->lng->txt('export'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, \ilExportGUI::class],
                'export'
            )
        );
    }

    private function addPermissionsTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_PERMISSIONS,
            $this->lng->txt('perm_settings'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, \ilPermissionGUI::class],
                'perm'
            )
        );
    }

    private function addExtendedSearchTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_EXTENDED_SEARCH,
            $this->lng->txt('search_user_extended'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, \ilRepositorySearchGUI::class],
                ''
            )
        );
    }

    private function activateTab(): void
    {
        if ($this->checkCmdClassInArray(self::CMD_CLASSES_WITH_SETTINGS_SUBTABS)) {
            $this->tabs_gui->activateTab(self::TAB_ID_SETTINGS);
            $this->activateSettingsSubTab();
            return;
        }

        if ($this->checkCmdClassInArray(self::CMD_CLASSES_WITH_PROFILE_SUBTABS)) {
            $this->tabs_gui->activateTab(self::TAB_ID_PROFILE);
            $this->activateProfileSubTab();
            return;
        }

        switch ($this->ctrl->getCmdClass()) {
            case strtolower(\ilObjUserFolderGUI::class):
                $this->tabs_gui->activateTab(self::TAB_ID_ACCOUNTS);
                break;
            case strtolower(\ilRepositorySearchGUI::class):
                $this->tabs_gui->activateTab(self::TAB_ID_EXTENDED_SEARCH);
                break;
            case strtolower(\ilExportGUI::class):
                $this->tabs_gui->activateTab(self::TAB_ID_EXPORT);
                break;
            case strtolower(\ilPermissionGUI::class):
                $this->tabs_gui->activateTab(self::TAB_ID_PERMISSIONS);
                break;
        }
    }

    private function initializeSubTabs(): void
    {
        if ($this->checkCmdClassInArray(self::CMD_CLASSES_WITH_SETTINGS_SUBTABS)) {
            $this->addSettingsSubTabs();
            return;
        }

        if ($this->checkCmdClassInArray(self::CMD_CLASSES_WITH_PROFILE_SUBTABS)) {
            $this->addProfileSubTabs();
            return;
        }
    }

    private function addSettingsSubTabs(): void
    {
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_SETTINGS_AMIN,
            $this->lng->txt('administrative_settings'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, AdminSettingsGUI::class],
                'show'
            )
        );
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_SETTINGS_USER,
            $this->lng->txt('user_settings'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, UserSettingsConfigurationGUI::class],
                'show'
            )
        );
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_SETTINGS_NEW_ACCOUNT_MAIL,
            $this->lng->txt('registration_user_new_account_mail'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, NewAccountMailSettingsGUI::class],
                'show'
            )
        );
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_SETTINGS_STARTING_POINTS,
            $this->lng->txt('starting_points'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, StartingPointSettingsGUI::class],
                'startingPoints'
            )
        );
    }

    private function addProfileSubTabs(): void
    {
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_PROFILE_STANDARD_FIELDS,
            $this->lng->txt('standard_fields'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, StandardFieldsGUI::class],
                'show'
            )
        );
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_PROFILE_CUSTOM_FIELDS,
            $this->lng->txt('user_defined_fields'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, CustomFieldsGUI::class],
                'listUserDefinedFields'
            )
        );
        $this->tabs_gui->addSubTab(
            self::SUBTAB_ID_PROFILE_INFO,
            $this->lng->txt('user_profile_info'),
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, ProfileSettingsGUI::class],
                ''
            )
        );
    }

    private function activateSettingsSubTab(): void
    {
        switch ($this->ctrl->getNextClass()) {
            case strtolower(AdminSettingsGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_SETTINGS_AMIN);
                break;
            case strtolower(UserSettingsConfigurationGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_SETTINGS_USER);
                break;
            case strtolower(NewAccountMailSettingsGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_SETTINGS_NEW_ACCOUNT_MAIL);
                break;
            case strtolower(StartingPointSettingsGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_SETTINGS_STARTING_POINTS);
                break;
        }
    }

    private function activateProfileSubTab(): void
    {
        switch ($this->ctrl->getNextClass()) {
            case strtolower(StandardFieldsGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_PROFILE_STANDARD_FIELDS);
                break;
            case strtolower(CustomFieldsGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_PROFILE_CUSTOM_FIELDS);
                break;
            case strtolower(ProfileSettingsGUI::class):
                $this->tabs_gui->activateSubTab(self::SUBTAB_ID_PROFILE_INFO);
                break;
        }
    }

    private function accessToPermissionsGranted(): bool
    {
        return $this->access->checkAccess(
            'edit_permission',
            '',
            $this->ref_id
        );
    }

    private function readAccessToAccountsGranted(): bool
    {
        return $this->access->checkRbacOrPositionPermissionAccess(
            'read',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            $this->ref_id
        );
    }

    private function editSettingsAccessGranted(): bool
    {
        return $this->access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            $this->ref_id
        );
    }

    private function checkCmdClassInArray(array $class_array): bool
    {
        $cmd_class = $this->ctrl->getNextClass();
        return array_filter(
            $class_array,
            static fn(string $class): bool => $cmd_class === strtolower($class)
        ) !== [];
    }
}
