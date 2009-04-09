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

/**
* Class ilChatServerCommunicator
* 
* @author Stefan Meyer 
* @version $Id$
*
*/

class ilChatServerCommunicator
{
	var $chat;

	var $message;
	var $socket_p;
	var $type;
	var $rcp_id;
	var $rcp_login;
	var $kicked_user;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct(&$chat_obj)
	{
		define(TIMEOUT,2);

		$this->chat =& $chat_obj;
	}

	// SET/GET
	public function setRecipientId($a_id)
	{
		$this->rcp_id = $a_id;
		
		if($this->rcp_id)
		{
			$tmp_user =& new ilObjUser($this->rcp_id);
			$this->setRecipientLogin($tmp_user->getLogin());
			unset($tmp_user);
		}
	}
	
	public function getRecipientId()
	{
		return $this->rcp_id;
	}
	
	public function setRecipientLogin($a_login)
	{
		$this->rcp_login = $a_login;
	}
	
	public function getRecipientLogin()
	{
		return $this->rcp_login;
	}
	
	public function setKickedUser($k_user)
	{
		$this->kicked_user = $k_user;
	}
	
	public function getKickedUser()
	{
		return $this->kicked_user;
	}
	
	public function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	
	public function getMessage()
	{
		return $this->message;
	}
	
	public function setType($a_type)
	{
		$this->type = $a_type;
	}
	
	public function getType()
	{
		return $this->type ? $this->type : 'normal';
	}
	
	public function getHtml()
	{
		return $this->html;
	}

	public function send(&$id = null)
	{
		if(!$this->openSocket())
		{
			return false;
		}
		fputs($this->socket_p,$this->formatMessage($id));
		fclose($this->socket_p);
		return true;
	}
	
	public function getServerFrameSource()
	{		
		return sprintf("http".($this->chat->server_conf->getSSLStatus() && $this->chat->server_conf->getSSLPort() ? "s" : "")."://%s:%s/%s%s",
                       $this->chat->server_conf->getExternalIp(),
					   ($this->chat->server_conf->getSSLStatus() && $this->chat->server_conf->getSSLPort() ? $this->chat->server_conf->getSSLPort() : $this->chat->server_conf->getPort()),
					   $this->formatLogin($this->chat->chat_user->getLogin()),
					   $this->getFormattedChatroom());
	}

	public function isAlive()
	{
		$this->setType("test");
		return $this->send();
	}

	private function getFormattedChatroom()
	{
		$nic = $this->chat->server_conf->getNic();

		return $nic.$this->chat->chat_room->getInternalName().
			substr("______________",0,14-strlen($this->chat->chat_room->getInternalName()));
	}
	
	private function formatLogin($a_login)
	{
		$nic = $this->chat->server_conf->getNic();

		return substr($nic.md5($a_login),0,32);
	}
	
	private function formatMessage(&$id = null)
	{
		global $ilSetting;
		if ((int)$ilSetting->get('chat_smilies_status') == 1)
			$this->emoticons();
		switch($this->getType())
		{
			case 'private':
				// STORE MESSAGE IN DB
				return "|".$this->formatLogin($this->getRecipientLogin()).
					$this->formatLogin($this->chat->chat_user->getLogin()).
					$this->getFormattedChatroom().$this->getMessage()."<br />";
			
			case 'address':
				// STORE MESSAGE IN DB
				$id = $this->chat->chat_room->appendMessageToDb($this->getMessage());
				return ">".$this->getFormattedChatroom().$this->getMessage()."<br />";

			case 'normal':
				// STORE MESSAGE IN DB
				$id = $this->chat->chat_room->appendMessageToDb($this->getMessage());
				return ">".$this->getFormattedChatroom().$this->getMessage()."<br />";

			case 'login':
				return "!".$this->formatLogin($this->chat->chat_user->getLogin()).$_SERVER["REMOTE_ADDR"];

			case "logout":
				return "-".$this->formatLogin($this->chat->chat_user->getLogin());

			case "kick":
				return "GET /moderate?".$this->chat->server_conf->getModeratorPassword().
					"&kick&".$this->formatLogin($this->getKickedUser()).
					"&".$this->getFormattedChatroom();

			case "delete":
				return "GET /moderate?".$this->chat->server_conf->getModeratorPassword().
					"&delete&".$this->getFormattedChatroom();

			case "empty":
				return "GET /moderate?".$this->chat->server_conf->getModeratorPassword().
					"&clear&".$this->getFormattedChatroom();

			case "test":
				return "GET /Version";

			default:
				return "GET /Version";

		}
	}

	private function emoticons()
	{
		global $ilSetting;
		
		if ($ilSetting->get('chat_smilies_status') == 1) {
			include_once 'Modules/Chat/classes/class.ilChatSmilies.php';
			$str = ilChatSmilies::_parseString($this->getMessage());
			$this->setMessage($str);
		}
	}

	private function openSocket()
	{
        $this->socket_p = @fsockopen($this->chat->server_conf->getInternalIp(), 
									 $this->chat->server_conf->getPort(), $errno, $errstr, TIMEOUT);

		return $this->socket_p == null ? false : true;
	}

	// STATIC
	static function _initObject()
	{
		global $ilias, $ilDB;
		
		$res = $ilDB->queryF('
			SELECT ref_id FROM object_data 
			NATURAL JOIN object_reference
			WHERE type = %s',
			array('text'),
			array('chac')
		);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$ref_id = $row->ref_id;
		}
		
		// GET INSTANCE
		return new ilChatServerCommunicator($tmp =& ilObjectFactory::getInstanceByRefId($ref_id));
	}		

	static function _login()
	{
		$obj =& ilChatServerCommunicator::_initObject();

		// CALLED BY login.php
		$obj->setType("login");
		$obj->chat->chat_user->setUserId($_SESSION["AccountId"]);
		$obj->send();
	}

	static function _logout()
	{
		$obj =& ilChatServerCommunicator::_initObject();

		// CALLED BY login.php
		$obj->setType("logout");
		$obj->chat->chat_user->setUserId($_SESSION["AccountId"]);
		$obj->send();
	}
		
	static function _lookupUser($usr_id) {
		global $ilDB, $ilObjDataCache;
		
		$row = $ilDB->queryF('
			SELECT room_id, chat_id, kicked  FROM chat_user 
			WHERE
				usr_id = %s AND
				last_conn_timestamp > %s',
			array('integer', 'integer'),
			array($usr_id, time() - 60));
		 
		$found =  $row->numRows() ? true : false;

		if (!$found)
			return false;
		$line = $row->fetchRow();
		
	
		$res = new stdClass();
		$res->chatId = $line[1];
		$res->roomId = $line[0];
		$res->kicked = $line[2];

		$room_title = '';
		
		if((int)$res->roomId)
		{
			include_once 'Modules/Chat/classes/class.ilChatRoom.php';
			$oTmpChatRoom = new ilChatRoom((int)$res->chatId);
			$oTmpChatRoom->setRoomId((int)$res->roomId);
			$room_title = $oTmpChatRoom->getTitle();
			if($room_title != '')
				$room_title = " (" . $room_title . ")";
		}
		$res->chatTitle = $ilObjDataCache->lookupTitle($res->chatId) . $room_title;
		
		return $res;
	}
	
	static function _getTailMessages($chat_id, $room_id, $start_date = 0) {
		global $ilDB;
		
		$ilDB->setLimit(1, 0);
		$row = $ilDB->queryF('
			SELECT entry_id, message
			FROM chat_room_messages 
			WHERE
				chat_id = %s
				AND room_id = %s
				AND commit_timestamp > %s 
			ORDER BY
				commit_timestamp DESC',
			array('integer', 'integer', 'integer'),
			array($chat_id, $room_id, $start_date)
		);
		
		if ($row->numRows()) {
			$line = $row->fetchRow();
			$res = new stdClass();
			$res->entryId = $line[0];
			$res->message = $line[1];
			return $res;
		}
		else
			return false;
	}

} // END class.ilChatServerCommunicator
?>
