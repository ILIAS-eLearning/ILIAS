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
	function ilChatServerCommunicator(&$chat_obj)
	{
		define(TIMEOUT,2);

		$this->chat =& $chat_obj;
	}

	// SET/GET
	function setRecipientId($a_id)
	{
		$this->rcp_id = $a_id;
		
		if($this->rcp_id)
		{
			$tmp_user =& new ilObjUser($this->rcp_id);
			$this->setRecipientLogin($tmp_user->getLogin());
			unset($tmp_user);
		}
	}
	function getRecipientId()
	{
		return $this->rcp_id;
	}
	function setRecipientLogin($a_login)
	{
		$this->rcp_login = $a_login;
	}
	function getRecipientLogin()
	{
		return $this->rcp_login;
	}
	function setKickedUser($k_user)
	{
		$this->kicked_user = $k_user;
	}
	function getKickedUser()
	{
		return $this->kicked_user;
	}
	function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	function getMessage()
	{
		return $this->message;
	}
	function setType($a_type)
	{
		$this->type = $a_type;
	}
	function getType()
	{
		return $this->type ? $this->type : 'normal';
	}
	function getHtml()
	{
		return $this->html;
	}

	function send()
	{
		if(!$this->__openSocket())
		{
			return false;
		}
		fputs($this->socket_p,$this->__formatMessage());
		fclose($this->socket_p);

		return true;
	}
	
	function getServerFrameSource()
	{		
		return sprintf("http".($this->chat->server_conf->getSSLStatus() && $this->chat->server_conf->getSSLPort() ? "s" : "")."://%s:%s/%s%s",
                       $this->chat->server_conf->getExternalIp(),
					   ($this->chat->server_conf->getSSLStatus() && $this->chat->server_conf->getSSLPort() ? $this->chat->server_conf->getSSLPort() : $this->chat->server_conf->getPort()),
					   $this->__formatLogin($this->chat->chat_user->getLogin()),
					   $this->__getFormattedChatroom());
	}

	function isAlive()
	{
		$this->setType("test");
		return $this->send();
	}

	// PRIVATE
	function __getFormattedChatroom()
	{
		$nic = $this->chat->server_conf->getNic();

		return $nic.$this->chat->chat_room->getInternalName().
			substr("______________",0,14-strlen($this->chat->chat_room->getInternalName()));
	}
	function __formatLogin($a_login)
	{
		$nic = $this->chat->server_conf->getNic();

		return substr($nic.md5($a_login),0,32);
	}
	function __formatMessage()
	{
		$this->__emoticons();

		switch($this->getType())
		{
			case 'private':
				// STORE MESSAGE IN DB
				return "|".$this->__formatLogin($this->getRecipientLogin()).
					$this->__formatLogin($this->chat->chat_user->getLogin()).
					$this->__getFormattedChatroom().$this->getMessage()."<br />";
			
			case 'address':
				// STORE MESSAGE IN DB
				$this->chat->chat_room->appendMessageToDb($this->getMessage());
				return ">".$this->__getFormattedChatroom().$this->getMessage()."<br />";

			case 'normal':
				// STORE MESSAGE IN DB
				$this->chat->chat_room->appendMessageToDb($this->getMessage());
				return ">".$this->__getFormattedChatroom().$this->getMessage()."<br />";

			case 'login':
				return "!".$this->__formatLogin($this->chat->chat_user->getLogin()).$_SERVER["REMOTE_ADDR"];

			case "logout":
				return "-".$this->__formatLogin($this->chat->chat_user->getLogin());

			case "kick":
				return "GET /moderate?".$this->chat->server_conf->getModeratorPassword().
					"&kick&".$this->__formatLogin($this->getKickedUser()).
					"&".$this->__getFormattedChatroom();

			case "delete":
				return "GET /moderate?".$this->chat->server_conf->getModeratorPassword().
					"&delete&".$this->__getFormattedChatroom();

			case "empty":
				return "GET /moderate?".$this->chat->server_conf->getModeratorPassword().
					"&clear&".$this->__getFormattedChatroom();

			case "test":
				return "GET /Version";

			default:
				return "GET /Version";

		}
	}

	function __emoticons()
	{
		$str = $this->getMessage();
		$str = str_replace(":)", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_smile.gif\" border=0>", $str);
		$str = str_replace(":-)", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_smile.gif\" border=0>", $str);
		$str = str_replace(":smile:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_smile.gif\" border=0>", $str);
		$str = str_replace(";)", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_wink.gif\" border=0>", $str);
		$str = str_replace(";-)", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_wink.gif\" border=0>", $str);
		$str = str_replace(":wink:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_wink.gif\" border=0>", $str);
		$str = str_replace(":D", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_laugh.gif\" border=0>", $str);
		$str = str_replace(":-D", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_laugh.gif\" border=0>", $str);
		$str = str_replace(":laugh:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_laugh.gif\" border=0>", $str);
		$str = str_replace(":grin:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_laugh.gif\" border=0>", $str);
		$str = str_replace(":biggrin:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_laugh.gif\" border=0>", $str);
		$str = str_replace(":(", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_sad.gif\" border=0>", $str);
		$str = str_replace(":-(", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_sad.gif\" border=0>", $str);
		$str = str_replace(":sad:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_sad.gif\" border=0>", $str);
		$str = str_replace(":o", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_shocked.gif\" border=0>", $str);
		$str = str_replace(":-o", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_shocked.gif\" border=0>", $str);
		$str = str_replace(":shocked:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_smile.gif\" border=0>", $str);
		$str = str_replace(":p", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_tongue.gif\" border=0>", $str);
		$str = str_replace(":-p", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_tongue.gif\" border=0>", $str);
		$str = str_replace(":tongue:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_smile.gif\" border=0>", $str);
		$str = str_replace(":cool:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_cool.gif\" border=0>", $str);
		$str = str_replace(":eek:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_eek.gif\" border=0>", $str);
		$str = str_replace(":||", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_angry.gif\" border=0>", $str);
		$str = str_replace(":-||", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_angry.gif\" border=0>", $str);
		$str = str_replace(":angry:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_angry.gif\" border=0>", $str);
		$str = str_replace(":flush:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_flush.gif\" border=0>", $str);
		$str = str_replace(":idea:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_idea.gif\" border=0>", $str);
		$str = str_replace(":thumbup:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_thumbup.gif\" border=0>", $str);
		$str = str_replace(":thumbdown:", "<img src=\"" . ILIAS_HTTP_PATH . 
						   "/templates/default/images/emoticons/icon_thumbdown.gif\" border=0>", $str);
		$this->setMessage($str);
	}

	function __openSocket()
	{
        $this->socket_p = @fsockopen($this->chat->server_conf->getInternalIp(), 
									 $this->chat->server_conf->getPort(), $errno, $errstr, TIMEOUT);

		return $this->socket_p == null ? false : true;
	}

	// STATIC
	function _initObject()
	{
		global $ilias;
		
		$query = "SELECT ref_id FROM object_data NATURAL JOIN object_reference ".
			"WHERE type = 'chac'";
		
		$res = $ilias->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$ref_id = $row->ref_id;
		}
		
		// GET INSTANCE
		return new ilChatServerCommunicator($tmp =& ilObjectFactory::getInstanceByRefId($ref_id));
	}		

	function _login()
	{
		$obj =& ilChatServerCommunicator::_initObject();

		// CALLED BY login.php
		$obj->setType("login");
		$obj->chat->chat_user->setUserId($_SESSION["AccountId"]);
		$obj->send();
	}

	function _logout()
	{
		$obj =& ilChatServerCommunicator::_initObject();

		// CALLED BY login.php
		$obj->setType("logout");
		$obj->chat->chat_user->setUserId($_SESSION["AccountId"]);
		$obj->send();
	}
		

} // END class.ilChatServerCommunicator
?>
