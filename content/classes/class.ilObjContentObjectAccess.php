<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once("classes/class.ilObjectAccess.php");

/**
* Class ilObjContentObjectAccess
*
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package AccessControl
*/
class ilObjContentObjectAccess extends ilObjectAccess
{
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
	*/
	function _lookupOnline($a_id)
	{
		global $ilDB;

		$q = "SELECT * FROM content_object WHERE id = '".$a_id."'";
		$lm_set = $ilDB->query($q);
		$lm_rec = $lm_set->fetchRow(DB_FETCHMODE_ASSOC);

		return ilUtil::yn2tf($lm_rec["online"]);
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

		$q = "SELECT * FROM lo_access WHERE ".
			"usr_id = ".$ilDB->quote($a_user_id)." AND ".
			"lm_id = ".$ilDB->quote($a_ref_id);
			
		$lm_id = ilObject::_lookupObjId($a_ref_id);

		$acc_set = $ilDB->query($q);

		if ($acc_rec = $acc_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
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

}

?>
