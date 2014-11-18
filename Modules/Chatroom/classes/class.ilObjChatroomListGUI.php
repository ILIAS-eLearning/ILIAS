<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
 * Class ilObjChatlistListGUI
 *
 * @author   Jan Posselt <jposselt at databay.de>
 * @version  $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilObjChatroomListGUI extends ilObjectListGUI
{

	private static $publicRoomObjId;

	/**
	 * Constructor
	 *
	 * Calls $this->ilObjectListGUI method.
	 */
	public function __construct()
	{
		$this->ilObjectListGUI();

		require_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';

		self::$publicRoomObjId = ilObjChatroom::_getPublicObjId();
	}

	/**
	 * Initialisation
	 */
	public function init()
	{
		$this->delete_enabled		= true;
		$this->cut_enabled			= true;
		$this->copy_enabled			= true;
		$this->subscribe_enabled	= true;
		$this->link_enabled			= true;
		$this->payment_enabled		= true;
		$this->info_screen_enabled	= true;
		$this->type					= "chtr";
		$this->gui_class_name		= "ilobjchatroomgui";

		// general commands array
		include_once('./Modules/Chatroom/classes/class.ilObjChatroomAccess.php');
		$this->commands = ilObjChatroomAccess::_getCommands();
	}

	private static $chat_enabled = null;

	/**
	 * Get item properties
	 *
	 * @return	array		array of property arrays:
	 * 						"alert" (boolean) => display as an alert property (usually in red)
	 * 						"property" (string) => property name
	 * 						"value" (string) => property value
	 */
	public function getProperties()
	{
		/**
		 * @var $lng ilLanguage
		 */
		global $lng;
		
		$props = array();

		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		$room = ilChatroom::byObjectId($this->obj_id);
		if($room)
		{
			$props[] = array(
				"alert" => false, "property" => $lng->txt("chat_users_active"),
				"value" => $room->countActiveUsers()
			);

			if($this->obj_id == self::$publicRoomObjId)
			{
				$props[] = array("alert" => false, "property" => $lng->txt("notice"), 'value' => $lng->txt('public_room'));
			}

			if(self::$chat_enabled === null)
			{
				$chatSetting        = new ilSetting('chatroom');
				self::$chat_enabled = (boolean)$chatSetting->get('chat_enabled');
			}

			if(!self::$chat_enabled)
			{
				$props[] = array("alert" => true, "property" => $lng->txt("status"), 'value' => $lng->txt("server_disabled"));
			}
		}

		return $props;
	}

	/**
	 * Get command link url.
	 *
	 * @param	int			$a_ref_id		reference id
	 * @param	string		$a_cmd			command
	 *
	 */
	/*
	 function getCommandLink($a_cmd)
	 {
	 // separate method for this line
	 $cmd_link = "repo.php?ref_id=".$this->ref_id."&cmd=$a_cmd";

	 return $cmd_link;
	 } */

	/**
	 * Returns command icon image.
	 *
	 * @param string $a_cmd
	 * @return string
	 */
	public function getCommandImage($a_cmd)
	{
		switch ($a_cmd)
		{
			default:
				return "";
		}
	}

}

?>
