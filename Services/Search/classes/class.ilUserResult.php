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
* search
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version Id$
* 
* @package ilias-search
*/

class ilUserResult
{
	// OBJECT VARIABLES
	var $ilias;

	var $title;
	var $obj_id;
	var $user_id;
	var $target;

	/**
	* Constructor
	* @access	public
	*/
	function ilUserResult($a_user_id,$a_obj_id = 0)
	{
		global $ilias;

		define("TABLE_SEARCH_DATA","search_data");

		$this->ilias =& $ilias;

		$this->obj_id = $a_obj_id;
		$this->user_id = $a_user_id;

		$this->__init();
	}

	// SET/GET
	function getUserId()
	{
		return $this->user_id;
	}

	function getType()
	{
		return "sea";
	}
	function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
	}
	function getObjId()
	{
		return $this->obj_id;
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setTarget($a_target)
	{
		$this->target = $a_target;
	}
	function getTarget()
	{
		return $this->target;
	}

	function createLink()
	{
		$target = $this->getTarget();

		include_once 'Services/Search/classes/class.ilSearchObjectListFactory.php';
		
		$item_list_gui =& ilSearchObjectListFactory::_getInstance($target['type']);
		$item_list_gui->initItem($target['id'],ilObject::_lookupObjId($target['id']));

		return array($item_list_gui->getCommandLink('view'),
					 $item_list_gui->getCommandFrame('view'));
	}

	function updateTitle($a_title)
	{
		$query = "UPDATE ".TABLE_SEARCH_DATA." ".
			"SET title = '".addslashes($a_title)."' ".
			"WHERE obj_id = '".$this->getObjId()."' ".
			"AND user_id = '".$this->getUserId()."'";

		$res = $this->ilias->db->query($query);

		return true;
	}
		
		
	// PRIVATE METHODS
	function __init()
	{
		if($this->getObjId())
		{
			$query = "SELECT * FROM ".TABLE_SEARCH_DATA." ".
				"WHERE obj_id = '".$this->getObjId()."' ".
				"AND user_id = '".$this->getUserId()."'";

			$res = $this->ilias->db->query($query);
			while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
			{
				$this->setTitle($row->title);
				$this->setTarget(unserialize(stripslashes($row->target)));
			}
			return true;
		}
	}
} // END class.Search
?>