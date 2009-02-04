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
* Class ilChatInvitations
* 
* @author Sascha Hofmann 
* @version $Id$
*
*/

class ilChatInvitations
{
	/**
	* Constructor
	*/
	function ilChatInvitations()
	{
		return;
	}
	
	public static function _countNewInvitations($a_user_id)
	{
		global $ilDB, $ilias;

		if (!$a_user_id)
		{
			return 0;
		}

		$statement = $ilias->db->prepare('
			SELECT COUNT(*) invitations FROM chat_invitations
			WHERE guest_id = ?
			AND guest_informed = ?
			AND invitation_time > ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($a_user_id, '0', time() - 2 * 60 * 60);
		$res = $ilias->db->execute($statement, $data);
		
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		
		return $row->invitations;
	}
	
	public static function _getNewInvitations($a_user_id)
	{
		global $ilDB, $ilias;

		if(!(int)$a_user_id)
		{
			return array();
		}

		$statement = $ilDB->prepare('
			SELECT * FROM chat_invitations
			WHERE guest_id = ?
			AND guest_informed = ?
			AND invitation_time > ?',
			array('integer', 'integer', 'integer')
		);

		$data = array($a_user_id, '0', time() - 2 * 60 * 60);
		$res = $ilDB->execute($statement, $data);
		
		$rows = array();
		
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$rows[] = $row;
		}
		
		return is_array($rows) ? $rows : array();
	}
} // END class.ilChatInvitations
?>