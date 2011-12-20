<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("classes/class.ilObjectAccess.php");

/**
* Class ilObjBlogAccess
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjRootFolderAccess.php 15678 2008-01-06 20:40:55Z akill $
*
*/
class ilObjBlogAccess extends ilObjectAccess
{
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
			array("permission" => "read", "cmd" => "preview", "lang_var" => "show", "default" => true),
			array("permission" => "write", "cmd" => "render", "lang_var" => "edit"),
			array("permission" => "write", "cmd" => "export", "lang_var" => "export")
		);
		
		return $commands;
	}
	
	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		$t_arr = explode("_", $a_target);
		
		include_once "Services/PersonalWorkspace/classes/class.ilSharedResourceGUI.php";
		return ilSharedResourceGUI::hasAccess($t_arr[1]);
	}
}

?>
