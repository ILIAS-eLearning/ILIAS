<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


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
		$this->items = array();
		$items = null;
		if (isset($_SESSION["il_nav_history"]))
		{
			$items = unserialize($_SESSION["il_nav_history"]);
		}
		if (is_array($items))
		{
			$this->items = $items;
		}
	}

	/**
	* Add an item to the stack. If ref_id is already used,
	* the item is moved to the top.
	*/
	public function addItem($a_ref_id, $a_link, $a_type, $a_title = "", $a_sub_obj_id = "",
		$a_goto_link = "")
	{
		global $ilUser, $ilDB;
		
		$a_sub_obj_id = $a_sub_obj_id."";
		
		if ($a_title == "" && $a_ref_id > 0)
		{
			$obj_id = ilObject::_lookupObjId($a_ref_id);
			if (ilObject::_exists($obj_id))
			{
				$a_title = ilObject::_lookupTitle($obj_id);
			}
		}

		$id = $a_ref_id.":".$a_sub_obj_id;

		// remove id from stack, if existing
		foreach($this->items as $key => $item)
		{
			if ($item["id"] == $id)
			{
				array_splice($this->items, $key, 1);
				break;
			}
		}
		// same in db
		$ilDB->manipulate($q = "DELETE FROM last_visited WHERE ".
			" user_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" AND ref_id = ".$ilDB->quote($a_ref_id, "integer").
			" AND ".$ilDB->equals("sub_obj_id", $a_sub_obj_id, "text", true)
			);
		
		// put items in session
		$this->items = array_merge(
			array(array("id" => $id,"ref_id" => $a_ref_id, "link" => $a_link, "title" => $a_title,
			"type" => $a_type)), $this->items);
		$items  = serialize($this->items);
		$_SESSION["il_nav_history"] = $items;
		
		// and into database
		$db_entries[] = array("user_id" => $ilUser->getId(), "nr" => 1,
			"ref_id" => $a_ref_id, "type" => $a_type, "sub_obj_id" => $a_sub_obj,
			"goto_link" => $a_goto_link);
		$set = $ilDB->query("SELECT * FROM last_visited ".
			" WHERE user_id = ".$ilDB->quote($ilUser->getId(), "integer").
			" ORDER BY nr ASC"
			);
		$cnt = 1;
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$cnt++;
			$rec["nr"] = $cnt;
			$db_entries[] = $rec;
		}
		// same in db
		$ilDB->manipulate("DELETE FROM last_visited WHERE ".
			" user_id = ".$ilDB->quote($ilUser->getId(), "integer")
			);
		foreach ($db_entries as $e)
		{
			if ($e["nr"] <= 10)
			{
				$ilDB->manipulate("INSERT INTO last_visited ".
					"(user_id, nr, ref_id, type, sub_obj_id, goto_link) VALUES (".
					$ilDB->quote($ilUser->getId(), "integer").",".
					$ilDB->quote($e["nr"], "integer").",".
					$ilDB->quote($e["ref_id"], "integer").",".
					$ilDB->quote($e["type"], "text").",".
					$ilDB->quote($e["sub_obj_id"], "text").",".
					$ilDB->quote($e["goto_link"], "text").
					")");
			}
		}
	}
	
	/**
	* Get navigation item stack.
	*/
	public function getItems()
	{
		global $tree, $ilDB, $ilUser;
		
		$items = array();
		
		foreach ($this->items as $it)
		{
			if ($tree->isInTree($it["ref_id"]))
			{
				$items[$it["ref_id"].":".$it["sub_obj_id"]] = $it;
			}
		}
		
		// less than 10? -> get items from db
		if (count($items) < 10)
		{
			$set = $ilDB->query("SELECT * FROM last_visited ".
				" WHERE user_id = ".$ilDB->quote($ilUser->getId(), "integer").
				" ORDER BY nr ASC"
				);
			$cnt = count($items);
			while ($rec = $ilDB->fetchAssoc($set))
			{
				include_once("./classes/class.ilLink.php");
				
				if ($cnt <= 10 && ! isset($items[$rec["ref_id"].":".$rec["sub_obj_id"]]))
				{
					$link = $rec["goto_link"] != ""
						? $rec["goto_link"]
						: ilLink::_getLink($rec["ref_id"]);
					$title = ilObject::_lookupTitle(ilObject::_lookupObjId($rec["ref_id"]));
					$items[] = array("id" => $rec["ref_id"].":".$rec["sub_obj_id"],
						"ref_id" => $rec["ref_id"], "link" => $link, "title" => $title,
						"type" => $rec["type"]);
					$cnt++;
				}
			}
		}
		
		return $items;
	}
}
?>
