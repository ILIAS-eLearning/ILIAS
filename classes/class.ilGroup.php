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
* group class for ilias
* TODO: this class is only used for mail functionality (class.ilmail.php) so far!!
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
*/
class ilGroup
{
	/**
	* ilias object
	* @var object ilias
	* @access private
	*/	
	var $ilias;

	/**
	* group_id
	* @var int group_id
	* @access private
	*/	
	var $group_id;
	
	/**
	* Constructor
	* @access	public
	* @param	integer group_id
	*/
	function ilGroup($a_group_id = 0)
	{
		global $ilias;
		
		// init variables
		$this->ilias = &$ilias;
		
		$this->group_id = $a_group_id;
	}
	
	/**
	* checks if group name already exists. Groupnames must be unique for mailing purposes
	* static function; move to better place (ilObjGroup or ilUtil)
	* @access	public
	* @param	string	groupname
	* @param	integer	obj_id of group to exclude from the check. 
	* @return	boolean	true if exists
	* @static
	*/
	function _groupNameExists($a_group_name,$a_id = 0)
	{
		global $ilDB,$ilErr;
		
		if (empty($a_group_name))
		{
			$message = get_class($this)."::groupNameExists(): No groupname given!";
			$ilErr->raiseError($message,$ilErr->WARNING);
		}

		$clause = ($a_id) ? " AND obj_id != '".$a_id."'" : "";

		$q = "SELECT obj_id FROM object_data ".
			 "WHERE title = ".$ilDB->quote($a_group_name)." ".
			 "AND type = 'grp'".
			 $clause;
		$r = $ilDB->query($q);

		if ($r->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}
?>
