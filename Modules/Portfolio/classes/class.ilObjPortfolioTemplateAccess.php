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
	public function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "preview", "lang_var" => "preview", "default" => true),
			array("permission" => "write", "cmd" => "view", "lang_var" => "edit"),
			array("permission" => "read", "cmd" => "createfromtemplate", "lang_var" => "prtf_create_portfolio_from_template"),
			// array("permission" => "write", "cmd" => "export", "lang_var" => "export_html")
		);
		
		return $commands;
	}	
	
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		  global $ilUser, $lng, $rbacsystem, $ilAccess;

		  if ($a_user_id == "")
		  {
			   $a_user_id = $ilUser->getId();
		  }

		  switch ($a_cmd)
		  {
			   case "view":
					if(!self::_lookupOnline($a_obj_id)
						 && !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
					{
						 $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
						 return false;
					}
					break;
					
			   // for permission query feature
			   case "infoScreen":
					if(!self::_lookupOnline($a_obj_id))
					{
						 $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					}
					else
					{
						 $ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("online"));
					}
					break;

		  }
		  
		  switch($a_permission)
		  {
			   case "read":
			   case "visible":
					if (!self::_lookupOnline($a_obj_id) &&
						 (!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)))
					{
						 $ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
						 return false;
					}
					break;
		  }

		  return true;
	 }
	
	public function _lookupOnline($a_id)
	{
		include_once "Modules/Portfolio/classes/class.ilObjPortfolioTemplate.php";
		return ilObjPortfolioTemplate::lookupOnline($a_id);
	}
	
	/**
	* check whether goto script will succeed
	*/
	public function _checkGoto($a_target)
	{		
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);		
		
		if ($t_arr[0] != "prtt" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}
		
		if ($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;	
	}
}

?>