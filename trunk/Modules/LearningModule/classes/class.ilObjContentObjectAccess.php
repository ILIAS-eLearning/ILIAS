<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("./Services/Object/classes/class.ilObjectAccess.php");

/**
 * Class ilObjContentObjectAccess
 *
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ModulesIliasLearningModule
 */
class ilObjContentObjectAccess extends ilObjectAccess
{
	static $online;
	static $lo_access;
	
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

				if(!ilObjContentObjectAccess::_lookupOnline($a_obj_id)
					&& !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}
				break;
				
			case "continue":
			
				// no continue command for anonymous user
				if ($ilUser->getId() == ANONYMOUS_USER_ID)
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("lm_no_continue_for_anonym"));
					return false;
				}
			
				if(!ilObjContentObjectAccess::_lookupOnline($a_obj_id)
					&& !$rbacsystem->checkAccessOfUser($a_user_id,'write',$a_ref_id))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}

				if (ilObjContentObjectAccess::_getLastAccessedPage($a_ref_id,$a_user_id) <= 0)
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("not_accessed_yet"));
					return false;
				}
				break;
				
			// for permission query feature
			case "info":
				if(!ilObjContentObjectAccess::_lookupOnline($a_obj_id))
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
				if (!ilObjContentObjectAccess::_lookupOnline($a_obj_id) &&
					(!$rbacsystem->checkAccessOfUser($a_user_id,'write', $a_ref_id)))
				{
					$ilAccess->addInfoItem(IL_NO_OBJECT_ACCESS, $lng->txt("offline"));
					return false;
				}
				break;
		}


		return true;
	}

	//
	// access relevant methods
	//

	/**
	* check wether learning module is online
	*
	* @param	int		$a_id	learning object id
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;

		if (isset(self::$online[$a_id]))
		{
			return self::$online[$a_id];
		}

		$q = "SELECT is_online FROM content_object WHERE id = ".$ilDB->quote($a_id, "integer");
		$lm_set = $ilDB->query($q);
		$lm_rec = $ilDB->fetchAssoc($lm_set);

		self::$online[$a_id] = ilUtil::yn2tf($lm_rec["is_online"]);
		return ilUtil::yn2tf($lm_rec["is_online"]);
	}

	/**
	* get last accessed page
	*
	* @param	int		$a_obj_id	content object id
	* @param	int		$a_user_id	user object id
	*/
	function _getLastAccessedPage($a_ref_id, $a_user_id = "")
	{
		global $ilDB, $ilUser;
		
		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		if (isset(self::$lo_access[$a_ref_id]))
		{
			$acc_rec["obj_id"] = self::$lo_access[$a_ref_id];
		}
		else
		{
			$q = "SELECT * FROM lo_access WHERE ".
				"usr_id = ".$ilDB->quote($a_user_id, "integer")." AND ".
				"lm_id = ".$ilDB->quote($a_ref_id, "integer");
	
			$acc_set = $ilDB->query($q);
			$acc_rec = $ilDB->fetchAssoc($acc_set);
		}
		
		if ($acc_rec["obj_id"] > 0)
		{
			$lm_id = ilObject::_lookupObjId($a_ref_id);
			$mtree = new ilTree($lm_id);
			$mtree->setTableNames('lm_tree','lm_data');
			$mtree->setTreeTablePK("lm_id");
			if ($mtree->isInTree($acc_rec["obj_id"]))
			{
				return $acc_rec["obj_id"];
			}
		}
		
		return 0;
	}

	/**
	* check whether goto script will succeed
	*/
	function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if (($t_arr[0] != "lm" &&  $t_arr[0] != "dbk" &&  $t_arr[0] != "st"
			&&  $t_arr[0] != "pg")
			|| ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if ($t_arr[0] == "lm" || $t_arr[0] == "dbk")
		{
			if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
				$ilAccess->checkAccess("visible", "", $t_arr[1]))
			{
				return true;
			}
		}
		else
		{
			if ($t_arr[2] > 0)
			{
				$ref_ids = array($t_arr[2]);
			}
			else
			{
				// determine learning object
				include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
				$lm_id = ilLMObject::_lookupContObjID($t_arr[1]);
				$ref_ids = ilObject::_getAllReferences($lm_id);
			}
			// check read permissions
			foreach ($ref_ids as $ref_id)
			{
				// Permission check
				if ($ilAccess->checkAccess("read", "", $ref_id))
				{
					return true;
				}
			}

		}
		return false;
	}
	
	/**
	 * Type-specific implementation of general status
	 *
	 * Used in ListGUI and Learning Progress
	 *
	 * @param int $a_obj_id
	 * @return bool
	 */
	static function _isOffline($a_obj_id)
	{
		return !self::_lookupOnline($a_obj_id);
	}
	
	/**
	 * Preload data
	 *
	 * @param array $a_obj_ids array of object ids
	 */
	function _preloadData($a_obj_ids, $a_ref_ids)
	{
		global $ilDB, $ilUser;
		
		$q = "SELECT id, is_online FROM content_object WHERE ".
			$ilDB->in("id", $a_obj_ids, false, "integer");

		$lm_set = $ilDB->query($q);
		while ($rec = $ilDB->fetchAssoc($lm_set))
		{
			self::$online[$rec["id"]] = ilUtil::yn2tf($rec["is_online"]);
		}
		
		$q = "SELECT obj_id, lm_id FROM lo_access WHERE ".
			"usr_id = ".$ilDB->quote($ilUser->getId(), "integer")." AND ".
			$ilDB->in("lm_id", $a_ref_ids, false, "integer");;
		$set = $ilDB->query($q);
		foreach ($a_ref_ids as $r)
		{
			self::$lo_access[$r] = 0;
		}
		while ($rec = $ilDB->fetchAssoc($set))
		{
			self::$lo_access[$rec["lm_id"]] = $rec["obj_id"];
		}

	}

}

?>
