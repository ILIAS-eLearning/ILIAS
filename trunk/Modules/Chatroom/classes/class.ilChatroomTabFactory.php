<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomTabFactory
 * @author  Jan Posselt <jposselt@databay.de>
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomTabFactory
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
	 * Constructor
	 * Sets $this->gui using given $gui.
	 * Sets $this->lng and $this->access
	 * @param ilObjectGUI      $gui
	 */
	public function __construct(ilObjectGUI $gui)
	{
		/**
		 * @var $lng      ilLanguage
		 * @var $ilAccess ilAccessHandler
		 */
		global $lng;

		$this->gui    = $gui;
		$this->lng    = $lng;
	}

	/**
	 * Activates tab or subtab if existing.
	 * Calls $ilTabs->activateTab() or $ilTabs->activateSubTab() method
	 * to set current tab active.
	 * @param array $commandParts
	 */
	private function activateTab(array $commandParts, $config)
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 */
		global $ilTabs;

		if(count($commandParts) > 1)
		{
			if(isset($config[$commandParts[0]]))
			{
				$ilTabs->activateTab($commandParts[0]);

				if(isset($config[$commandParts[0]]['subtabs'][$commandParts[1]]))
				{
					$ilTabs->activateSubTab($commandParts[1]);
				}
			}
		}
		else if(count($commandParts) == 1)
		{
			$ilTabs->activateTab($commandParts[0]);
		}
	}

	/**
	 * Builds $config and $commandparts arrays to assign them as parameters
	 * when calling $this->buildTabs and $this->activateTab.
	 * @param string     $command
	 */
	public function getAdminTabsForCommand($command)
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 * @var $ilCtrl ilCtrl
		 * @var $ilDB   ilDB
		 */
		global $ilTabs, $ilCtrl, $ilDB;

		$command      = $this->convertLowerCamelCaseToUnderscoreCaseConversion($command);
		$stopCommands = array('create');

		if(in_array($command, $stopCommands))
		{
			return;
		}

		$settings        = new ilSetting('chatroom');
		$public_room_ref = $settings->get('public_room_ref');

		$query     = 'SELECT ref_id FROM object_reference INNER JOIN object_data ON object_data.obj_id = object_reference.obj_id WHERE type = ' . $ilDB->quote('chta', 'text');
		$rset      = $ilDB->query($query);
		$data      = $ilDB->fetchAssoc($rset);
		$admin_ref = $data['ref_id'];

		$ilCtrl->setParameterByClass('ilObjChatroomAdminGUI', 'ref_id', $admin_ref);

		$config = array(
			'view'   => array(
				'lng'        => 'settings',
				'link'       => $ilCtrl->getLinkTargetByClass('ilObjChatroomAdminGUI', 'view-clientsettings'),
				'permission' => 'read',
				'subtabs'    => array(
					'clientsettings' => array(
						'lng'        => 'client_settings',
						'link'       => $ilCtrl->getLinkTargetByClass('ilObjChatroomAdminGUI', 'view-clientsettings'),
						'permission' => 'read'
					),
					'serversettings' => array(
						'lng'        => 'server_settings',
						'link'       => $ilCtrl->getLinkTargetByClass('ilObjChatroomAdminGUI', 'view-serversettings'),
						'permission' => 'read'
					)
				)
			),
			'smiley' => array(
				'lng'             => 'smiley',
				'link'            => $ilCtrl->getLinkTargetByClass('ilObjChatroomAdminGUI', 'smiley'),
				'permission'      => 'read'
			)
		);
		$ilCtrl->setParameterByClass('ilObjChatroomGUI', 'ref_id', $public_room_ref);

		$config['settings'] = array(
			'lng'             => 'public_chat_settings',
			'link'            => $ilCtrl->getLinkTargetByClass('ilObjChatroomGUI', 'settings-general'),
			'permission'      => 'write',
			'subtabs'         => array(
				'settings' => array(
					'lng'        => 'settings',
					'link'       => $ilCtrl->getLinkTarget($this->gui, 'settings-general'),
					'permission' => 'write'
				),
				'ban'      => array(
					'lng'        => 'bans',
					'link'       => $ilCtrl->getLinkTargetByClass('ilObjChatroomGUI', 'ban-show'),
					'permission' => 'moderate'
				)
			)
		);

		$ilCtrl->setParameterByClass('ilPermissionGUI', 'ref_id', $public_room_ref);
		$config['perm'] = array(
			'lng'        => 'public_chat_permissions',
			'link'       => $ilCtrl->getLinkTargetByClass('ilPermissionGUI', 'perm'),
			'permission' => 'write',
		);
		$ilCtrl->clearParametersByClass('ilPermissionGUI');

		$ilCtrl->setParameterByClass('ilPermissionGUI', 'ref_id', $admin_ref);
		$config['perm_settings'] = array(
			'lng'        => 'perm_settings',
			'link'       => $ilCtrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
			'permission' => 'write',
		);
		$ilCtrl->clearParametersByClass('ilPermissionGUI');

		$commandParts = explode('_', $command, 2);
		if($command == 'ban_show')
		{
			$commandParts[0] = 'settings';
			$commandParts[1] = 'ban';
		}
		else if($command == 'settings_general')
		{
			$commandParts[0] = 'settings';
			$commandParts[1] = 'settings';
		}
		else if($command == 'view_savesettings')
		{
			$commandParts[0] = 'view';
			$commandParts[1] = 'serversettings';
		}
		else if($command == 'view_saveclientsettings')
		{
			$commandParts[0] = 'view';
			$commandParts[1] = 'clientsettings';
		}
		else if($ilCtrl->getCmdClass() == 'ilpermissiongui' && $_REQUEST['ref_id'] == $public_room_ref)
		{
			$commandParts[0] = 'perm';
			$ilCtrl->setParameterByClass('ilPermissionGUI', 'ref_id', $public_room_ref);
		}
		else if($ilCtrl->getCmdClass() == 'ilpermissiongui' && $_REQUEST['ref_id'] == $admin_ref)
		{
			$commandParts[0] = 'perm_settings';
			$ilCtrl->setParameterByClass('ilPermissionGUI', 'ref_id', $admin_ref);
		}

		$this->buildTabs($ilTabs, $config, $commandParts);
		$this->activateTab($commandParts, $config);
	}

	/**
	 * Builds $config and $commandparts arrays to assign them as parameters
	 * when calling $this->buildTabs and $this->activateTab.
	 * @param string $command
	 */
	public function getTabsForCommand($command)
	{
		/**
		 * @var $ilTabs ilTabsGUI
		 * @var $ilCtrl ilCtrl
		 */
		global $ilTabs, $ilCtrl;

		$command      = $this->convertLowerCamelCaseToUnderscoreCaseConversion($command);
		$stopCommands = array('create');

		if(in_array($command, $stopCommands))
		{
			return;
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		$room = ilChatroom::byObjectId($this->gui->object->getId());

		$config = array(
			'view'     => array(
				'lng'        => 'view',
				'link'       => $ilCtrl->getLinkTarget($this->gui, 'view'),
				'permission' => 'read'
			),
			'history'  => array(
				'lng'        => 'history',
				'link'       => $ilCtrl->getLinkTarget($this->gui, 'history-byday'),
				'permission' => 'read',
				'enabled'    => $room ? $room->getSetting('enable_history') : false,
				'subtabs'    => array(
					'byday'     => array(
						'lng'        => 'history_by_day',
						'link'       => $ilCtrl->getLinkTarget($this->gui, 'history-byday'),
						'permission' => 'read'
					),
					'bysession' => array(
						'lng'        => 'history_by_session',
						'link'       => $ilCtrl->getLinkTarget($this->gui, 'history-bysession'),
						'permission' => 'read'
					)
				)
			),
			'info'     => array(
				'lng'        => 'info_short',
				'link'       => $ilCtrl->getLinkTargetByClass(array(get_class($this->gui), 'ilinfoscreengui'), 'info'),
				'permission' => 'read'
			),
			'settings' => array(
				'lng'        => 'settings',
				'link'       => $ilCtrl->getLinkTarget($this->gui, 'settings-general'),
				'permission' => 'write',
				'subtabs'    => array(
					'general' => array(
						'lng'        => 'settings_general',
						'link'       => $ilCtrl->getLinkTarget($this->gui, 'settings-general'),
						'permission' => 'write'
					)
				)
			),
			'ban'      => array(
				'lng'        => 'bans',
				'link'       => $ilCtrl->getLinkTarget($this->gui, 'ban-show'),
				'permission' => 'moderate',
				'subtabs'    => array(
					'show' => array(
						'lng'        => 'bans_table',
						'link'       => $ilCtrl->getLinkTarget($this->gui, 'ban-show'),
						'permission' => 'moderate'
					)
				)
			),
			'perm'     => array(
				'lng'        => 'permissions',
				'link'       => $ilCtrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
				'permission' => 'write'
			)
		);

		$commandParts = explode('_', $command, 2);

		if($ilCtrl->getCmdClass() == 'ilpermissiongui')
		{
			$commandParts[0] = 'perm';
		}

		$this->buildTabs($ilTabs, $config, $commandParts);
		$this->activateTab($commandParts, $config);
	}

	/**
	 * Builds tabs and subtabs using given $tabs, $config and $command
	 * parameters.
	 * @param ilTabsGUI $tabs
	 * @param array     $config
	 * @param array     $command
	 */
	private function buildTabs(ilTabsGUI $tabs, $config, $command)
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;
		
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		foreach($config as $id => $tabDefinition)
		{
			if(!ilChatroom::checkUserPermissions($tabDefinition['permission'], $this->gui->getRefId(), false))
			{
				continue;
			}
			else if(isset($tabDefinition['enabled']) && !$tabDefinition['enabled'])
			{
				continue;
			}

			$tabs->addTab($id, $this->getLabel($tabDefinition, $id), $tabDefinition['link']);

			if($command[0] == $id && isset($tabDefinition['subtabs']) &&
				is_array($tabDefinition['subtabs'])
			)
			{
				foreach($tabDefinition['subtabs'] as $subid => $subTabDefinition)
				{
					if(!$rbacsystem->checkAccess($subTabDefinition['permission'], $this->gui->getRefId()))
					{
						continue;
					}
					else if(isset($subTabDefinition['enabled']) && !$subTabDefinition['enabled'])
					{
						continue;
					}
					$tabs->addSubTab(
						$subid, $this->getLabel($subTabDefinition, $subid),
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
	 * (array $tabDefinition, string $id)
	 */
	private function getLabel($tabDefinition, $id)
	{
		if(isset($tabDefinition['lng']))
			return $this->lng->txt($tabDefinition['lng']);
		else
			return $this->lng->txt($id);
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
	 * Convert a value given in underscore case conversion to lower camel case conversion (e.g. my_class to MyClass)
	 * @param string  $value            Value in underscore case conversion
	 * @param boolean $upper_case_first If TRUE first character in upper case, lower case if FALSE
	 * @return string The value in lower camel case conversion
	 */
	public static function convertUnderscoreCaseToLowerCamelCaseConversion($value, $upper_case_first = FALSE)
	{
		$tokens = (array)explode('_', $value);
		$value  = '';

		foreach($tokens as $token)
		{
			$value .= ucfirst($token);
		}

		if($upper_case_first === FALSE)
		{
			$value = strtolower($value, 0, 1) . substr($value, 1);
		}

		return $value;
	}
}