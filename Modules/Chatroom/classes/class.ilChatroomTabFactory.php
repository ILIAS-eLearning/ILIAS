<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroomTabFactory
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomTabFactory
{

	private $gui;
	private $lng;
	private $access;

	/**
	 * Constructor
	 *
	 * Sets $this->gui using given $gui.
	 * Sets $this->lng and $this->access
	 *
	 * @global ilLanguage $lng
	 * @global ilAccessHandler $ilAccess
	 * @param ilObjectGUI $gui
	 */
	public function __construct(ilObjectGUI $gui)
	{
		global $lng, $ilAccess;

		$this->gui		= $gui;
		$this->lng		= $lng;
		$this->access	= $ilAccess;
	}

	/**
	 * Activates tab or subtab if existing.
	 *
	 * Calls $ilTabs->activateTab() or $ilTabs->activateSubTab() method
	 * to set current tab active.
	 *
	 * @global ilTabsGUI $ilTabs
	 * @param array $command
	 * @todo: config muss auch immer ein array sein, oder?! typehint?
	 */
	private function activateTab(array $commandParts, $config)
	{
		global $ilTabs;

		if( count( $commandParts ) > 1 )
		{
			if( isset( $config[$commandParts[0]] ) )
			{
				$ilTabs->activateTab( $commandParts[0] );

				if( isset( $config[$commandParts[0]]['subtabs'][$commandParts[1]] ) )
				{
					$ilTabs->activateSubTab( $commandParts[1] );
				}
			}
		}
		else if( count($commandParts) == 1 && isset( $config[$commandParts[0]] ) )
		{
			$ilTabs->activateTab( $commandParts[0] );
		}
	}

	/**
	 * Builds $config and $commandparts arrays to assign them as parameters
	 * when calling $this->buildTabs and $this->activateTab.
	 *
	 * @global ilTabsGUI $ilTabs
	 * @global ilCtrl2 $ilCtrl
	 * @param string $command
	 * @todo: $command muss eig. immer ein string sein, oder?
	 * Dann könnte man hier typehinten (string $command)
	 */
	public function getAdminTabsForCommand($command)
	{
		global $ilTabs, $ilCtrl;

		$command = $this->convertLowerCamelCaseToUnderscoreCaseConversion( $command );
		$stopCommands = array('create');

		if( in_array( $command, $stopCommands ) )
		{
			return;
		}

		$config = array(
			'smiley' => array(
				'lng'			=> 'smiley',
				'link'			=> $ilCtrl->getLinkTarget( $this->gui, 'smiley' ),
				'permission'	=> 'read'
				),
			  'view' => array(
			  'lng' => 'view',
			  'link' => $ilCtrl->getLinkTarget($this->gui, 'view'),
			  'permission' => 'read',
				'subtabs' => array(
					'view' => array(
						'lng' => 'server_settings',
						'link' => $ilCtrl->getLinkTarget( $this->gui, 'view' ),
						'permission' => 'read',
				),
					'clientsettings' => array(
						'lng' => 'client_settings',
						'link' => $ilCtrl->getLinkTarget( $this->gui, 'view-clientsettings' ),
						'permission' => 'read',
				)
				),
				),
				);

				$commandParts = explode( '_', $command, 2 );

				$this->buildTabs( $ilTabs, $config, $commandParts );
				$this->activateTab( $commandParts, $config );
	}

	/**
	 * Builds $config and $commandparts arrays to assign them as parameters
	 * when calling $this->buildTabs and $this->activateTab.
	 *
	 * @global ilTabsGUI $ilTabs
	 * @global ilCtrl2 $ilCtrl
	 * @param string $command
	 * @todo: $command muss eig. immer ein string sein, oder?
	 * Dann könnte man hier typehinten (string $command)
	 */
	public function getTabsForCommand($command)
	{
		global $ilTabs, $ilCtrl;

		$command = $this->convertLowerCamelCaseToUnderscoreCaseConversion( $command );
		$stopCommands = array('create');

		if( in_array( $command, $stopCommands ) )
		{
			return;
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		$config = array(
			'view' => array(
				'lng' => 'view',
				'link' => $ilCtrl->getLinkTarget( $this->gui, 'view' ),
				'permission' => 'read'
				),
			'history' => array(
				'lng' => 'history',
				'link' => $ilCtrl->getLinkTarget( $this->gui, 'history-byday' ),
				'permission' => 'read',
				'enabled' => $room ? $room->getSetting('enable_history') : false,
				'subtabs' => array(
					'byday' => array(
						'lng' => 'history_by_day',
						'link' => $ilCtrl->getLinkTarget( $this->gui, 'history-byday' ),
						'permission' => 'read',
				),
					'bysession' => array(
						'lng' => 'history_by_session',
						'link' => $ilCtrl->getLinkTarget( $this->gui, 'history-bysession' ),
						'permission' => 'read',
				)
				),
				),
			'info' => array(
				'lng' => 'info_short',
				'link' => $ilCtrl->getLinkTargetByClass( array(get_class( $this->gui ), 'ilinfoscreengui'), 'info' ),
				'permission' => 'read'
				),
			'settings' => array(
				'lng' => 'settings',
				'link' => $ilCtrl->getLinkTarget( $this->gui, 'settings-general' ),
				'permission' => 'write',
				'subtabs' => array(
					'general' => array(
						'lng' => 'settings_general',
						'link' => $ilCtrl->getLinkTarget( $this->gui, 'settings-general' ),
						'permission' => 'write',
				)/*,
				'privacy' => array(
				'lng' => 'settings_privacy',
				'link' => $ilCtrl->getLinkTarget( $this->gui, 'settings-privacy' ),
				'permission' => 'write',
				)*/
				),
				),
			'ban' => array(
				'lng' => 'bans',
				'link' => $ilCtrl->getLinkTarget( $this->gui, 'ban-show' ),
				'permission' => 'moderate',
				'subtabs' => array(
					'show' => array(
						'lng' => 'bans_table',
						'link' => $ilCtrl->getLinkTarget( $this->gui, 'ban-show' ),
						'permission' => 'moderate',
				),
				/* 'add' => array(
				 'lng' => 'bans_add',
				 'link' => $ilCtrl->getLinkTarget($this->gui, 'ban-add'),
				 'permission' => 'moderate',
				 ) */
				),
				),
			'perm' => array(
				'lng' => 'permissions',
				'link' => $ilCtrl->getLinkTargetByClass( 'ilpermissiongui', 'perm' ),
				'permission' => 'write',
				)
				);

				$commandParts = explode( '_', $command, 2 );

				$this->buildTabs( $ilTabs, $config, $commandParts );

				$this->activateTab( $commandParts, $config );
	}

	/**
	 * Builds tabs and subtabs using given $tabs, $config and $command
	 * parameters.
	 *
	 * @param ilTabsGUI $tabs
	 * @param array $config
	 * @param array $command
	 * @todo: soweit ich das sehe müssen sowohl $config, als auch $command
	 * grundsätzlich arrays sein, d.h. man könnte/sollte hier typehinten
	 * (ilTabsGUI $tabs, array $config, array $command) oder?!
	 */
	private function buildTabs(ilTabsGUI $tabs, $config, $command)
	{
		foreach( $config as $id => $tabDefinition )
		{
			if( !$this->access->checkAccess( $tabDefinition['permission'], '', $this->gui->getRefId() ) ) {
				continue;
			}
			else if (isset($tabDefinition['enabled']) && !$tabDefinition['enabled']) {
				continue;
			}
					
			$tabs->addTab( $id, $this->getLabel( $tabDefinition, $id ), $tabDefinition['link'] );

			if( $command[0] == $id && isset( $tabDefinition['subtabs'] ) &&
			is_array( $tabDefinition['subtabs'] )
			)
			{
				foreach( $tabDefinition['subtabs'] as $subid => $subTabDefinition )
				{
					if( !$this->access->checkAccess( $subTabDefinition['permission'], '', $this->gui->getRefId() ) ) {
						continue;
					}
					else if (isset($subTabDefinition['enabled']) && !$subTabDefinition['enabled']) {
						continue;
					}
						
					$tabs->addSubTab(
					$subid, $this->getLabel( $subTabDefinition, $subid ),
					$subTabDefinition['link']
					);
				}
			}
		}
	}

	/**
	 * Returns label for tab by $tabDefinition or $id
	 *
	 * @param array $tabDefinition
	 * @param string $id
	 * @return string
	 * @todo: $tabDefinition sollte doch stets ein array und $id stets ein
	 * string sein, oder? Dann sollte man auch hier typehinten.
	 * (array $tabDefinition, string $id)
	 */
	private function getLabel($tabDefinition, $id)
	{
		if( isset( $tabDefinition['lng'] ) )
		return $this->lng->txt( $tabDefinition['lng'] );
		else
		return $this->lng->txt( $id );
	}

	/**
	 * Convert a value given in lower camel case conversion to underscore case conversion (e.g. MyClass to my_class)
	 *
	 * @param string $value Value in lower camel case conversion
	 *
	 * @return string The value in underscore case conversion
	 */
	public static function convertLowerCamelCaseToUnderscoreCaseConversion($value)
	{
		//return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $value));
		return strtolower( preg_replace( '/(.*?)-(.*?)/', '$1_$2', $value ) );
	}

	/**
	 * Convert a value given in underscore case conversion to lower camel case conversion (e.g. my_class to MyClass)
	 *
	 * @param string $value Value in underscore case conversion
	 * @param boolean $upper_case_first If TRUE first character in upper case, lower case if FALSE
	 *
	 * @return string The value in lower camel case conversion
	 */
	public static function convertUnderscoreCaseToLowerCamelCaseConversion($value, $upper_case_first = FALSE)
	{
		$tokens = (array)explode( '_', $value );
		$value	= '';

		foreach( $tokens as $token )
		{
			$value .= ucfirst( $token );
		}

		if( $upper_case_first === FALSE )
		{
			$value = strtolower( $value, 0, 1 ) . substr( $value, 1 );
		}

		return $value;
	}

}

?>
