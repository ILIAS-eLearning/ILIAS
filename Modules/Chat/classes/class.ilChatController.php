<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once "Modules/Chat/classes/class.ilObjChatGUI.php";

/**
* Class ilObjTest
* 
* @author Stefan Meyer 
* @version $Id:class.ilChatController.php 12853 2006-12-15 13:36:31 +0000 (Fr, 15 Dez 2006) smeyer $
*
*/
class ilChatController
{
	var $gui_obj;
	
	var $ref_id;
	var $cmd;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id
	*/
	function ilChatController($a_ref_id)
	{

		$this->ref_id = (int) $a_ref_id;
		$this->gui_obj =& new ilObjChatGUI(array(),$a_ref_id,true,false);
		$this->gui_obj->object->chat_room->setRoomId((int) $_REQUEST["room_id"]);
		$this->gui_obj->object->chat_room->setUserId($_SESSION["AccountId"]);
		
		// CHECK HACK
		if(!$this->gui_obj->object->chat_room->checkAccess())
		{
			unset($_REQUEST["room_id"]);
			unset($_REQUEST["message"]);
			ilUtil::sendInfo("You are not entitled to view this room",true);
		}
		$this->gui_obj->object->server_comm->setRecipientId((int) $_GET["p_id"]);
		$this->__getCommand();
	}

	// SET/GET
	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	function getRefId()
	{
		return $this->ref_id;
	}

	function execute()
	{
		$cmd = $this->cmd;
		$this->gui_obj->$cmd();
	}

	// PRIVATE
	function __getCommand()
	{
		if($_GET["cmd"] == 'gateway')
		{
			if(is_array($_POST["cmd"]))
			{
				$_GET["cmd"] = key($_POST["cmd"]);
			}
			// Workaround for Internet Explorer (IE). If a user presses
			// the Enter in the message input field, IE does not send a
			// "cmd" parameter. We fill in the command "input", because
			// we can safely assume that the user intended to post the 
			// message.
			else if (! $_POST["cmd"] && $_POST["message"])
			{
				$_GET["cmd"] = 'input';
			}
			else
			{
				$_GET["cmd"] = 'cancel';
			}
		}

		if($_GET["cmd"])
		{
			$this->cmd = $_GET["cmd"];
		}
		else
		{
			$this->cmd = "showFrames";
		}
		
		if($_GET['vcard'] == 1 && (int)$_GET['user'])
		{
			$this->cmd = 'deliverVCard';
		}
	}
} // END class.ilObjTest
?>
