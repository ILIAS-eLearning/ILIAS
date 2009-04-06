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
* Class ilChatUser
* 
* @author Stefan Meyer 
* @version $Id$
*
*/

class ilChatUser
{
	var $ilias;
	var $lng;
	var $user;

	var $u_id;
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilChatUser($a_id = 0)
	{
		global $ilias,$lng;

		$this->ilias =& $ilias;
		$this->lng =& $lng;

		$this->u_id = $a_id;
	}

	// SET/GET
	function setUserId($a_id)
	{
		$this->u_id = $a_id;
		$this->__initUserObject();
	}
	function getUserId()
	{
		return $this->u_id;
	}
	
	function getLogin()
	{
		return $this->user->getLogin();
	}

	// PRIVATE
	function __initUserObject()
	{
		$this->user =& new ilObjUser($this->getUserId());
	}

} // END class.ilChatUser
?>