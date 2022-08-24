<?php

declare(strict_types=1);

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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilChatroomTabGUIFactory
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomTabGUIFactory
{
    private ilObjectGUI $gui;
    private ilLanguage $lng;
    private ilRbacSystem $rbacSystem;
    private GlobalHttpState $http;
    private Refinery $refinery;

    public function __construct(ilObjectGUI $gui)
    {
        /** @var $DIC \ILIAS\DI\Container */
        global $DIC;

        $this->gui = $gui;
        $this->lng = $DIC->language();
        $this->rbacSystem = $DIC->rbac()->system();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    /**
     * Builds $config and $commandparts arrays to assign them as parameters
     * when calling $this->buildTabs and $this->activateTab.
     * @param string $command
     */
    public function getAdminTabsForCommand(string $command): void
    {
        global $DIC;

        $command = self::convertLowerCamelCaseToUnderscoreCaseConversion($command);
        $stopCommands = ['create'];

        if (in_array($command, $stopCommands, true)) {
            return;
        }

        $settings = new ilSetting('chatroom');
        $public_room_ref = (int) $settings->get('public_room_ref', '0');

        $objIds = ilObject::_getObjectsByType('chta');
        $firstObjId = (int) current(array_keys($objIds));
        $refIds = ilObject::_getAllReferences($firstObjId);
        $admin_ref = (int) current($refIds);

        $DIC->ctrl()->setParameterByClass(ilObjChatroomAdminGUI::class, 'ref_id', $admin_ref);

        $config = [
            'view' => [
                'lng' => 'settings',
                'link' => $DIC->ctrl()->getLinkTargetByClass(ilObjChatroomAdminGUI::class, 'view-clientsettings'),
                'permission' => 'read',
                'subtabs' => [
                    'clientsettings' => [
                        'lng' => 'client_settings',
                        'link' => $DIC->ctrl()->getLinkTargetByClass(
                            ilObjChatroomAdminGUI::class,
                            'view-clientsettings'
                        ),
                        'permission' => 'read'
                    ]
                ]
            ],
            'smiley' => [
                'lng' => 'smiley',
                'link' => $DIC->ctrl()->getLinkTargetByClass(ilObjChatroomAdminGUI::class, 'smiley'),
                'permission' => 'read'
            ]
        ];
        $DIC->ctrl()->setParameterByClass(ilObjChatroomGUI::class, 'ref_id', $public_room_ref);

        $config['settings'] = [
            'lng' => 'public_chat_settings',
            'link' => $DIC->ctrl()->getLinkTargetByClass(ilObjChatroomGUI::class, 'settings-general'),
            'permission' => 'read',
            'subtabs' => [
                'settings' => [
                    'lng' => 'settings',
                    'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'settings-general'),
                    'permission' => 'read'
                ],
                'ban' => [
                    'lng' => 'bans',
                    'link' => $DIC->ctrl()->getLinkTargetByClass(ilObjChatroomGUI::class, 'ban-show'),
                    'permission' => 'read'
                ]
            ]
        ];

        $DIC->ctrl()->setParameterByClass(ilPermissionGUI::class, 'ref_id', $public_room_ref);
        $config['perm'] = [
            'lng' => 'public_chat_permissions',
            'link' => $DIC->ctrl()->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
            'permission' => 'read',
        ];
        $DIC->ctrl()->clearParametersByClass(ilPermissionGUI::class);

        $DIC->ctrl()->setParameterByClass(ilPermissionGUI::class, 'ref_id', $admin_ref);
        $config['perm_settings'] = [
            'lng' => 'perm_settings',
            'link' => $DIC->ctrl()->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
            'permission' => 'edit_permission',
        ];
        $DIC->ctrl()->clearParametersByClass(ilPermissionGUI::class);

        $is_in_permission_gui = strtolower($DIC->ctrl()->getCmdClass()) === strtolower(ilPermissionGUI::class);

        $commandParts = explode('_', $command, 2);
        if ($command === 'ban_show') {
            $commandParts[0] = 'settings';
            $commandParts[1] = 'ban';
        } elseif ($command === 'settings_general') {
            $commandParts[0] = 'settings';
            $commandParts[1] = 'settings';
        } elseif ($command === 'view_saveclientsettings') {
            $commandParts[0] = 'view';
            $commandParts[1] = 'clientsettings';
        } elseif (
            $is_in_permission_gui &&
            $this->http->wrapper()->query()->has('ref_id') &&
            $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int()) === $public_room_ref
        ) {
            $commandParts[0] = 'perm';
            $DIC->ctrl()->setParameterByClass(ilPermissionGUI::class, 'ref_id', $public_room_ref);
        } elseif (
            $is_in_permission_gui &&
            $this->http->wrapper()->query()->has('ref_id') &&
            $this->http->wrapper()->query()->retrieve('ref_id', $this->refinery->kindlyTo()->int()) === $admin_ref
        ) {
            $commandParts[0] = 'perm_settings';
            $DIC->ctrl()->setParameterByClass(ilPermissionGUI::class, 'ref_id', $admin_ref);
        }

        $this->buildTabs($DIC->tabs(), $config, $commandParts, false);
        $this->activateTab($commandParts, $config);
    }

    /**
     * Convert a value given in lower camel case conversion to underscore case conversion (e.g. MyClass to my_class)
     * @param string $value Value in lower camel case conversion
     * @return string The value in underscore case conversion
     */
    private static function convertLowerCamelCaseToUnderscoreCaseConversion(string $value): string
    {
        return strtolower(preg_replace('/(.*?)-(.*?)/', '$1_$2', $value));
    }

    /**
     * Builds tabs and subtabs using given $tabs, $config and $command
     * parameters.
     * @param ilTabsGUI $tabs
     * @param array $config
     * @param array $command
     * @param bool $inRoom
     */
    private function buildTabs(ilTabsGUI $tabs, array $config, array $command, bool $inRoom = true): void
    {
        foreach ($config as $id => $tabDefinition) {
            if (!$inRoom && !$this->rbacSystem->checkAccess($tabDefinition['permission'], $this->gui->getRefId())) {
                continue;
            }

            if (
                $inRoom &&
                !ilChatroom::checkUserPermissions($tabDefinition['permission'], $this->gui->getRefId(), false)
            ) {
                continue;
            }

            if (isset($tabDefinition['enabled']) && !$tabDefinition['enabled']) {
                continue;
            }

            $tabs->addTab($id, $this->getLabel($tabDefinition, $id), $tabDefinition['link']);

            if (
                $command[0] === $id && isset($tabDefinition['subtabs']) &&
                is_array($tabDefinition['subtabs'])
            ) {
                foreach ($tabDefinition['subtabs'] as $subid => $subTabDefinition) {
                    if (
                        !$inRoom &&
                        $this->rbacSystem->checkAccess($tabDefinition['permission'], $this->gui->getRefId())
                    ) {
                        continue;
                    }

                    if (
                        $inRoom &&
                        !ilChatroom::checkUserPermissions($subTabDefinition['permission'], $this->gui->getRefId())
                    ) {
                        continue;
                    }

                    if (isset($subTabDefinition['enabled']) && !$subTabDefinition['enabled']) {
                        continue;
                    }

                    $tabs->addSubTab(
                        $subid,
                        $this->getLabel($subTabDefinition, $subid),
                        $subTabDefinition['link']
                    );
                }
            }
        }
    }

    /**
     * Returns label for tab by $tabDefinition or $id
     * @param array $tabDefinition
     * @param string $id
     * @return string
     */
    private function getLabel(array $tabDefinition, string $id): string
    {
        if (isset($tabDefinition['lng'])) {
            return $this->lng->txt($tabDefinition['lng']);
        }

        return $this->lng->txt($id);
    }

    /**
     * Activates tab or subtab if existing.
     * Calls $ilTabs->activateTab() or $ilTabs->activateSubTab() method
     * to set current tab active.
     * @param array $commandParts
     * @param array $config
     */
    private function activateTab(array $commandParts, array $config): void
    {
        global $DIC;

        if (count($commandParts) > 1) {
            if (isset($config[$commandParts[0]])) {
                $DIC->tabs()->activateTab($commandParts[0]);

                if (isset($config[$commandParts[0]]['subtabs'][$commandParts[1]])) {
                    $DIC->tabs()->activateSubTab($commandParts[1]);
                }
            }
        } elseif (count($commandParts) === 1) {
            $DIC->tabs()->activateTab($commandParts[0]);
        }
    }

    /**
     * Builds $config and $commandparts arrays to assign them as parameters
     * when calling $this->buildTabs and $this->activateTab.
     * @param string $command
     */
    public function getTabsForCommand(string $command): void
    {
        global $DIC;

        $command = self::convertLowerCamelCaseToUnderscoreCaseConversion($command);
        $stopCommands = ['create'];

        if (in_array($command, $stopCommands, true)) {
            return;
        }

        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());

        $config = [
            'view' => [
                'lng' => 'view',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'view'),
                'permission' => 'read'
            ],
            'history' => [
                'lng' => 'history',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'history-byday'),
                'permission' => 'read',
                'enabled' => $room ? $room->getSetting('enable_history') : false,
                'subtabs' => [
                    'byday' => [
                        'lng' => 'history_by_day',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'history-byday'),
                        'permission' => 'read'
                    ],
                    'bysession' => [
                        'lng' => 'history_by_session',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'history-bysession'),
                        'permission' => 'read'
                    ]
                ]
            ],
            'info' => [
                'lng' => 'info_short',
                'link' => $DIC->ctrl()->getLinkTargetByClass([get_class($this->gui), ilInfoScreenGUI::class], 'info'),
                'permission' => 'read'
            ],
            'settings' => [
                'lng' => 'settings',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'settings-general'),
                'permission' => 'write',
                'subtabs' => [
                    'general' => [
                        'lng' => 'settings_general',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'settings-general'),
                        'permission' => 'write'
                    ]
                ]
            ],
            'ban' => [
                'lng' => 'bans',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'ban-show'),
                'permission' => 'moderate',
                'subtabs' => [
                    'show' => [
                        'lng' => 'bans_table',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'ban-show'),
                        'permission' => 'moderate'
                    ]
                ]
            ],
            'export' => [
                'lng' => 'export',
                'link' => $DIC->ctrl()->getLinkTargetByClass(ilExportGUI::class, ''),
                'permission' => 'write'
            ],
            'perm' => [
                'lng' => 'permissions',
                'link' => $DIC->ctrl()->getLinkTargetByClass(ilPermissionGUI::class, 'perm'),
                'permission' => 'edit_permission'
            ]
        ];

        $commandParts = explode('_', $command, 2);
        if (strtolower($DIC->ctrl()->getCmdClass()) === strtolower(ilPermissionGUI::class)) {
            $commandParts[0] = 'perm';
        }

        $this->buildTabs($DIC->tabs(), $config, $commandParts);
        $this->activateTab($commandParts, $config);
    }
}
