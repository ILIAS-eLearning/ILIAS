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

include_once './Services/Object/classes/class.ilObjectAccess.php';

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/

class ilObjSessionAccess extends ilObjectAccess
{
	protected static $registrations = null;
	protected static $registered = null;

	/**
	 * get list of command/permission combinations
	 *
	 * @access public
	 * @return array
	 * @static
	 */
	public function _getCommands()
	{
		$commands = array
		(
			array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "info_short", "default" => true),
			array("permission" => "read", "cmd" => "register", "lang_var" => "join_session"),
			array("permission" => "read", "cmd" => "unregister", "lang_var" => "event_unregister"),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
			array('permission' => 'write', 'cmd' => 'members', 'lang_var' => 'event_edit_members')
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
	public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $lng, $rbacsystem, $ilAccess;
		
		$a_user_id = $a_user_id ? $a_user_id : $ilUser->getId();
		switch($a_cmd)
		{
			case 'register':
				if(self::_lookupRegistration($a_obj_id))
				{
					return !self::_lookupRegistered($a_user_id,$a_obj_id);
				}
				return false;
				
			case 'unregister':
				if(self::_lookupRegistration($a_obj_id))
				{
					return self::_lookupRegistered($a_user_id,$a_obj_id);
				}
				return false;
		}
		return true;
	}
	
	
	/**
	* check whether goto script will succeed
	*/
	public function _checkGoto($a_target)
	{
		global $ilAccess;
		
		$t_arr = explode("_", $a_target);

		if ($t_arr[0] != "sess" || ((int) $t_arr[1]) <= 0)
		{
			return false;
		}

		if($ilAccess->checkAccess("read", "", $t_arr[1]))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * lookup registrations
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _lookupRegistration($a_obj_id)
	{
		if(!is_null(self::$registrations))
		{
			return self::$registrations[$a_obj_id];
		}
		
		global $ilDB;
		
		$query = "SELECT registration,obj_id FROM event ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$registrations[$row->obj_id] = (bool) $row->registration;
		}
		return self::$registrations[$a_obj_id];
	}
	
	/**
	 * lookup if user has registered
	 *
	 * @access public
	 * @param int usr_id
	 * @param int obj_id
	 * @return
	 * @static
	 */
	public static function _lookupRegistered($a_usr_id,$a_obj_id)
	{
		if(isset(self::$registered[$a_usr_id]))
		{
			return (bool) self::$registered[$a_usr_id][$a_obj_id];
		}
		
		global $ilDB,$ilUser;
		
		$query = "SELECT event_id, registered FROM event_participants WHERE usr_id = ".$ilDB->quote($ilUser->getId(),'integer');
		$res = $ilDB->query($query);
		self::$registered[$a_usr_id] = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$registered[$a_usr_id][$row->event_id] = (bool) $row->registered;
		}
		return (bool) self::$registered[$a_usr_id][$a_obj_id];
	}
	
}
?>