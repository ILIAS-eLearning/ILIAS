<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* Chat presentation GUI handler
* 
* @version $Id$
*
* @ilCtrl_Calls ilChatPresentationGUI: ilObjChatGUI
*/
class ilChatPresentationGUI
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id
	*/
	function __construct()
	{
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $lng, $ilAccess, $tpl, $ilNavigationHistory, $ilCtrl;
		
//echo "*".$ilCtrl->getCmd()."*";
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		if ($next_class == "")
		{
			$ilCtrl->setCmdClass("ilobjchatgui");
			if($cmd == '') $ilCtrl->setCmd("showFrames");
			$next_class = $ilCtrl->getNextClass($this);
		}
		
		if(strpos(trim(ilUtil::stripSlashes($_POST['invitation'])), '_') !== false)
		{
			$param = trim(ilUtil::stripSlashes($_POST['invitation']));
			
			$_GET['ref_id'] = (int)substr($param, 0, strpos($param, '_'));
			$_REQUEST['room_id'] = (int)substr($param, strrpos($param, '_') + 1); 
		}		

		// add entry to navigation history
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"ilias.php?baseClass=ilChatPresentationGUI&cmd=infoScreen&ref_id=".$_GET["ref_id"], "chat");
		}

		switch ($next_class)
		{
			case 'ilobjchatgui':
				include_once("./Modules/Chat/classes/class.ilObjChatGUI.php");
				$chat_gui = new ilObjChatGUI(array(), $_GET["ref_id"], true, false);
				$chat_gui->object->chat_room->setRoomId((int) $_REQUEST["room_id"]);
				$chat_gui->object->chat_room->setUserId($_SESSION["AccountId"]);
				
				// check room access
				if(!$chat_gui->object->chat_room->checkAccess())
				{
					unset($_REQUEST["room_id"]);
					unset($_REQUEST["message"]);
					ilUtil::sendInfo("You are not entitled to view this room",true);
				}
				
				// check server accessibility
				if(!$chat_gui->object->server_conf->getActiveStatus())
				{
					$tmp_tpl = new ilTemplate('tpl.chat_srv_off_redirect_js.html', true, true, 'Modules/Chat');
					$tmp_tpl->setVariable('OPENER_LOCATION', 'goto.php?target=chat_'.(int)$_GET['ref_id'].'&client_id='.CLIENT_ID);
					echo $tmp_tpl->get();
					exit();
				}
						
				$chat_gui->object->server_comm->setRecipientId((int) $_GET["p_id"]);
				$ret = $ilCtrl->forwardCommand($chat_gui);
				break;
		}

		$tpl->show();
	}

	// PRIVATE
/*
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
*/

}
?>
