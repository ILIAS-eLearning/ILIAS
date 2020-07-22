<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomTabGUIFactory
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomTabGUIFactory
{
    /**
     * @var ilObjectGUI
     */
    private $gui;

    /**
     * @var ilLanguage
     */
    private $lng;

    /**
     * @param ilObjectGUI $gui
     */
    public function __construct(ilObjectGUI $gui)
    {
        global $DIC;

        $this->gui = $gui;
        $this->lng = $DIC->language();
    }

    /**
     * Convert a value given in underscore case conversion to lower camel case conversion (e.g. my_class to MyClass)
     * @param string  $value            Value in underscore case conversion
     * @param boolean $upper_case_first If TRUE first character in upper case, lower case if FALSE
     * @return string The value in lower camel case conversion
     */
    public static function convertUnderscoreCaseToLowerCamelCaseConversion($value, $upper_case_first = false)
    {
        $tokens = (array) explode('_', $value);
        $value = '';

        foreach ($tokens as $token) {
            $value .= ucfirst($token);
        }

        if ($upper_case_first === false) {
            $value = strtolower(substr($value, 0, 1)) . substr($value, 1);
        }

        return $value;
    }

    /**
     * Builds $config and $commandparts arrays to assign them as parameters
     * when calling $this->buildTabs and $this->activateTab.
     * @param string $command
     */
    public function getAdminTabsForCommand($command)
    {
        global $DIC;

        $command = $this->convertLowerCamelCaseToUnderscoreCaseConversion($command);
        $stopCommands = array('create');

        if (in_array($command, $stopCommands)) {
            return;
        }

        $settings = new ilSetting('chatroom');
        $public_room_ref = $settings->get('public_room_ref');

        $objIds = ilObject::_getObjectsByType('chta');
        $firstObjId = current(array_keys($objIds));
        $refIds = ilObject::_getAllReferences($firstObjId);
        $admin_ref = current($refIds);

        $DIC->ctrl()->setParameterByClass('ilObjChatroomAdminGUI', 'ref_id', $admin_ref);

        $config = array(
            'view' => array(
                'lng' => 'settings',
                'link' => $DIC->ctrl()->getLinkTargetByClass('ilObjChatroomAdminGUI', 'view-clientsettings'),
                'permission' => 'read',
                'subtabs' => array(
                    'clientsettings' => array(
                        'lng' => 'client_settings',
                        'link' => $DIC->ctrl()->getLinkTargetByClass('ilObjChatroomAdminGUI', 'view-clientsettings'),
                        'permission' => 'read'
                    ),
                    'serversettings' => array(
                        'lng' => 'server_settings',
                        'link' => $DIC->ctrl()->getLinkTargetByClass('ilObjChatroomAdminGUI', 'view-serversettings'),
                        'permission' => 'read'
                    )
                )
            ),
            'smiley' => array(
                'lng' => 'smiley',
                'link' => $DIC->ctrl()->getLinkTargetByClass('ilObjChatroomAdminGUI', 'smiley'),
                'permission' => 'read'
            )
        );
        $DIC->ctrl()->setParameterByClass('ilObjChatroomGUI', 'ref_id', $public_room_ref);

        $config['settings'] = array(
            'lng' => 'public_chat_settings',
            'link' => $DIC->ctrl()->getLinkTargetByClass('ilObjChatroomGUI', 'settings-general'),
            'permission' => 'write',
            'subtabs' => array(
                'settings' => array(
                    'lng' => 'settings',
                    'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'settings-general'),
                    'permission' => 'write'
                ),
                'ban' => array(
                    'lng' => 'bans',
                    'link' => $DIC->ctrl()->getLinkTargetByClass('ilObjChatroomGUI', 'ban-show'),
                    'permission' => 'moderate'
                )
            )
        );

        $DIC->ctrl()->setParameterByClass('ilPermissionGUI', 'ref_id', $public_room_ref);
        $config['perm'] = array(
            'lng' => 'public_chat_permissions',
            'link' => $DIC->ctrl()->getLinkTargetByClass('ilPermissionGUI', 'perm'),
            'permission' => 'edit_permission',
        );
        $DIC->ctrl()->clearParametersByClass('ilPermissionGUI');

        $DIC->ctrl()->setParameterByClass('ilPermissionGUI', 'ref_id', $admin_ref);
        $config['perm_settings'] = array(
            'lng' => 'perm_settings',
            'link' => $DIC->ctrl()->getLinkTargetByClass('ilpermissiongui', 'perm'),
            'permission' => 'edit_permission',
        );
        $DIC->ctrl()->clearParametersByClass('ilPermissionGUI');

        $commandParts = explode('_', $command, 2);
        if ($command == 'ban_show') {
            $commandParts[0] = 'settings';
            $commandParts[1] = 'ban';
        } elseif ($command == 'settings_general') {
            $commandParts[0] = 'settings';
            $commandParts[1] = 'settings';
        } elseif ($command == 'view_savesettings') {
            $commandParts[0] = 'view';
            $commandParts[1] = 'serversettings';
        } elseif ($command == 'view_saveclientsettings') {
            $commandParts[0] = 'view';
            $commandParts[1] = 'clientsettings';
        } elseif ($DIC->ctrl()->getCmdClass() == 'ilpermissiongui' && $_REQUEST['ref_id'] == $public_room_ref) {
            $commandParts[0] = 'perm';
            $DIC->ctrl()->setParameterByClass('ilPermissionGUI', 'ref_id', $public_room_ref);
        } elseif ($DIC->ctrl()->getCmdClass() == 'ilpermissiongui' && $_REQUEST['ref_id'] == $admin_ref) {
            $commandParts[0] = 'perm_settings';
            $DIC->ctrl()->setParameterByClass('ilPermissionGUI', 'ref_id', $admin_ref);
        }

        $this->buildTabs($DIC->tabs(), $config, $commandParts, false);
        $this->activateTab($commandParts, $config);
    }

    /**
     * Convert a value given in lower camel case conversion to underscore case conversion (e.g. MyClass to my_class)
     * @param string $value Value in lower camel case conversion
     * @return string The value in underscore case conversion
     */
    public static function convertLowerCamelCaseToUnderscoreCaseConversion($value)
    {
        return strtolower(preg_replace('/(.*?)-(.*?)/', '$1_$2', $value));
    }

    /**
     * Builds tabs and subtabs using given $tabs, $config and $command
     * parameters.
     * @param ilTabsGUI $tabs
     * @param array     $config
     * @param array     $command
     * @param bool      $inRoom
     */
    private function buildTabs(ilTabsGUI $tabs, $config, $command, $inRoom = true)
    {
        global $DIC;

        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
        foreach ($config as $id => $tabDefinition) {
            if (!$inRoom && !$DIC->rbac()->system()->checkAccess($tabDefinition['permission'], $this->gui->getRefId())) {
                continue;
            } elseif ($inRoom && !ilChatroom::checkUserPermissions($tabDefinition['permission'], $this->gui->getRefId(), false)) {
                continue;
            } elseif (isset($tabDefinition['enabled']) && !$tabDefinition['enabled']) {
                continue;
            }

            $tabs->addTab($id, $this->getLabel($tabDefinition, $id), $tabDefinition['link']);

            if ($command[0] == $id && isset($tabDefinition['subtabs']) &&
                is_array($tabDefinition['subtabs'])
            ) {
                foreach ($tabDefinition['subtabs'] as $subid => $subTabDefinition) {
                    if (!$inRoom && !$DIC->rbac()->system()->checkAccess($tabDefinition['permission'], $this->gui->getRefId())) {
                        continue;
                    } elseif ($inRoom && !ilChatroom::checkUserPermissions($subTabDefinition['permission'], $this->gui->getRefId())) {
                        continue;
                    } elseif (isset($subTabDefinition['enabled']) && !$subTabDefinition['enabled']) {
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
     * @param array  $tabDefinition
     * @param string $id
     * @return string
     * @todo: $tabDefinition sollte doch stets ein array und $id stets ein
     *      string sein, oder? Dann sollte man auch hier typehinten.
     *      (array $tabDefinition, string $id)
     */
    private function getLabel($tabDefinition, $id)
    {
        if (isset($tabDefinition['lng'])) {
            return $this->lng->txt($tabDefinition['lng']);
        } else {
            return $this->lng->txt($id);
        }
    }

    /**
     * Activates tab or subtab if existing.
     * Calls $ilTabs->activateTab() or $ilTabs->activateSubTab() method
     * to set current tab active.
     * @param array $commandParts
     */
    private function activateTab(array $commandParts, $config)
    {
        global $DIC;

        if (count($commandParts) > 1) {
            if (isset($config[$commandParts[0]])) {
                $DIC->tabs()->activateTab($commandParts[0]);

                if (isset($config[$commandParts[0]]['subtabs'][$commandParts[1]])) {
                    $DIC->tabs()->activateSubTab($commandParts[1]);
                }
            }
        } elseif (count($commandParts) == 1) {
            $DIC->tabs()->activateTab($commandParts[0]);
        }
    }

    /**
     * Builds $config and $commandparts arrays to assign them as parameters
     * when calling $this->buildTabs and $this->activateTab.
     * @param string $command
     */
    public function getTabsForCommand($command)
    {
        global $DIC;

        $command = $this->convertLowerCamelCaseToUnderscoreCaseConversion($command);
        $stopCommands = array('create');

        if (in_array($command, $stopCommands)) {
            return;
        }

        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
        $room = ilChatroom::byObjectId($this->gui->object->getId());

        $config = array(
            'view' => array(
                'lng' => 'view',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'view'),
                'permission' => 'read'
            ),
            'history' => array(
                'lng' => 'history',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'history-byday'),
                'permission' => 'read',
                'enabled' => $room ? $room->getSetting('enable_history') : false,
                'subtabs' => array(
                    'byday' => array(
                        'lng' => 'history_by_day',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'history-byday'),
                        'permission' => 'read'
                    ),
                    'bysession' => array(
                        'lng' => 'history_by_session',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'history-bysession'),
                        'permission' => 'read'
                    )
                )
            ),
            'info' => array(
                'lng' => 'info_short',
                'link' => $DIC->ctrl()->getLinkTargetByClass(array(get_class($this->gui), 'ilinfoscreengui'), 'info'),
                'permission' => 'read'
            ),
            'settings' => array(
                'lng' => 'settings',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'settings-general'),
                'permission' => 'write',
                'subtabs' => array(
                    'general' => array(
                        'lng' => 'settings_general',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'settings-general'),
                        'permission' => 'write'
                    )
                )
            ),
            'ban' => array(
                'lng' => 'bans',
                'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'ban-show'),
                'permission' => 'moderate',
                'subtabs' => array(
                    'show' => array(
                        'lng' => 'bans_table',
                        'link' => $DIC->ctrl()->getLinkTarget($this->gui, 'ban-show'),
                        'permission' => 'moderate'
                    )
                )
            ),
            'export' => array(
                'lng' => 'export',
                'link' => $DIC->ctrl()->getLinkTargetByClass('ilexportgui', ''),
                'permission' => 'write'
            ),
            'perm' => array(
                'lng' => 'permissions',
                'link' => $DIC->ctrl()->getLinkTargetByClass('ilpermissiongui', 'perm'),
                'permission' => 'edit_permission'
            )
        );

        $commandParts = explode('_', $command, 2);

        if ($DIC->ctrl()->getCmdClass() == 'ilpermissiongui') {
            $commandParts[0] = 'perm';
        }

        $this->buildTabs($DIC->tabs(), $config, $commandParts);
        $this->activateTab($commandParts, $config);
    }
}
