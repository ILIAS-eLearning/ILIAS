<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
* Class ilObjWikiAccess
*
*
* @author 		Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesWiki
*/
class ilObjWikiAccess extends ilObjectAccess
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
			array("permission" => "read", "cmd" => "view", "lang_var" => "show",
				"default" => true),
			array("permission" => "write", "cmd" => "editSettings", "lang_var" => "settings")
		);
		
		return $commands;
	}
	
	/**
	* checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* @param	string		$a_cmd		command (not permission!)
	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id	reference id
	* @param	int			$a_obj_id	object id
	* @param	int			$a_user_id	user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $lng, $rbacsystem, $ilAccess;

		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_cmd)
		{
			case "view":

				if(!ilObjWikiAccess::_lookupOnline($a_obj_id)
					&& !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}
				break;
				
			// for permission query feature
			case "infoScreen":
				if(!ilObjWikiAccess::_lookupOnline($a_obj_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
				}
				else
				{
					$ilAccess->addInfoItem(IL_STATUS_MESSAGE, $lng->txt("online"));
				}
				break;

		}
		switch ($a_permission)
		{
			case "read":
			case "visible":
				if (!ilObjWikiAccess::_lookupOnline($a_obj_id) &&
					(!$rbacsystem->checkAccessOfUser($a_user_id, 'write', $a_ref_id)))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}
				break;
		}

		return true;
	}

	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
//	echo "-".$a_target."-"; exit;
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "wiki" || (((int) $t_arr[1]) <= 0) && $t_arr[1] != "wpage")
		{
			return false;
		}
		
		if ($t_arr[1] == "wpage")
		{
			$wpg_id = (int) $t_arr[2];
			include_once("./Modules/Wiki/classes/class.ilWikiPage.php");
			$w_id = ilWikiPage::lookupWikiId($wpg_id);
			if ((int) $t_arr[3] > 0)
			{
				$refs = array((int) $t_arr[3]);
			}
			else
			{
				$refs = ilObject::_getAllReferences($w_id);
			}
			foreach ($refs as $r)
			{
				if ($ilAccess->checkAccess("read", "", $r))
				{
					return true;
				}
			}
		}
		else if ($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
	
	/**
	* Check wether wiki cast is online
	*
	* @param	int		$a_id	wiki id
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM il_wiki_data WHERE id = ".
			$ilDB->quote($a_id, "integer");
		$wk_set = $ilDB->query($q);
		$wk_rec = $ilDB->fetchAssoc($wk_set);

		return $wk_rec["is_online"];
	}

	/**
	* Check wether files should be public
	*
	* @param	int		$a_id	wiki id
	*/
	function _lookupPublicFiles($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM il_wiki_data WHERE id = ".
			$ilDB->quote($a_id, "integer");
		$wk_set = $ilDB->query($q);
		$wk_rec = $ilDB->fetchAssoc($wk_set);

		return $wk_rec["public_files"];
	}

}

?>
