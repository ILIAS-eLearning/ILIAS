<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjLinkResourceAccess
 *
 *
 * @author 		Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesWebResource
 */
class ilObjLinkResourceAccess extends ilObjectAccess
{
	static $item = array();
	
	/**
	 * get commands
	 * 
	 * this method returns an array of all possible commands/permission combinations
	 * 
	 * example:	
	 * $commands = array
	 *	(
	 *		array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
	 *		array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
	 *	);
	 */
	function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "", "lang_var" => "show",
				"default" => true),
			array("permission" => "write", "cmd" => "editLinks", "lang_var" => "edit")
		);
		
		return $commands;
	}

	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "webr" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
			$ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get first link item
	 * Check before with _isSingular() if there is more or less than one
	 *
	 * @param	int			$a_webr_id		object id of web resource
	 * @return array link item data
	 *
	 */
	public static function _getFirstLink($a_webr_id)
	{
		global $ilDB;

		if (isset(self::$item[$a_webr_id]))
		{
			return self::$item[$a_webr_id];
		}
		$res = $ilDB->query("SELECT * FROM webr_items WHERE webr_id = ".
			$ilDB->quote($a_webr_id ,'integer')." AND active = '1'");
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$item['title']				= $row->title;
			$item['description']		= $row->description;
			$item['target']				= $row->target;
			$item['active']				= (bool) $row->active;
			$item['disable_check']		= $row->disable_check;
			$item['create_date']		= $row->create_date;
			$item['last_update']		= $row->last_update;
			$item['last_check']			= $row->last_check;
			$item['valid']				= $row->valid;
			$item['link_id']			= $row->link_id;
			self::$item[$row->webr_id] = $item;
		}
		return $item ? $item : array();
	}

	/**
	 * Preload data
	 *
	 * @param array $a_obj_ids array of object ids
	 */
	function _preloadData($a_obj_ids, $a_ref_ids)
	{
		global $ilDB, $ilUser;
		
		$res = $ilDB->query("SELECT * FROM webr_items WHERE ".
			$ilDB->in("webr_id", $a_obj_ids, false, "integer").
			" AND active = '1'");
		foreach ($a_obj_ids as $id)
		{
			self::$item[$id] = array();
		}
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$item['title']				= $row->title;
			$item['description']		= $row->description;
			$item['target']				= $row->target;
			$item['active']				= (bool) $row->active;
			$item['disable_check']		= $row->disable_check;
			$item['create_date']		= $row->create_date;
			$item['last_update']		= $row->last_update;
			$item['last_check']			= $row->last_check;
			$item['valid']				= $row->valid;
			$item['link_id']			= $row->link_id;
			self::$item[$row->webr_id] = $item;
		}
	}

}

?>
