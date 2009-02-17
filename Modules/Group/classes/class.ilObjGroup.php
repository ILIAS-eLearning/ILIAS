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


//TODO: function getRoleId($groupRole) returns the object-id of grouprole

require_once "./Services/Container/classes/class.ilContainer.php";
include_once('./Services/Calendar/classes/class.ilDateTime.php');


define('GRP_REGISTRATION_DEACTIVATED',-1);
define('GRP_REGISTRATION_DIRECT',0);
define('GRP_REGISTRATION_REQUEST',1);
define('GRP_REGISTRATION_PASSWORD',2);

define('GRP_REGISTRATION_LIMITED',1);
define('GRP_REGISTRATION_UNLIMITED',2);

define('GRP_TYPE_UNKNOWN',0);
define('GRP_TYPE_CLOSED',1);
define('GRP_TYPE_OPEN',2);
define('GRP_TYPE_PUBLIC',3);

/**
* Class ilObjGroup
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @extends ilObject
*/
class ilObjGroup extends ilContainer
{
	const CAL_REG_START = 1;
	const CAL_REG_END 	= 2;
	
	
	const ERR_MISSING_TITLE = 'grp_missing_title';
	const ERR_MISSING_GROUP_TYPE = 'grp_missing_grp_type';
	const ERR_MISSING_PASSWORD = 'grp_missing_password';
	const ERR_WRONG_MAX_MEMBERS = 'grp_wrong_max_members';
	const ERR_WRONG_REG_TIME_LIMIT = 'grp_wrong_reg_time_limit';
	
	protected $information;
	protected $group_type = null;
	protected $reg_type = GRP_REGISTRATION_DIRECT;
	protected $reg_enabled = true;
	protected $reg_unlimited = true;
	protected $reg_start = null;
	protected $reg_end = null;
	protected $reg_password = '';
	protected $reg_membership_limitation = false;
	protected $reg_max_members = 0;
	protected $waiting_list = false;
	
	
	// Map
	protected $latitude;
	protected $longitude;
	protected $location_zoom;
	protected $enablemap;
	
	public $members_obj;


	/**
	* Group file object for handling of export files
	*/
	var $file_obj = null;

	var $m_grpStatus;

	var $m_roleMemberId;

	var $m_roleAdminId;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $tree;

		$this->tree =& $tree;

		$this->type = "grp";
		$this->ilObject($a_id,$a_call_by_reference);
		$this->setRegisterMode(true); // ???
	}
	
	// Setter/Getter
	/**
	 * set information
	 *
	 * @access public
	 * @param string information
	 * @return
	 */
	public function setInformation($a_information)
	{
		$this->information = $a_information;
	}
	
	/**
	 * get Information
	 *
	 * @access public
	 * @param
	 * @return string information
	 */
	public function getInformation()
	{
		return $this->information;
	}
	
	/**
	 * set group type
	 *
	 * @access public
	 * @param int type
	 */
	public function setGroupType($a_type)
	{
		$this->group_type = $a_type;
	}
	
	/**
	 * get group type
	 *
	 * @access public
	 * @return int group type
	 */
	public function getGroupType()
	{
		return $this->group_type;
	}
	
	/**
	 * check if group type is modified
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function isGroupTypeModified($a_old_type)
	{
		if($a_old_type == GRP_TYPE_UNKNOWN)
		{
			$group_type = $this->readGroupStatus();
		}
		else
		{
			$group_type = $a_old_type;
		}
		return $group_type != $this->getGroupType(); 
	}
	
	/**
	 * set registration type
	 *
	 * @access public
	 * @param int registration type
	 * @return
	 */
	public function setRegistrationType($a_type)
	{
		$this->reg_type = $a_type;
	}
	
	/**
	 * get registration type
	 *
	 * @access public
	 * @return int registration type
	 */
	public function getRegistrationType()
	{
		return $this->reg_type;
	}
	
	/**
	 * is registration enabled
	 *
	 * @access public
	 * @return bool
	 */
	public function isRegistrationEnabled()
	{
		return $this->getRegistrationType() != GRP_REGISTRATION_DEACTIVATED;
	}
	
	/**
	 * enable unlimited registration 
	 *
	 * @access public
	 * @param bool
	 * @return
	 */
	public function enableUnlimitedRegistration($a_status)
	{
		$this->reg_unlimited = $a_status;
	}
	
	/**
	 * is registration unlimited
	 *
	 * @access public
	 * @return bool
	 */
	public function isRegistrationUnlimited()
	{
		return $this->reg_unlimited;
	}
	
	/**
	 * set registration start
	 *
	 * @access public
	 * @param object ilDateTime
	 * @return
	 */
	public function setRegistrationStart($a_start)
	{
		$this->reg_start = $a_start;
	}
	
	/**
	 * get registration start
	 *
	 * @access public
	 * @return int registration start 
	 */
	public function getRegistrationStart()
	{
		return $this->reg_start ? $this->reg_start : $this->reg_start = new ilDateTime(date('Y-m-d').' 08:00:00',IL_CAL_DATETIME);
	}
	

	/**
	 * set registration end
	 *
	 * @access public
	 * @param int unix time
	 * @return
	 */
	public function setRegistrationEnd($a_end)
	{
		$this->reg_end = $a_end;
	}
	
	/**
	 * get registration end
	 *
	 * @access public
	 * @return int registration end
	 */
	public function getRegistrationEnd()
	{
		return $this->reg_end ? $this->reg_end : $this->reg_end = new ilDateTime(date('Y-m-d').' 16:00:00',IL_CAL_DATETIME);
	}

	/**
	 * set password
	 *
	 * @access public
	 * @param string password
	 */
	public function setPassword($a_pass)
	{
		$this->reg_password = $a_pass;
	}
	
	/**
	 * get password
	 *
	 * @access public
	 * @return string password
	 */
	public function getPassword()
	{
		return $this->reg_password;
	}
	
	/**
	 * enable max member limitation
	 *
	 * @access public
	 * @param bool status
	 * @return
	 */
	public function enableMembershipLimitation($a_status)
	{
		$this->reg_membership_limitation = $a_status;
	}
	
	/**
	 * is max member limited
	 *
	 * @access public
	 * @return
	 */
	public function isMembershipLimited()
	{
		return (bool) $this->reg_membership_limitation;
	}

	/**
	 * set max members
	 *
	 * @access public
	 * @param int max members
	 */
	public function setMaxMembers($a_max)
	{
		$this->reg_max_members = $a_max;
	}
	
	/**
	 * get max members
	 *
	 * @access public
	 * @return
	 */
	public function getMaxMembers()
	{
		return $this->reg_max_members;
	}
	
	/**
	 * enable waiting list
	 *
	 * @access public
	 * @param bool
	 * @return
	 */
	public function enableWaitingList($a_status)
	{
		$this->waiting_list = $a_status;
	}
	
	/**
	 * is waiting list enabled
	 *
	 * @access public
	 * @param
	 * @return bool
	 */
	public function isWaitingListEnabled()
	{
		return $this->waiting_list;
	}
	
	/**
	* Set Latitude.
	*
	* @param	string	$a_latitude	Latitude
	*/
	function setLatitude($a_latitude)
	{
		$this->latitude = $a_latitude;
	}

	/**
	* Get Latitude.
	*
	* @return	string	Latitude
	*/
	function getLatitude()
	{
		return $this->latitude;
	}

	/**
	* Set Longitude.
	*
	* @param	string	$a_longitude	Longitude
	*/
	function setLongitude($a_longitude)
	{
		$this->longitude = $a_longitude;
	}

	/**
	* Get Longitude.
	*
	* @return	string	Longitude
	*/
	function getLongitude()
	{
		return $this->longitude;
	}

	/**
	* Set LocationZoom.
	*
	* @param	int	$a_locationzoom	LocationZoom
	*/
	function setLocationZoom($a_locationzoom)
	{
		$this->locationzoom = $a_locationzoom;
	}

	/**
	* Get LocationZoom.
	*
	* @return	int	LocationZoom
	*/
	function getLocationZoom()
	{
		return $this->locationzoom;
	}

	/**
	* Set Enable Group Map.
	*
	* @param	boolean	$a_enablemap	Enable Group Map
	*/
	function setEnableGroupMap($a_enablemap)
	{
		$this->enablemap = $a_enablemap;
	}

	/**
	* Get Enable Group Map.
	*
	* @return	boolean	Enable Group Map
	*/
	function getEnableGroupMap()
	{
		return $this->enablemap;
	}
	
	/**
	 * validate group settings
	 *
	 * @access public
	 * @return bool
	 */
	public function validate()
	{
		global $ilErr;
		
		if($this->getTitle() == 'NO TITLE')
		{
			$this->title = '';
			$ilErr->appendMessage(self::ERR_MISSING_TITLE);
		}
		if(!$this->getGroupType())
		{
			$ilErr->appendMessage(self::ERR_MISSING_GROUP_TYPE);
		}
		if($this->getRegistrationType() == GRP_REGISTRATION_PASSWORD and !strlen($this->getPassword()))
		{
			$ilErr->appendMessage(self::ERR_MISSING_PASSWORD);
		}
		if(ilDateTime::_before($this->getRegistrationEnd(),$this->getRegistrationStart()))
		{
			$ilErr->appendMessage(self::ERR_WRONG_REG_TIME_LIMIT);
		}
		if($this->isMembershipLimited() and (!is_numeric($this->getMaxMembers()) or $this->getMaxMembers() <= 0))
		{
			$ilErr->appendMessage(self::ERR_WRONG_MAX_MEMBERS);
		}
		return strlen($ilErr->getMessage()) == 0;
	}
	
	
	

	/**
	* Create group
	*/
	function create()
	{
		global $ilDB,$ilAppEventHandler;

		if(!parent::create())
		{
			return false;
		}

		$query = "INSERT INTO grp_settings ".
			"SET obj_id = ".$ilDB->quote($this->getId()).", ".
			"information = ".$ilDB->quote($this->getInformation()).", ".
			"grp_type = ".$ilDB->quote((int) $this->getGroupType()).", ".
			"registration_type = ".$ilDB->quote($this->getRegistrationType()).", ".
			"registration_enabled = ".($this->isRegistrationEnabled() ? 1 : 0).", ".
			"registration_unlimited = ".($this->isRegistrationUnlimited() ? 1 : 0).", ".
			"registration_start = ".$ilDB->quote($this->getRegistrationStart()->get(IL_CAL_DATETIME,'')).", ".
			"registration_end = ".$ilDB->quote($this->getRegistrationEnd()->get(IL_CAL_DATETIME,'')).", ".
			"registration_password = ".$ilDB->quote($this->getPassword()).", ".
			"registration_membership_limited = ".$ilDB->quote((int) $this->isMembershipLimited()).", ".
			"registration_max_members = ".$ilDB->quote($this->getMaxMembers()).", ".
			"waiting_list = ".$ilDB->quote($this->isWaitingListEnabled() ? 1 : 0).", ".
			"latitude = ".$ilDB->quote($this->getLatitude()).", ".
			"longitude = ".$ilDB->quote($this->getLongitude()).", ".
			"location_zoom = ".$ilDB->quote($this->getLocationZoom()).", ".
			"enablemap = ".$ilDB->quote($this->getEnableGroupMap())." ";

		$ilDB->query($query);

		$ilAppEventHandler->raise('Modules/Group',
			'create',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareAppointments('create')));
		
		return $this->getId();
	}

	/**
	* Update group
	*/
	function update()
	{
		global $ilDB,$ilAppEventHandler;

		if (!parent::update())
		{
			return false;
		}

		$query = "UPDATE grp_settings ".
			"SET information = ".$ilDB->quote($this->getInformation()).", ".
			"grp_type = ".$ilDB->quote((int) $this->getGroupType()).", ".
			"registration_type = ".$ilDB->quote($this->getRegistrationType()).", ".
			"registration_enabled = ".($this->isRegistrationEnabled() ? 1 : 0).", ".
			"registration_unlimited = ".($this->isRegistrationUnlimited() ? 1 : 0).", ".
			"registration_start = ".$ilDB->quote($this->getRegistrationStart()->get(IL_CAL_DATETIME,'')).", ".
			"registration_end = ".$ilDB->quote($this->getRegistrationEnd()->get(IL_CAL_DATETIME,'')).", ".
			"registration_password = ".$ilDB->quote($this->getPassword()).", ".
			"registration_membership_limited = ".$ilDB->quote((int) $this->isMembershipLimited()).", ".
			"registration_max_members = ".$ilDB->quote($this->getMaxMembers()).", ".
			"waiting_list = ".$ilDB->quote($this->isWaitingListEnabled() ? 1 : 0).", ".
			"latitude = ".$ilDB->quote($this->getLatitude()).", ".
			"longitude = ".$ilDB->quote($this->getLongitude()).", ".
			"location_zoom = ".$ilDB->quote($this->getLocationZoom()).", ".
			"enablemap = ".$ilDB->quote($this->getEnableGroupMap())." ".
			"WHERE obj_id = ".$ilDB->quote($this->getId())." ";

		$ilDB->query($query);
		
		$ilAppEventHandler->raise('Modules/Group',
			'update',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareAppointments('update')));
				
		
		return true;
	}
	
	/**
	* delete group and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	public function delete()
	{
		global $ilDB,$ilAppEventHandler;

		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		$query = "DELETE FROM grp_settings ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		$ilDB->query($query);
		
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		ilGroupParticipants::_deleteAllEntries($this->getId());
		
		$ilAppEventHandler->raise('Modules/Group',
			'delete',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareAppointments('delete')));
		
		
		return true;
	}
	

	/**
	* Read group
	*/
	function read()
	{
		global $ilDB;

		parent::read();

		$query = "SELECT * FROM grp_settings ".
			"WHERE obj_id = ".$ilDB->quote($this->getId());
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setInformation($row->information);
			$this->setGroupType($row->grp_type);
			$this->setRegistrationType($row->registration_type);
			$this->enableUnlimitedRegistration($row->registration_unlimited);
			$this->setRegistrationStart(new ilDateTime($row->registration_start,IL_CAL_DATETIME));
			$this->setRegistrationEnd(new ilDateTime($row->registration_end,IL_CAL_DATETIME));
			$this->setPassword($row->registration_password);
			$this->enableMembershipLimitation((bool) $row->registration_membership_limited);
			$this->setMaxMembers($row->registration_max_members);
			$this->enableWaitingList($row->waiting_list);
			$this->setLatitude($row->latitude);
			$this->setLongitude($row->longitude);
			$this->setLocationZoom($row->location_zoom);
			$this->setEnableGroupMap($row->enablemap);
		}
		$this->initParticipants();
		
		// Inherit order type from parent course (if exists)
		include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
		$this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
	}

	/**
	 * Clone group (no member data)
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 *
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB,$ilUser;

	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	$new_obj->setGroupType($this->getGroupType());
	 	$new_obj->initGroupStatus($this->getGroupType() ? $this->getGroupType() : $this->readGroupStatus());

	 	$this->cloneAutoGeneratedRoles($new_obj);

		$new_obj->setInformation($this->getInformation());
		$new_obj->setRegistrationStart($this->getRegistrationStart());
		$new_obj->setRegistrationEnd($this->getRegistrationEnd());
		$new_obj->enableUnlimitedRegistration($this->isRegistrationUnlimited());
		$new_obj->setPassword($this->getPassword());
		$new_obj->enableMembershipLimitation($this->isMembershipLimited());
		$new_obj->setMaxMembers($this->getMaxMembers());
		$new_obj->enableWaitingList($this->isWaitingListEnabled());
		
		// map
		$new_obj->setLatitude($this->getLatitude());
		$new_obj->setLongitude($this->getLongitude());
		$new_obj->setLocationZoom($this->getLocationZoom());
		$new_obj->setEnableGroupMap($this->getEnableGroupMap());
		$new_obj->update();

		global $ilLog;
		$ilLog->write(__METHOD__.': Starting add user');
		
		// Assign user as admin
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$part = ilGroupParticipants::_getInstanceByObjId($new_obj->getId());
		$part->add($ilUser->getId(),IL_GRP_ADMIN);
		$part->updateNotification($ilUser->getId(),1);

		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
		
		// clone icons
		$new_obj->saveIcons($this->getBigIconPath(),
			$this->getSmallIconPath(),
			$this->getTinyIconPath());

		return $new_obj;
	}

	/**
	 * Clone object dependencies (crs items, preconditions)
	 *
	 * @access public
	 * @param int target ref id of new course
	 * @param int copy id
	 *
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		global $tree;

		parent::cloneDependencies($a_target_id,$a_copy_id);

		if($course_ref_id = $tree->checkForParentType($this->getRefId(),'crs') and
			$new_course_ref_id = $tree->checkForParentType($a_target_id,'crs'))
		{
			include_once('Modules/Course/classes/class.ilCourseItems.php');
			$course_obj =& ilObjectFactory::getInstanceByRefId($course_ref_id,false);
			$course_items = new ilCourseItems($course_obj,$this->getRefId());
			$course_items->cloneDependencies($a_target_id,$a_copy_id);
		}

		include_once('Services/Tracking/classes/class.ilLPCollections.php');
		$lp_collection = new ilLPCollections($this->getId());
		$lp_collection->cloneCollections($a_target_id,$a_copy_id);

	 	return true;
	}

	/**
	 * Clone group admin and member role permissions
	 *
	 * @access public
	 * @param object new group object
	 *
	 */
	public function cloneAutoGeneratedRoles($new_obj)
	{
		global $ilLog,$rbacadmin,$rbacreview;

		$admin = $this->getDefaultAdminRole();
		$new_admin = $new_obj->getDefaultAdminRole();
	 	$source_rolf = $rbacreview->getRoleFolderIdOfObject($this->getRefId());
	 	$target_rolf = $rbacreview->getRoleFolderIdOfObject($new_obj->getRefId());

		if(!$admin || !$new_admin || !$source_rolf || !$target_rolf)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_grp_admin');
		}
		$rbacadmin->copyRolePermissions($admin,$source_rolf,$target_rolf,$new_admin,true);
		$ilLog->write(__METHOD__.' : Finished copying of role il_grp_admin.');

		$member = $this->getDefaultMemberRole();
		$new_member = $new_obj->getDefaultMemberRole();
		if(!$member || !$new_member)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_grp_member');
		}
		$rbacadmin->copyRolePermissions($member,$source_rolf,$target_rolf,$new_member,true);
		$ilLog->write(__METHOD__.' : Finished copying of role grp_member.');
	}


	/**
	* join Group, assigns user to role
	* @access	private
	* @param	integer	member status = obj_id of local_group_role
	*/
	function join($a_user_id, $a_mem_role="")
	{
		global $rbacadmin;

		if (is_array($a_mem_role))
		{
			foreach ($a_mem_role as $role)
			{
				$rbacadmin->assignUser($role,$a_user_id, false);
			}
		}
		else
		{
			$rbacadmin->assignUser($a_mem_role,$a_user_id, false);
		}

		return true;
	}

	/**
	* returns object id of created default member role
	* @access	public
	*/
	function getDefaultMemberRole()
	{
		$local_group_Roles = $this->getLocalGroupRoles();

		return $local_group_Roles["il_grp_member_".$this->getRefId()];
	}

	/**
	* returns object id of created default adminstrator role
	* @access	public
	*/
	function getDefaultAdminRole()
	{
		$local_group_Roles = $this->getLocalGroupRoles();

		return $local_group_Roles["il_grp_admin_".$this->getRefId()];
	}

	/**
	* add Member to Group
	* @access	public
	* @param	integer	user_id
	* @param	integer	member role_id of local group_role
	*/
	function addMember($a_user_id, $a_mem_role)
	{
		global $rbacadmin;

		if (isset($a_user_id) && isset($a_mem_role) )
		{
			$this->join($a_user_id,$a_mem_role);
			return true;
		}
		else
		{
			$this->ilias->raiseError(get_class($this)."::addMember(): Missing parameters !",$this->ilias->error_obj->WARNING);
			return false;
		}
	}


	/**
	* is called when a member decides to leave group
	* @access	public
	* @param	integer	user-Id
	* @param	integer group-Id
	*/
	function leaveGroup()
	{
		global $rbacadmin, $rbacreview;

		$member_ids = $this->getGroupMemberIds();

		if (count($member_ids) <= 1 || !in_array($this->ilias->account->getId(), $member_ids))
		{
			return 2;
		}
		else
		{
			if (!$this->isAdmin($this->ilias->account->getId()))
			{
				$this->leave($this->ilias->account->getId());
				$member = new ilObjUser($this->ilias->account->getId());
				$member->dropDesktopItem($this->getRefId(), "grp");

				return 0;
			}
			else if (count($this->getGroupAdminIds()) == 1)
			{
				return 1;
			}
		}
	}

	/**
	* deassign member from group role
	* @access	private
	*/
	function leave($a_user_id)
	{
		global $rbacadmin;

		$arr_groupRoles = $this->getMemberRoles($a_user_id);

		if (is_array($arr_groupRoles))
		{
			foreach ($arr_groupRoles as $groupRole)
			{
				$rbacadmin->deassignUser($groupRole, $a_user_id);
			}
		}
		else
		{
			$rbacadmin->deassignUser($arr_groupRoles, $a_user_id);
		}

		return true;
	}

	/**
	* get all group Member ids regardless of role
	* @access	public
	* @return	return array of users (obj_ids) that are assigned to
	* the groupspecific roles (grp_member,grp_admin)
	*/
	function getGroupMemberIds()
	{
		global $rbacadmin, $rbacreview;

		$usr_arr= array();

		$rol  = $this->getLocalGroupRoles();

		foreach ($rol as $value)
		{
			foreach ($rbacreview->assignedUsers($value) as $member_id)
			{
				array_push($usr_arr,$member_id);
			}
		}

		$mem_arr = array_unique($usr_arr);

		return $mem_arr ? $mem_arr : array();
	}

	/**
	* get all group Members regardless of group role.
	* fetch all users data in one shot to improve performance
	* @access	public
	* @param	array	of user ids
	* @return	return array of userdata
	*/
	function getGroupMemberData($a_mem_ids, $active = 1)
	{
		global $rbacadmin, $rbacreview, $ilBench, $ilDB;

		$usr_arr= array();

		$q = "SELECT login,firstname,lastname,title,usr_id,last_login ".
			 "FROM usr_data ".
			 "WHERE usr_id IN (".implode(',',ilUtil::quoteArray($a_mem_ids)).") ";

  		if (is_numeric($active) && $active > -1)
  			$q .= "AND active = '$active'";
  			
  		$q .= 'ORDER BY lastname,firstname';

  		$r = $ilDB->query($q);
  		
		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$mem_arr[] = array("id" => $row->usr_id,
								"login" => $row->login,
								"firstname" => $row->firstname,
								"lastname" => $row->lastname,
								"last_login" => $row->last_login
								);
		}

		return $mem_arr ? $mem_arr : array();
	}

	function getCountMembers()
	{
		return count($this->getGroupMemberIds());
	}

	/**
	* get Group Admin Id
	* @access	public
	* @param	integer	group id
	* @param	returns userids that are assigned to a group administrator! role
	*/
	function getGroupAdminIds($a_grpId="")
	{
		global $rbacreview;

		if (!empty($a_grpId))
		{
			$grp_id = $a_grpId;
		}
		else
		{
			$grp_id = $this->getRefId();
		}

		$usr_arr = array();
		$roles = $this->getDefaultGroupRoles($this->getRefId());

		foreach ($rbacreview->assignedUsers($this->getDefaultAdminRole()) as $member_id)
		{
			array_push($usr_arr,$member_id);
		}

		return $usr_arr;
	}

	/**
	* get default group roles, returns the defaultlike create roles il_grp_member, il_grp_admin
	* @access	public
	* @param 	returns the obj_ids of group specific roles(il_grp_member,il_grp_admin)
	*/
	function getDefaultGroupRoles($a_grp_id="")
	{
		global $rbacadmin, $rbacreview;

		if (strlen($a_grp_id) > 0)
		{
			$grp_id = $a_grp_id;
		}
		else
		{
			$grp_id = $this->getRefId();
		}

		$rolf 	   = $rbacreview->getRoleFolderOfObject($grp_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

		foreach ($role_arr as $role_id)
		{
			$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

			$grp_Member ="il_grp_member_".$grp_id;
			$grp_Admin  ="il_grp_admin_".$grp_id;

			if (strcmp($role_Obj->getTitle(), $grp_Member) == 0 )
			{
				$arr_grpDefaultRoles["grp_member_role"] = $role_Obj->getId();
			}

			if (strcmp($role_Obj->getTitle(), $grp_Admin) == 0)
			{
				$arr_grpDefaultRoles["grp_admin_role"] = $role_Obj->getId();
			}
		}

		return $arr_grpDefaultRoles;
	}

	/**
	* get ALL local roles of group, also those created and defined afterwards
	* only fetch data once from database. info is stored in object variable
	* @access	public
	* @return	return array [title|id] of roles...
	*/
	function getLocalGroupRoles($a_translate = false)
	{
		global $rbacadmin,$rbacreview;

		if (empty($this->local_roles))
		{
			$this->local_roles = array();
			$rolf 	   = $rbacreview->getRoleFolderOfObject($this->getRefId());
			$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

			foreach ($role_arr as $role_id)
			{
				if ($rbacreview->isAssignable($role_id,$rolf["ref_id"]) == true)
				{
					$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

					if ($a_translate)
					{
						$role_name = ilObjRole::_getTranslation($role_Obj->getTitle());
					}
					else
					{
						$role_name = $role_Obj->getTitle();
					}

					$this->local_roles[$role_name] = $role_Obj->getId();
				}
			}
		}

		return $this->local_roles;
	}

	/**
	* get group status closed template
	* @access	public
	* @param	return obj_id of roletemplate containing permissionsettings for a closed group
	*/
	function getGrpStatusClosedTemplateId()
	{
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_closed'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		return $row["obj_id"];
	}

	/**
	* get group status open template
	* @access	public
	* @param	return obj_id of roletemplate containing permissionsettings for an open group
	*/
	function getGrpStatusOpenTemplateId()
	{
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_open'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		return $row["obj_id"];
	}

	/**
	* set Expiration Date and Time
	* @access	public
	* @param	date
	*/
	function setExpirationDateTime($a_date)
	{
		global $ilDB;

		$q = "SELECT * FROM grp_data WHERE grp_id= ".
			$ilDB->quote($this->getId());
		$res = $this->ilias->db->query($q);
		$date = ilFormat::input2date($a_date);

		if ($res->numRows() == 0)
		{
			$q = "INSERT INTO grp_data (grp_id, expiration) VALUES(".
				$ilDB->quote($this->getId()).",".$ilDB->quote($date).")";
			$res = $this->ilias->db->query($q);
		}
		else
		{
			$q = "UPDATE grp_data SET expiration=".
				$ilDB->quote($date)." WHERE grp_id=".$ilDB->quote($this->getId());
			$res = $this->ilias->db->query($q);
		}
	}

	/**
	 * Get expiration
	 *
	 * @access public
	 * @param
	 *
	 */
	public function getExpiration()
	{
		global $ilDB;

		$q = "SELECT * FROM grp_data WHERE grp_id= ".
			$ilDB->quote($this->getId());
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $datetime = $row["expiration"];
	}


	function getExpirationTimestamp()
	{
		global $ilDB;

		$query = "SELECT UNIX_TIMESTAMP(expiration) as timest FROM grp_data WHERE grp_id = ".
			$ilDB->quote($this->getId());

		$res = $this->ilias->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['timest'];
	}

	
	/**
	 * Change group type
	 * 
	 * Revokes permissions of all parent non-protected roles 
	 * and initiates these roles with the according il_grp_(open|closed) template.
	 *
	 * @access public
	 * @return
	 */
	public function updateGroupType()
	{
		global $tree,$rbacreview,$rbacadmin;
		
		$parent_roles = $rbacreview->getParentRoleIds($this->getRefId());
		$real_parent_roles = array_diff(array_keys($parent_roles),$this->getDefaultGroupRoles());
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());
		
		// Delete parent roles with stopped inheritance
		foreach($real_parent_roles as $role_id)
		{
			// Delete local role
			if(isset($rolf_data['child']) and $rolf_data['child'])
			{
				$rbacadmin->deleteLocalRole($role_id,$rolf_data['child']);
			}
		}
		$parent_roles = $rbacreview->getParentRoleIds($this->getRefId());
		$real_parent_roles = array_diff(array_keys($parent_roles),$this->getDefaultGroupRoles());
		
		switch($this->getGroupType())
		{
			case GRP_TYPE_PUBLIC:
				$template_id = $this->getGrpStatusOpenTemplateId();
				break;
				
			case GRP_TYPE_CLOSED:
				$template_id = $this->getGrpStatusClosedTemplateId();
				break;
		}
		
		$first = true;
		foreach($tree->getFilteredSubTree($this->getRefId(),array('rolf','grp')) as $subnode)
		{
			// Read template operations
			$template_ops = $rbacreview->getOperationsOfRole($template_id,$subnode['type'], ROLE_FOLDER_ID);
			
			$rolf_data = $rbacreview->getRoleFolderOfObject($subnode['child']);
			
			
			// for all parent roles
			foreach($real_parent_roles as $role_id)
			{
				if($rbacreview->isProtected($parent_roles[$role_id]['parent'],$role_id))
				{
					continue;
				}

				// Delete local role
				if(isset($rolf_data['child']) and $rolf_data['child'])
				{
					$rbacadmin->deleteLocalRole($role_id,$rolf_data['child']);
				}
				
				// Store current operations
				$current_ops = $rbacreview->getOperationsOfRole($role_id,$subnode['type'],$parent_roles[$role_id]['parent']);

				// Revoke permissions
				$rbacadmin->revokePermission($subnode['child'],$role_id);

				// Grant permissions
				$granted = array();
				foreach($template_ops as $operation)
				{
					if(in_array($operation,$current_ops))
					{
						$granted[] = $operation;
					}
				}
				if($granted)
				{
					$rbacadmin->grantPermission($role_id, $granted,$subnode['child']);
				}
				
				if($first)
				{
					// This is the group itself
					$rbacadmin->copyRolePermissionIntersection(
						$template_id, ROLE_FOLDER_ID,
						$role_id, $parent_roles[$role_id]['parent'],
						$rolf_data["child"],$role_id);
					$rbacadmin->assignRoleToFolder($role_id,$rolf_data["child"],"n");
					
				}
			}
			$first = false;
		}
	}

	/**
	* set group status
	*
	* Grants permissions on the group object for all parent roles.
	* Each permission is granted by computing the intersection of the role
	* template il_grp_status_open/_closed and the permission template of
	* the parent role.
	*
	* Creates linked roles in the local role folder object for all
	* parent roles and initializes their permission templates.
	* Each permission template is initialized by computing the intersection
	* of the role template il_grp_status_open/_closed and the permission
	* template of the parent role.
	*
	* @access	public
	* @param	integer group status GRP_TYPE_PUBLIC or GRP_TYPE_CLOSED
	*/
	function initGroupStatus($a_grpStatus = GRP_TYPE_PUBLIC)
	{
		global $rbacadmin, $rbacreview, $rbacsystem;

		//get Rolefolder of group
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());

		//define all relevant roles that rights are needed to be changed
		$arr_parentRoles = $rbacreview->getParentRoleIds($this->getRefId());
		$arr_relevantParentRoleIds = array_diff(array_keys($arr_parentRoles),$this->getDefaultGroupRoles());

		//group status open (aka public) or group status closed
		if ($a_grpStatus == GRP_TYPE_PUBLIC || $a_grpStatus == GRP_TYPE_CLOSED)
		{
			if ($a_grpStatus == GRP_TYPE_PUBLIC)
			{
				$template_id = $this->getGrpStatusOpenTemplateId();
			} 
			else 
			{
				$template_id = $this->getGrpStatusClosedTemplateId();
			}
			//get defined operations from template
			$template_ops = $rbacreview->getOperationsOfRole($template_id, 'grp', ROLE_FOLDER_ID);

			foreach ($arr_relevantParentRoleIds as $parentRole)
			{
				if ($rbacreview->isProtected($arr_parentRoles[$parentRole]['parent'],$parentRole))
				{
					continue;
				}

				$granted_permissions = array();

				// Delete the linked role for the parent role
				// (just in case if it already exists).
				
				// Added additional check, since this operation is very dangerous.
				// If there is no role folder ALL parent roles are deleted. 
				if(isset($rolf_data['child']) and $rolf_data['child'])
				{
					$rbacadmin->deleteLocalRole($parentRole,$rolf_data["child"]);
				}

				// Grant permissions on the group object for
				// the parent role. In the foreach loop we
				// compute the intersection of the role
				// template il_grp_status_open/_closed and the
				// permission template of the parent role.
				$current_ops = $rbacreview->getRoleOperationsOnObject($parentRole, $this->getRefId());
				$rbacadmin->revokePermission($this->getRefId(), $parentRole);
				foreach ($template_ops as $template_op)
				{
					if (in_array($template_op,$current_ops))
					{
						array_push($granted_permissions,$template_op);
					}
				}
				if (!empty($granted_permissions))
				{
					$rbacadmin->grantPermission($parentRole, $granted_permissions, $this->getRefId());
				}

				// Create a linked role for the parent role and
				// initialize it with the intersection of
				// il_grp_status_open/_closed and the permission
				// template of the parent role
				$rbacadmin->copyRolePermissionIntersection(
					$template_id, ROLE_FOLDER_ID,
					$parentRole, $arr_parentRoles[$parentRole]['parent'],
					$rolf_data["child"], $parentRole
				);
				$rbacadmin->assignRoleToFolder($parentRole,$rolf_data["child"],"false");
			}//END foreach
		}
	}

	/**
	 * Set group status
	 *
	 * @access public
	 * @param int group status[0=public|2=closed]
	 *
	 */
	public function setGroupStatus($a_status)
	{
		$this->group_status = $a_status;
	}

	/**
	 * get group status
	 *
	 * @access public
	 * @param int group status
	 *
	 */
	public function getGroupStatus()
	{
	 	return $this->group_status;
	}

	/**
	* get group status, redundant method because
	* @access	public
	* @param	return group status[0=public|2=closed]
	*/
	function readGroupStatus()
	{
		global $rbacsystem,$rbacreview;

		$role_folder = $rbacreview->getRoleFolderOfObject($this->getRefId());
		$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);

		//get Rolefolder of group
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->getRefId());
		//get all relevant roles
		$arr_globalRoles = array_diff($local_roles, $this->getDefaultGroupRoles());

		//if one global role has no permission to join the group is officially closed !
		foreach ($arr_globalRoles as $globalRole)
		{
			$ops_of_role = $rbacreview->getOperationsOfRole($globalRole,"grp", ROLE_FOLDER_ID);

			if ($rbacsystem->checkPermission($this->getRefId(), $globalRole ,"join"))
			{
				return $this->group_status = GRP_TYPE_PUBLIC;
			}
		}

		return $this->group_status = GRP_TYPE_CLOSED;
	}

	/**
	* get group member status
	* @access	public
	* @param	integer	user_id
	* @return	returns array of obj_ids of assigned local roles
	*/
	function getMemberRoles($a_user_id)
	{
		global $rbacadmin, $rbacreview,$ilBench;

		$ilBench->start("Group", "getMemberRoles");

		$arr_assignedRoles = array();

		$arr_assignedRoles = array_intersect($rbacreview->assignedRoles($a_user_id),$this->getLocalGroupRoles());

		$ilBench->stop("Group", "getMemberRoles");

		return $arr_assignedRoles;
	}

	/**
	* is Admin
	* @access	public
	* @param	integer	user_id
	* @param	boolean, true if user is group administrator
	*/
	function isAdmin($a_userId)
	{
		global $rbacreview;

		$grp_Roles = $this->getDefaultGroupRoles();

		if (in_array($a_userId,$rbacreview->assignedUsers($grp_Roles["grp_admin_role"])))
		{
			return true;
		}
		else
		{
			return false;
		}
	}



	/**
	* init default roles settings
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin, $rbacreview;

		// create a local role folder
		$rfoldObj =& $this->createRoleFolder();

		// ADMIN ROLE
		// create role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_grp_admin_".$this->getRefId(),"Groupadmin of group obj_no.".$this->getId());
		$this->m_roleAdminId = $roleObj->getId();

		//set permission template of new local role
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_admin'";
		$r = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRoleTemplatePermissions($r->obj_id,ROLE_FOLDER_ID,$rfoldObj->getRefId(),$roleObj->getId());

		// set object permissions of group object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"grp",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$this->getRefId());

		// set object permissions of role folder object
		//$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"rolf",$rfoldObj->getRefId());
		//$rbacadmin->grantPermission($roleObj->getId(),$ops,$rfoldObj->getRefId());

		// MEMBER ROLE
		// create role and assign role to rolefolder...
		$roleObj = $rfoldObj->createRole("il_grp_member_".$this->getRefId(),"Groupmember of group obj_no.".$this->getId());
		$this->m_roleMemberId = $roleObj->getId();

		//set permission template of new local role
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_member'";
		$r = $this->ilias->db->getRow($q, DB_FETCHMODE_OBJECT);
		$rbacadmin->copyRoleTemplatePermissions($r->obj_id,ROLE_FOLDER_ID,$rfoldObj->getRefId(),$roleObj->getId());

		// set object permissions of group object
		$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"grp",$rfoldObj->getRefId());
		$rbacadmin->grantPermission($roleObj->getId(),$ops,$this->getRefId());

		// set object permissions of role folder object
		//$ops = $rbacreview->getOperationsOfRole($roleObj->getId(),"rolf",$rfoldObj->getRefId());
		//$rbacadmin->grantPermission($roleObj->getId(),$ops,$rfoldObj->getRefId());

		unset($rfoldObj);
		unset($roleObj);

		$roles[] = $this->m_roleAdminId;
		$roles[] = $this->m_roleMemberId;

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	*
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;

		$parent_id = (int) $tree->getParentId($a_node_id);

		if ($parent_id != 0)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$obj_data->notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$parent_id,$a_params);
		}

		return true;
	}


	function exportXML()
	{
		include_once 'Modules/Group/classes/class.ilGroupXMLWriter.php';

		$xml_writer = new ilGroupXMLWriter($this);
		$xml_writer->start();

		$xml = $xml_writer->getXML();
		
		$name = time().'__'.$this->ilias->getSetting('inst_id').'__grp_'.$this->getId();

		$this->__initFileObject();

		$this->file_obj->addGroupDirectory();
		$this->file_obj->addDirectory($name);
		$this->file_obj->writeToFile($xml,$name.'/'.$name.'.xml');
		$this->file_obj->zipFile($name,$name.'.zip');
		$this->file_obj->deleteDirectory($name);

		return true;
	}

	function deleteExportFiles($a_files)
	{
		$this->__initFileObject();

		foreach($a_files as $file)
		{
			$this->file_obj->deleteFile($file);
		}
		return true;
	}

	function downloadExportFile($file)
	{
		$this->__initFileObject();

		if($abs_name = $this->file_obj->getExportFile($file))
		{
			ilUtil::deliverFile($abs_name,$file);
			// Not reached
		}
		return false;
	}

	/**
	 * Static used for importing a group from xml string
	 *
	 * @param	xml string
	 * @static
	 * @access	public
	 */

	function _importFromXMLString($xml,$parent_id)
	{
		include_once 'Modules/Group/classes/class.ilGroupXMLParser.php';

		$import_parser = new ilGroupXMLParser($xml,$parent_id);

		return $import_parser->startParsing();
	}

	/**
	 * Static used for importing an group from xml zip file
	 *
	 * @param	xml file array structure like $_FILE from upload
	 * @static
	 * @access	public
	 */
	function _importFromFile($file,$parent_id)
	{
		global $lng;

		include_once 'classes/class.ilFileDataGroup.php';

		$file_obj = new ilFileDataGroup(null);
		$file_obj->addImportDirectory();
		$file_obj->createImportFile($_FILES["xmldoc"]["tmp_name"],$_FILES['xmldoc']['name']);
		$file_obj->unpackImportFile();

		if(!$file_obj->validateImportFile())
		{
			return false;
		}
		return ilObjGroup::_importFromXMLString(file_get_contents($file_obj->getImportFile()),$parent_id);
	}

	/**
	 * STATIC METHOD
	 * search for group data. This method is called from class.ilSearch
	 * This method used by class.ilSearchGUI.php to a link to the results
	 * @param	object object of search class
	 * @static
	 * @access	public
	 */
	function _search(&$a_search_obj)
	{
		global $ilBench;

		// NO CLASS VARIABLES IN STATIC METHODS

		$where_condition = $a_search_obj->getWhereCondition("like",array("title","description"));
		$in = $a_search_obj->getInStatement("ore.ref_id");

		$query = "SELECT ore.ref_id ref_id FROM object_data od, object_reference ore ".
			$where_condition." ".
			$in." ".
			"AND od.obj_id = ore.obj_id ".
			"AND od.type = 'grp' ";

		$ilBench->start("Search", "ilObjGroup_search");
		$res = $a_search_obj->ilias->db->query($query);
		$ilBench->stop("Search", "ilObjGroup_search");

		$counter = 0;

		while ($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_data[$counter++]["id"]				=  $row->ref_id;
			#$result_data[$counter]["link"]				=  "group.php?cmd=view&ref_id=".$row->ref_id;
			#$result_data[$counter++]["target"]			=  "";
		}

		return $result_data ? $result_data : array();
	}

	/**
	 * STATIC METHOD
	 * create a link to the object
	 * @param	int uniq id
	 * @return array array('link','target')
	 * @static
	 * @access	public
	 */
	function _getLinkToObject($a_id)
	{
		return array("repository.php?ref_id=".$a_id."&set_mode=flat&cmdClass=ilobjgroupgui","");
	}

	function _lookupIdByTitle($a_title)
	{
		global $ilDB;

		$query = "SELECT * FROM object_data WHERE title = ".
			$ilDB->quote($a_title)." AND type = 'grp'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}
		return 0;
	}


	function _isMember($a_user_id,$a_ref_id,$a_field = '')
	{
		global $rbacreview,$ilObjDataCache,$ilDB;

		$rolf = $rbacreview->getRoleFolderOfObject($a_ref_id);
		$local_roles = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"],false);
		$user_roles = $rbacreview->assignedRoles($a_user_id);

		// Used for membership limitations -> check membership by given field
		if($a_field)
		{
			include_once './Services/User/classes/class.ilObjUser.php';

			$tmp_user =& ilObjectFactory::getInstanceByObjId($a_user_id);
			switch($a_field)
			{
				case 'login':
					$and = "AND login = '".$tmp_user->getLogin()."' ";
					break;
				case 'email':
					$and = "AND email = '".$tmp_user->getEmail()."' ";
					break;
				case 'matriculation':
					$and = "AND matriculation = '".$tmp_user->getMatriculation()."' ";
					break;

				default:
					$and = "AND usr_id = '".$a_user_id."'";
					break;
			}
			if(!$members = ilObjGroup::_getMembers($ilObjDataCache->lookupObjId($a_ref_id)))
			{
				return false;
			}
			$query = "SELECT * FROM usr_data as ud ".
				"WHERE usr_id IN (".implode(",",ilUtil::quoteArray($members)).") ".
				$and;
			$res = $ilDB->query($query);

			return $res->numRows() ? true : false;
		}

		if (!array_intersect($local_roles,$user_roles))
		{
			return false;
		}

		return true;
	}

	function _getMembers($a_obj_id)
	{
		global $rbacreview;

		// get reference
		$ref_ids = ilObject::_getAllReferences($a_obj_id);
		$ref_id = current($ref_ids);

		$rolf = $rbacreview->getRoleFolderOfObject($ref_id);
		$local_roles = $rbacreview->getRolesOfRoleFolder($rolf['ref_id'],false);

		$users = array();
		foreach($local_roles as $role_id)
		{
			$users = array_merge($users,$rbacreview->assignedUsers($role_id));
		}

		return array_unique($users);
	}
	
	/**
	 * get view mode
	 *
	 * @access public
	 * @return int view mode
	 */
	public function getViewMode()
	{
		global $tree;
		
		// default: by type
		$view = ilContainer::VIEW_BY_TYPE;
		
		if ($course_ref_id = $tree->checkForParentType($this->ref_id,'crs'))
		{
			include_once("./Modules/Course/classes/class.ilObjCourse.php");
			$view_mode = ilObjCourse::_lookupViewMode(
				ilObject::_lookupObjId($course_ref_id));
			if ($view_mode == ilContainer::VIEW_SESSIONS ||
				$view_mode == ilContainer::VIEW_BY_TYPE ||
				$view_mode == ilContainer::VIEW_SIMPLE)
			{
				$view = $view_mode;
			}
		}
		return $view;
	}
	
	/**
	* Add additional information to sub item, e.g. used in
	* courses for timings information etc.
	*/
	function addAdditionalSubItemInformation(&$a_item_data)
	{
		global $tree;
		
		static $items = null;
		
		if(!is_object($items[$this->getRefId()]))
		{
			if ($course_ref_id = $tree->checkForParentType($this->getRefId(),'crs'))
			{
				include_once("./Modules/Course/classes/class.ilObjCourse.php");
				include_once("./Modules/Course/classes/class.ilCourseItems.php");
				$course_obj = new ilObjCourse($course_ref_id);
				$items[$this->getRefId()] = new ilCourseItems($course_obj, $this->getRefId());
			}
		}
		if(is_object($items[$this->getRefId()]))
		{
			$items[$this->getRefId()]->addAdditionalSubItemInformation($a_item_data);
		}
	}
	

	// Private / Protected
	function __initFileObject()
	{
		if($this->file_obj)
		{
			return $this->file_obj;
		}
		else
		{
			include_once 'classes/class.ilFileDataGroup.php';

			return $this->file_obj = new ilFileDataGroup($this);
		}
	}

	function getMessage()
	{
		return $this->message;
	}
	function setMessage($a_message)
	{
		$this->message = $a_message;
	}
	function appendMessage($a_message)
	{
		if($this->getMessage())
		{
			$this->message .= "<br /> ";
		}
		$this->message .= $a_message;
	}
	
	/**
	 * Prepare calendar appointments
	 *
	 * @access protected
	 * @param string mode UPDATE|CREATE|DELETE
	 * @return
	 */
	protected function prepareAppointments($a_mode = 'create')
	{
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php');
		
		switch($a_mode)
		{
			case 'create':
			case 'update':
				if($this->isRegistrationUnlimited())
				{
					return array();
				}
				$app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
				$app->setTitle($this->getTitle());
				$app->setSubtitle('grp_cal_reg_start');
				$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$app->setDescription($this->getLongDescription());	
				$app->setStart($this->getRegistrationStart());
				$apps[] = $app;

				$app = new ilCalendarAppointmentTemplate(self::CAL_REG_END);
				$app->setTitle($this->getTitle());
				$app->setSubtitle('grp_cal_reg_end');
				$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
				$app->setDescription($this->getLongDescription());
				$app->setStart($this->getRegistrationEnd());
				$apps[] = $app;
				
				return $apps;
				
			case 'delete':
				// Nothing to do: The category and all assigned appointments will be deleted.
				return array();
		}
	}
	
	/**
	 * init participants object
	 * 
	 *
	 * @access protected
	 * @return
	 */
	protected function initParticipants()
	{
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$this->members_obj = ilGroupParticipants::_getInstanceByObjId($this->getId());
	}
	
} //END class.ilObjGroup
?>
