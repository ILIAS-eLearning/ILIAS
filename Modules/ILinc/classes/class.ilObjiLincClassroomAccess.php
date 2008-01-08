<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

include_once("./classes/class.ilObjectAccess.php");

/**
* Class ilObjiLincClassroomAccess
*
*
* @author 		Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
*/
class ilObjiLincClassroomAccess extends ilObjectAccess
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
		global $ilUser, $lng, $rbacsystem, $ilAccess, $ilias;

		$user_id = $ilUser->getId();
		$class_id = $a_ref_id;
		$course_ref_id = $a_obj_id;
		$class_arr = $a_user_id;
//var_dump($a_cmd,$a_permission,$a_ref_id,$a_obj_id,$a_user_id);
		/** ATTENTION
		* ref_id contains ilinc classroom id
		* obj_id contains ILIAS ref_id of iLinc Seminar
		* user_id contains online status of classroom
		*/
		switch ($a_cmd)
		{

		}

		switch ($a_permission)
		{
			case 'join':
				// Cannot join closed classrooms 
				if (!$class_arr['alwaysopen'])
				{
					return false;
				}
				
				// non members cannot join
				include_once ('./Modules/ILinc/classes/class.ilObjiLincCourse.php');
				
				if (!ilObjiLincCourse::_isMember($user_id,$a_ref_id))
				{
					return false;
				}
				break;

			case "write":
			case "delete":
				if(!$rbacsystem->checkAccessOfUser($user_id,'write',$a_ref_id,"ilca"))
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
			array("permission" => "join", "cmd" => "joinClassroom", "lang_var" => "join", "frame" => "_blank"),
			array("permission" => "write", "cmd" => "editClassroom", "lang_var" => "edit"),
			array("permission" => "delete", "cmd" => "removeClassroom", "lang_var" => "delete")
		);
		
		return $commands;
	}
}

?>
