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
* Class ilObjChatServer
* 
* @author Stefan Meyer 
* @version $Id$
*
* @extends ilObject
*/

require_once 'classes/class.ilObjectGUI.php';
require_once 'Modules/Chat/classes/class.ilChatServerConfig.php';
require_once 'Modules/Chat/classes/class.ilChatServerCommunicator.php';
require_once 'Modules/Chat/classes/class.ilChatUser.php';
require_once 'Modules/Chat/classes/class.ilChatRoom.php';

class ilObjChatServer extends ilObject
{
	var $server_conf;
	var $server_comm;
	var $chat_room;
	var $chat_user;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjChatServer($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = 'chac';
		$this->ilObject($a_id,$a_call_by_reference);

		$this->server_conf =& new ilChatServerConfig();
		$this->server_comm =& new ilChatServerCommunicator($this);
		$this->chat_user =& new ilChatUser();
		$this->chat_room =& new ilChatRoom($this->getId());
	}

	function read()
	{
		// USED ilObjectFactory::getInstance...
		parent::read();

		$this->server_conf =& new ilChatServerConfig();
		$this->server_comm =& new ilChatServerCommunicator($this);
		$this->chat_user =& new ilChatUser();
		$this->chat_room =& new ilChatRoom($this->getId());
	}
} // END class.ilObjChatServer
?>