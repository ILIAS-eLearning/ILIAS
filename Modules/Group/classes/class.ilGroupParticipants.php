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

include_once('./Services/Membership/classes/class.ilParticipants.php');

/**
* 
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/


class ilGroupParticipants extends ilParticipants
{
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	protected function __construct($a_obj_id)
	{
		$this->type = 'grp';
		parent::__construct($a_obj_id);
	}
	
	/**
	 * Get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _getInstanceByObjId($a_obj_id)
	{
		if(isset(self::$instances[$a_obj_id]) and self::$instances[$a_obj_id])
		{
			return self::$instances[$a_obj_id];
		}
		return self::$instances[$a_obj_id] = new ilGroupParticipants($a_obj_id);
	}
	
	/**
	 * Static function to check if a user is a participant of the container object
	 *
	 * @access public
	 * @param int ref_id
	 * @param int user id
	 * @static
	 */
	public static function _isParticipant($a_ref_id,$a_usr_id)
	{
		global $rbacreview,$ilObjDataCache,$ilDB,$ilLog;

		$rolf = $rbacreview->getRoleFolderOfObject($a_ref_id);
		if(!isset($rolf['ref_id']) or !$rolf['ref_id'])
		{
			$title = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($a_ref_id));
			$ilLog->write(__METHOD__.': Found object without role folder. Ref_id: '.$a_ref_id.', title: '.$title);
			$ilLog->logStack();
			
			return false;
		}
		$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
		$user_roles = $rbacreview->assignedRoles($a_usr_id);
		
		return count(array_intersect((array) $local_roles,(array) $user_roles)) ? true : false;
	}
	
	
}
?>