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

namespace ILIAS\User\Administration;

use ILIAS\User\Profile\Prompt\SettingsGUI;

class Tabs
{
    private const TAB_ID_ACCOUNTS = 'accounts';
    private const TAB_ID_EXTENDED_SEARCH = 'search_user_extended';
    private const TAB_ID_SETTINGS = 'settings';
    private const TAB_ID_PROFILE = 'profile';
    private const TAB_ID_EXPORT = 'export';
    private const TAB_ID_PERMISSIONS = 'edit_permissions';

    private const SUBTAB_ID_SETTINGS_AMIN = 'admin_settings';
    private const SUBTAB_ID_SETTINGS_USER = 'user_settings';
    private const SUBTAB_ID_SETTINGS_NEW_ACCOUNT_MAIL = 'new_account_mail';
    private const SUBTAB_ID_SETTINGS_STARTING_POINTS = 'starting_points';

    private const SUBTAB_ID_PROFILE_STANDARD_FIELDS = 'standard_fields';
    private const SUBTAB_ID_PROFILE_CUSTOM_FIELDS = 'custom_fields';
    private const SUBTAB_ID_PROFILE_INFO = 'profile_info';

    private const CMD_CLASSES_WITH_SETTINGS_SUBTABS = [
        SettingsGUI::class,
        ilCustomUserFieldsGUI::class,
        \ilUserStartingPointGUI::class,
    ];

    private const CMD_CLASSES_WITH_PROFILE_SUBTABS = [

    ];

    public function __construct(
        private readonly \ilTabsGUI $tabs_gui,
        private readonly \ilLanguage $lng,
        private readonly \ilCtrlInterface $ctrl,
        private readonly \ilAccess $access,
        private readonly int $ref_id
    ) {
    }

    public function activateTab(string $tab_id): void
    {
        if (in_array(
            $tab_id,
            [
                self::TAB_ID_ACCOUNTS,
                self::TAB_ID_EXTENDED_SEARCH,
                self::TAB_ID_SETTINGS,
                self::TAB_ID_PROFILE,
                self::TAB_ID_EXPORT,
                self::TAB_ID_PERMISSIONS
            ]
        )) {
            $this->tabs_gui->activateTab($tab_id);
        }
    }

    public function activateSubTab(string $sub_tab_id): void
    {
        if (in_array(
            $sub_tab_id,
            [
                self::SUBTAB_ID_SETTINGS_AMIN,
                self::SUBTAB_ID_SETTINGS_USER,
                self::SUBTAB_ID_SETTINGS_NEW_ACCOUNT_MAIL,
                self::SUBTAB_ID_SETTINGS_STARTING_POINTS,
                self::SUBTAB_ID_PROFILE_STANDARD_FIELDS,
                self::SUBTAB_ID_PROFILE_CUSTOM_FIELDS,
                self::SUBTAB_ID_PROFILE_INFO
            ]
        )) {
            $this->tabs_gui->activateSubTab($sub_tab_id);
        }
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
    }

    private function addAccountsTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_ACCOUNTS,
            'usrf',
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
            'settings',
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class],
                'adminSettings'
            )
        );
    }

    private function addProfileTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_PROFILE,
            'profile',
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class],
                'standardFields'
            )
        );
    }

    private function addExportTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_EXPORT,
            'export',
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class],
                'export'
            )
        );
    }

    private function addPermissionsTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_PERMISSIONS,
            'perm_settings',
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilPermissionGUI::class],
                'perm'
            )
        );
    }

    private function addExtendedSearchTab(): void
    {
        $this->tabs_gui->addTab(
            self::TAB_ID_EXTENDED_SEARCH,
            'search_user_extended',
            $this->ctrl->getLinkTargetByClass(
                [\ilAdministrationGUI::class, \ilObjUserFolderGUI::class, \ilRepositorySearchGUI::class],
                ''
            )
        );
    }

    private function initializeSubTabs(): void
    {
        if (in_array($this->ctrl->getCmdClass(), self::CMD_CLASSES_WITH_SETTINGS_SUBTABS)) {
            $this->addSettingsSubTabs();
        }

        if (in_array($this->ctrl->getCmdClass(), self::CMD_CLASSES_WITH_PROFILE_SUBTABS)) {
            $this->addProfileSubTabs();
        }
    }

    private function addSettingsSubTabs(): void
    {

    }

    private function addProfileSubTabs(): void
    {

    }

    private function accessToPermissionsGranted(): bool
    {
        return $this->access->checkAccess(
            'edit_permission',
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
}
