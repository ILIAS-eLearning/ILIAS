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
* Class ilSearchItemFactory
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias-search
*/
class ilSearchItemFactory
{
		
	/**
	* get an instance of an Ilias object by object id
	*
	* @param	int		$obj_id		object id
	* @return	object	instance of Ilias object (i.e. derived from ilObject)
	*/
	function &getInstance($a_obj_id,$a_user_id = '')
	{
		global $ilias;

		define("TABLE_SEARCH_DATA","search_data");

		if(!$a_user_id)
		{
			$user_id = $_SESSION["AccountId"];
		}

		$query = "SELECT type FROM ".TABLE_SEARCH_DATA." ".
			"WHERE obj_id = '".$a_obj_id."'";

		$res = $this->ilias->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$type = $row->type;
		}
		
		switch($type)
		{
			case "seaf":

				include_once "Services/Search/classes/class.ilSearchFolder.php";

				return new ilSearchFolder($user_id,$a_obj_id);
				
			case "sea":

				include_once "Services/Search/classes/class.ilUserResult.php";

				return new ilUserResult($user_id,$a_obj_id);
		}
	}
}
?>
