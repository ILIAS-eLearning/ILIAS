<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjPortfolioTemplateAccess
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjRootFolderAccess.php 15678 2008-01-06 20:40:55Z akill $
*
*/
class ilObjPortfolioTemplateAccess extends ilObjectAccess
{	
	function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "preview", "lang_var" => "preview", "default" => true),
			array("permission" => "write", "cmd" => "view", "lang_var" => "edit"),
			// array("permission" => "write", "cmd" => "export", "lang_var" => "export_html")
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
		
		if ($t_arr[0] != "prtt" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}
		
		if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
		{
			return true;
		}
		return false;	
	}
}

?>