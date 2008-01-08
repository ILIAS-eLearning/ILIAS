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
	
	function _countNewInvitations($a_user_id)
	{
		global $ilDB, $ilias;

		if (!$a_user_id)
		{
			return 0;
		}

		$q = "SELECT count(*) as invitations FROM chat_invitations WHERE guest_id = ".$ilDB->quote($a_user_id)." ".
			 "AND guest_informed = 0 ".
			 "AND invitation_time > ".(time() - 2 * 60 * 60)." ";
		$row = $ilias->db->getRow($q,MDB2_FETCHMODE_OBJECT);
		
		return $row->invitations;
	}
} // END class.ilChatInvitations
?>