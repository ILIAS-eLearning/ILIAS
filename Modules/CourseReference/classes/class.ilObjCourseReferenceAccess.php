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

include_once("./Services/ContainerReference/classes/class.ilContainerReferenceAccess.php");

/** 
* 
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
* 
* 
* @ingroup ModulesCourseReference
*/

class ilObjCourseReferenceAccess extends ilContainerReferenceAccess
{
	/**
	* Checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* Please do not check any preconditions handled by
	* ilConditionHandler here. Also don't do any RBAC checks.
	*
	* @param	string		$a_cmd			command (not permission!)
 	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	int			$a_user_id		user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilAccess;
		
		switch($a_permission)
		{
			case 'visible':
			case 'read':
				include_once './Modules/CourseReference/classes/class.ilObjCourseReference.php';
				$target_ref_id = ilObjCourseReference::_lookupTargetRefId($a_obj_id);
				
				if(!$ilAccess->checkAccessOfUser($a_user_id, $a_permission, $a_cmd, $target_ref_id))
				{
					return false;
				}
				break;
		}

		return true;
	}

	/**
	 * get commands
	 * 
	 * Depends on permissions
	 * 
	 * @param int $a_ref_id Reference id of course link
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
	function _getCommands($a_ref_id)
	{
		global $ilAccess;
		
		if($ilAccess->checkAccess('write','',$a_ref_id))
		{
			// Only local (reference specific commands)
			$commands = array
			(
				array("permission" => "visible", "cmd" => "", "lang_var" => "show","default" => true),
				array("permission" => "write", "cmd" => "editReference", "lang_var" => "edit")
			);
		}
		else
		{
			include_once('./Modules/Course/classes/class.ilObjCourseAccess.php');
			$commands = ilObjCourseAccess::_getCommands();
		}
		return $commands;
	}
	
} 
?>