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
* @package ilias-core
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
	* check if group name exists
	* @access	public
	* @param	string group name
	*/
	function groupNameExists($a_group_name)
	{
		$query = "SELECT obj_id FROM object_data ".
			"WHERE title = '".$a_group_name ."' ".
			"AND type = 'grp'";

		$row = $this->ilias->db->getRow($query,DB_FETCHMODE_OBJECT);
		return $row->obj_id ? $row->obj_id : 0;
	}
	/*
	* get the user_ids which correspond a search string 
	* @param	string search string
	* @access	public
	*/
	function searchGroups($a_search_str)
	{
		$query = "SELECT * ".
			"FROM object_data ,object_reference ".
			"WHERE (object_data.title LIKE '%".$a_search_str."%' ".
			"OR object_data.description LIKE '%".$a_search_str."%') ".
			"AND object_data.type = 'grp' ".
			"AND object_data.obj_id = object_reference.obj_id";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// STORE DATA IN ARRAY WITH KEY obj_id
			// SO DUPLICATE ENTRIES ( LINKED OBJECTS ) ARE UNIQUE
			$ids[$row->obj_id] = array(
				"ref_id"        => $row->ref_id,
				"title"         => $row->title,
				"description"   => $row->description);
		}
		return $ids ? $ids : array();
	}
}
?>
