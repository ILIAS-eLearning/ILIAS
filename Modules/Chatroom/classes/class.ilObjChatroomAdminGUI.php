<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./classes/class.ilObjectGUI.php";
require_once "./Modules/Chatroom/classes/class.ilObjChatroom.php";
require_once "./Modules/Chatroom/classes/class.ilObjChatroomAccess.php";
require_once 'Modules/Chatroom/lib/DatabayHelper/databayHelperLoader.php';

/**
 * Class ilObjChatroomAdminGUI
 *
 * GUI class for chatroom objects.
 *
 * @author Jan Posselt <jposselt at databay.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjChatroomAdminGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjChatroomAdminGUI: ilExportGUI
 * @ilCtrl_IsCalledBy ilObjChatroomAdminGUI: ilRepositoryGUI, ilAdministrationGUI
 *
 * @ingroup ModulesChatroom
 */
class ilObjChatroomAdminGUI extends ilDBayObjectGUI
{

	/**
	 * Constructor
	 *
	 * @param array $a_data
	 * @param integer $a_id
	 * @param boolean $a_call_by_reference
	 */
	public function __construct($a_data = null, $a_id = null, $a_call_by_reference = true)
	{
		global $lng;

		$lng->loadLanguageModule( 'chatroom_adm' );

		if( $a_data == null )
		{
			if( $_GET['serverInquiry'] )
			{
				require_once dirname( __FILE__ ) . '/class.ilChatroomServerHandler.php';
				new ilChatroomServerHandler();
				return;
			}
		}

		$this->type = 'chta';
		$this->ilObjectGUI( $a_data, $a_id, $a_call_by_reference, false );

	}

	/**
	 * Returns object definition by calling getDefaultDefinitionWithCustomTaskPath
	 * method in ilDBayObjectDefinition.
	 *
	 * @return ilDBayObjectDefinition
	 */
	protected function getObjectDefinition()
	{
		return ilDBayObjectDefinition::getDefaultDefinitionWithCustomTaskPath(
				'Chatroom', 'admintasks'
				);
	}

	/**
	 * Returns empty array.
	 *
	 * @return array
	 */
	public function _forwards()
	{
		return array();
	}

	/**
	 * Dispatches the command to the related executor class.
	 *
	 * @global ilCtrl2 $ilCtrl
	 */
	public function executeCommand()
	{
		//global $ilAccess, $ilNavigationHistory, $ilCtrl, $ilUser, $ilTabs;
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();

		require_once 'Modules/Chatroom/classes/class.ilChatroomTabFactory.php';

		$tabFactory = new ilChatroomTabFactory( $this );
		$tabFactory->getAdminTabsForCommand( $ilCtrl->getCmd() );

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->prepareOutput();
				$perm_gui = & new ilPermissionGUI( $this );
				$ret = & $this->ctrl->forwardCommand( $perm_gui );
				break;

			default:
				$res = split( '-', $ilCtrl->getCmd(), 2 );
				$this->dispatchCall( $res[0], $res[1] ? $res[1] : '' );
		}
	}

	/**
	 * Returns connector.
	 *
	 * @return ilChatroomServerConnector
	 */
	public function getConnector()
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';

		$settings	= ilChatroomServerSettings::loadDefault();
		$connector	= new ilChatroomServerConnector( $settings );

		return $connector;
	}

	/**
	 * Overwrites $_GET['ref_id'] with given $ref_id.
	 *
	 * @global ilCtrl2 $ilCtrl
	 * @param integer $ref_id
	 */
	public static function _goto($ref_id)
	{
		//global $ilCtrl;
		//$ilCtrl->setParameter($this, 'cmd', 'view');

		include_once("./classes/class.ilObjectGUI.php");
		ilObjectGUI::_gotoRepositoryNode($ref_id, "view");
		/*$_GET['cmd'] = 'view';
		$_GET['ref_id'] = $ref_id;
		require 'repository.php';*/
	}

	/**
	 * Returns RefId.
	 *
	 * @return integer
	 */
	public function getRefId()
	{
		return $this->object->getRefId();
	}

}

?>
