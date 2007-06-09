<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* Navigation History of Repository Items
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilNavigationHistory
{

	private $items;

	/**
	* Constructor.
	*
	* @param	int	$a_id	
	*/
	public function __construct()
	{
		if (is_string($_SESSION["il_nav_history"]))
		{
			$items = unserialize($_SESSION["il_nav_history"]);
		}
		if (is_array($items))
		{
			$this->items = $items;
		}
		else
		{
			$this->items = array();
		}
	}

	/**
	* Add an item to the stack. If ref_id is already used,
	* the item is moved to the top.
	*/
	public function addItem($a_ref_id, $a_link, $a_type, $a_title = "")
	{
		if ($a_title == "" && $a_ref_id > 0)
		{
			$obj_id = ilObject::_lookupObjId($a_ref_id);
			if (ilObject::_exists($obj_id))
			{
				$a_title = ilObject::_lookupTitle($obj_id);
			}
		}
		
		// remove ref id from stack, if existing
		foreach($this->items as $key => $item)
		{
			if ($item["ref_id"] == $a_ref_id)
			{
				array_splice($this->items, $key, 1);
				break;
			}
		}
		
		$this->items = array_merge(
			array(array("ref_id" => $a_ref_id, "link" => $a_link, "title" => $a_title,
			"type" => $a_type)), $this->items);
		
		$items  = serialize($this->items);
		$_SESSION["il_nav_history"] = $items;
	}
	
	/**
	* Get navigation item stack.
	*/
	public function getItems()
	{
		global $tree;
		
		$items = array();
		
		foreach ($this->items as $it)
		{
			if ($tree->isInTree($it["ref_id"]))
			{
				$items[] = $it;
			}
		}
		
		return $items;
	}
}
?>
