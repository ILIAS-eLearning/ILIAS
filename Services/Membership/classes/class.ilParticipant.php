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


include_once './Services/Membership/classes/class.ilParticipants.php';

/**
* Base class for course and group participants
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesMembership
*/
abstract class ilParticipant
{
	private $obj_id = 0;
	private $usr_id = 0;
	private $type = '';
	private $ref_id = 0;

	private $participants = false;
	private $admins = false;
	private $tutors = false;
	private $members = false;

	private $participants_status = array();

	/**
	 * Singleton Constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	protected function __construct($a_obj_id,$a_usr_id)
	{
	 	global $ilDB,$lng;
	 	
	 	$this->obj_id = $a_obj_id;
		$this->usr_id = $a_usr_id;
	 	$this->type = ilObject::_lookupType($a_obj_id);
		$ref_ids = ilObject::_getAllReferences($this->obj_id);
		$this->ref_id = current($ref_ids);
	 	
	 	$this->readParticipant();
	 	$this->readParticipantStatus();
	}

	/**
	 * get user id
	 * @return int
	 */
	public function getUserId()
	{
		return $this->usr_id;
	}

	public function isBlocked()
	{
		return (bool) $this->participants_status['blocked'];
	}

	public function isAssigned()
	{
		return (bool) $this->participants;
	}

	public function isMember()
	{
		return (bool) $this->members;
	}

	public function isAdmin()
	{
		return $this->admins;
	}

	public function isTutor()
	{
		return (bool) $this->tutors;
	}

	public function isParticipant()
	{
		return (bool) $this->participants;
	}
	

	/**
	 * Read participant
	 * @return void
	 */
	protected function readParticipant()
	{
		global $rbacreview,$ilObjDataCache,$ilLog;

		$rolf = $rbacreview->getRoleFolderOfObject($this->ref_id);

		if(!isset($rolf['ref_id']) or !$rolf['ref_id'])
		{
			$title = $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->ref_id));
			$ilLog->write(__METHOD__.': Found object without role folder. Ref_id: '.$this->ref_id.', title: '.$title);
			$ilLog->logStack();
			return false;
		}

		$this->roles = $rbacreview->getRolesOfRoleFolder($rolf['ref_id'],false);

		$users = array();
		$this->participants = array();
		$this->members = $this->admins = $this->tutors = array();

		foreach($this->roles as $role_id)
		{
			$title = $ilObjDataCache->lookupTitle($role_id);
			switch(substr($title,0,8))
			{
				case 'il_crs_m':
					$this->role_data[IL_CRS_MEMBER] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->member = true;
					}
					break;

				case 'il_crs_a':
					$this->role_data[IL_CRS_ADMIN] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->admins = true;
					}
					break;

				case 'il_crs_t':
					$this->role_data[IL_CRS_TUTOR] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->tutors = true;
					}
					break;

				case 'il_grp_a':
					$this->role_data[IL_GRP_ADMIN] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->admins = true;
					}
					break;

				case 'il_grp_m':
					$this->role_data[IL_GRP_MEMBER] = $role_id;
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->members = true;
					}
					break;

				default:
					if($rbacreview->isAssigned($this->getUserId(),$role_id))
					{
						$this->participants = true;
						$this->members = true;
					}
					break;
			}
		}
	}

	/**
	 * Read participant status
	 * @global <type> $ilDB
	 */
	protected function readParticipantStatus()
	{
	 	global $ilDB;

	 	$query = "SELECT * FROM crs_members ".
	 		"WHERE obj_id = ".$ilDB->quote($this->obj_id ,'integer')." ".
			'AND usr_id = '.$ilDB->quote($this->getUserId(),'integer');

	 	$res = $ilDB->query($query);
	 	$this->participants_status = array();
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		$this->participants_status[$this->getUserId()]['blocked'] = $row->blocked;
	 		$this->participants_status[$this->getUserId()]['notification']  = $row->notification;
	 		$this->participants_status[$this->getUserId()]['passed'] = $row->passed;
	 	}
	}
}
?>