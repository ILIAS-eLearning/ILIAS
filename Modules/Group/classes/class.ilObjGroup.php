<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

//TODO: function getRoleId($groupRole) returns the object-id of grouprole

require_once "./Services/Container/classes/class.ilContainer.php";
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once './Services/Membership/interfaces/interface.ilMembershipRegistrationCodes.php';

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
class ilObjGroup extends ilContainer implements ilMembershipRegistrationCodes
{
	const CAL_REG_START = 1;
	const CAL_REG_END 	= 2;
	const CAL_START		= 3;
	const CAL_END		= 4;
	
	const GRP_MEMBER = 1;
	const GRP_ADMIN = 2;
	
	const ERR_MISSING_TITLE = 'msg_no_title';
	const ERR_MISSING_GROUP_TYPE = 'grp_missing_grp_type';
	const ERR_MISSING_PASSWORD = 'grp_missing_password';
	const ERR_WRONG_MAX_MEMBERS = 'grp_wrong_max_members';
	const ERR_WRONG_REG_TIME_LIMIT = 'grp_wrong_reg_time_limit';
	const ERR_MISSING_MIN_MAX_MEMBERS = 'grp_wrong_min_max_members';
	const ERR_WRONG_MIN_MAX_MEMBERS = 'grp_max_and_min_members_invalid';
	const ERR_WRONG_REGISTRATION_LIMITED = 'grp_err_registration_limited';
	
	const MAIL_ALLOWED_ALL = 1;
	const MAIL_ALLOWED_TUTORS = 2;

	public $SHOW_MEMBERS_ENABLED = 1;
	public $SHOW_MEMBERS_DISABLED = 0;
	
	protected $information;
	protected $group_type = null;
	protected $reg_type = GRP_REGISTRATION_DIRECT;
	protected $reg_enabled = true;
	protected $reg_unlimited = true;
	protected $reg_start = null;
	protected $reg_end = null;
	protected $reg_password = '';
	protected $reg_membership_limitation = false;
	protected $reg_min_members = 0;
	protected $reg_max_members = 0;
	protected $waiting_list = false;
	protected $auto_fill_from_waiting; // [bool]
	protected $leave_end; // [ilDate]
	protected $show_members = 1;
	
	
	protected $start = null;
	protected $end = null;
	
	
	// Map
	private $latitude = '';
	private $longitude = '';
	private $locationzoom = 0;
	private $enablemap = 0;
	
	private $reg_access_code = '';
	private $reg_access_code_enabled = false;

	private $view_mode = NULL;
	
	private $mail_members = self::MAIL_ALLOWED_ALL;
	
	
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
		parent::__construct($a_id,$a_call_by_reference);
		$this->setRegisterMode(true); // ???
	}
	
	/**
	 * Lookup group type
	 * @param object $a_id
	 * @return 
	 */
	public static function lookupGroupTye($a_id)
	{
		global $ilDB;
		
		$query = "SELECT grp_type FROM grp_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			return $row->grp_type;
		}
		return GRP_TYPE_UNKNOWN;
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
		return $this->reg_start;
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
		return $this->reg_end;
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
	 * set min members
	 *
	 * @access public
	 * @param int min members
	 */
	public function setMinMembers($a_max)
	{
		$this->reg_min_members = $a_max;
	}
	
	/**
	 * get min members
	 *
	 * @access public
	 * @return
	 */
	public function getMinMembers()
	{
		return $this->reg_min_members;
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
	
	function setWaitingListAutoFill($a_value)
	{
		$this->auto_fill_from_waiting = (bool)$a_value;
	}
	
	function hasWaitingListAutoFill()
	{
		return (bool)$this->auto_fill_from_waiting;
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
	 * Type independent wrapper
	 * @return type
	 */
	public function getEnableMap()
	{
		return $this->getEnableGroupMap();
	}
	
	/**
	* Get Enable Group Map.
	*
	* @return	boolean	Enable Group Map
	*/
	function getEnableGroupMap()
	{
		return (bool) $this->enablemap;
	}
	
		/**
	 * get access code
	 * @return 
	 */
	public function getRegistrationAccessCode()
	{
		return $this->reg_access_code;
	}
	
	/**
	 * Set refistration access code
	 * @param string $a_code
	 * @return 
	 */
	public function setRegistrationAccessCode($a_code)
	{
		$this->reg_access_code = $a_code;
	}
	
	/**
	 * Check if access code is enabled
	 * @return 
	 */
	public function isRegistrationAccessCodeEnabled()
	{
		return (bool) $this->reg_access_code_enabled;
	}
	
	/**
	 * En/disable registration access code
	 * @param object $a_status
	 * @return 
	 */
	public function enableRegistrationAccessCode($a_status)
	{
		$this->reg_access_code_enabled = $a_status;
	}
	
	/**
	 * Set mail to members type
	 * @see ilCourseConstants
	 * @param type $a_type
	 */
	public function setMailToMembersType($a_type)
	{
		$this->mail_members = $a_type;
	}
	
	/**
	 * Get mail to members type
	 * @return int
	 */
	public function getMailToMembersType()
	{
		return $this->mail_members;
	}
	
	function setCancellationEnd(ilDate $a_value = null)
	{		
		$this->leave_end = $a_value;
	}
	
	function getCancellationEnd()
	{		
		return $this->leave_end;
	}

	function setShowMembers($a_status)
	{
		$this->show_members = $a_status;
	}
	function getShowMembers()
	{
		return $this->show_members;
	}
	
	/**
	 * Get group start
	 * @return ilDate
	 */
	public function getStart()
	{
		return $this->start;
	}
	
	/**
	 * Set start
	 * @param ilDate $start
	 */
	public function setStart(ilDate $start = null)
	{
		$this->start = $start;
	}
	
	/**
	 * Get end
	 * @return ilDate
	 */
	public function getEnd()
	{
		return $this->end;
	}
	
	/**
	 * Set end
	 * @param ilDate $end
	 */
	public function setEnd(ilDate $end = null)
	{
		$this->end = $end;
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
		
		if(!$this->getTitle())
		{
			$this->title = '';
			$ilErr->appendMessage($this->lng->txt(self::ERR_MISSING_TITLE));
		}
		if($this->getRegistrationType() == GRP_REGISTRATION_PASSWORD and !strlen($this->getPassword()))
		{
			$ilErr->appendMessage($this->lng->txt(self::ERR_MISSING_PASSWORD));
		}
		/*
		if(ilDateTime::_before($this->getRegistrationEnd(),$this->getRegistrationStart()))
		{
			$ilErr->appendMessage($this->lng->txt(self::ERR_WRONG_REG_TIME_LIMIT));
		}		 
		*/
		if($this->isMembershipLimited())
		{
			if($this->getMinMembers() <= 0 && $this->getMaxMembers() <= 0)
			{
				$ilErr->appendMessage($this->lng->txt(self::ERR_MISSING_MIN_MAX_MEMBERS));
			}
			if($this->getMaxMembers() <= 0 && $this->isWaitingListEnabled())
			{
				$ilErr->appendMessage($this->lng->txt(self::ERR_WRONG_MAX_MEMBERS));
			}
			if($this->getMaxMembers() > 0 && $this->getMinMembers() > $this->getMaxMembers())
			{
				$ilErr->appendMessage($this->lng->txt(self::ERR_WRONG_MIN_MAX_MEMBERS));
			}
		}
		if(
			($this->getRegistrationStart() && !$this->getRegistrationEnd()) ||
			(!$this->getRegistrationStart() && $this->getRegistrationEnd()) ||
			$this->getRegistrationEnd() <= $this->getRegistrationStart()
		)
		{
			$ilErr->appendMessage($this->lng->txt((self::ERR_WRONG_REGISTRATION_LIMITED)));
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

		$query = "INSERT INTO grp_settings (obj_id,information,grp_type,registration_type,registration_enabled,".
			"registration_unlimited,registration_start,registration_end,registration_password,registration_mem_limit,".
			"registration_max_members,waiting_list,latitude,longitude,location_zoom,enablemap,reg_ac_enabled,reg_ac,view_mode,mail_members_type,".
			"leave_end,registration_min_members,auto_wait, grp_start, grp_end) ".
			"VALUES(".
			$ilDB->quote($this->getId() ,'integer').", ".
			$ilDB->quote($this->getInformation() ,'text').", ".
			$ilDB->quote((int) $this->getGroupType() ,'integer').", ".
			$ilDB->quote($this->getRegistrationType() ,'integer').", ".
			$ilDB->quote(($this->isRegistrationEnabled() ? 1 : 0) ,'integer').", ".
			$ilDB->quote(($this->isRegistrationUnlimited() ? 1 : 0) ,'integer').", ".
			$ilDB->quote(($this->getRegistrationStart() && !$this->getRegistrationStart()->isNull()) ? $this->getRegistrationStart()->get(IL_CAL_DATETIME,'') : null,'timestamp').", ".
			$ilDB->quote(($this->getRegistrationEnd() && !$this->getRegistrationEnd()->isNull()) ? $this->getRegistrationEnd()->get(IL_CAL_DATETIME,'') : null,'timestamp').", ".
			$ilDB->quote($this->getPassword() ,'text').", ".
			$ilDB->quote((int) $this->isMembershipLimited() ,'integer').", ".
			$ilDB->quote($this->getMaxMembers() ,'integer').", ".
			$ilDB->quote($this->isWaitingListEnabled() ? 1 : 0 ,'integer').", ".
			$ilDB->quote($this->getLatitude() ,'text').", ".
			$ilDB->quote($this->getLongitude() ,'text').", ".
			$ilDB->quote($this->getLocationZoom() ,'integer').", ".
			$ilDB->quote((int) $this->getEnableGroupMap() ,'integer').", ".
			$ilDB->quote($this->isRegistrationAccessCodeEnabled(),'integer').', '.
			$ilDB->quote($this->getRegistrationAccessCode(),'text').', '.
			$ilDB->quote($this->getViewMode(),'integer').', '.
			$ilDB->quote($this->getMailToMembersType(),'integer').', '.				
			$ilDB->quote(($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null, 'integer').', '.			
			$ilDB->quote($this->getMinMembers(),'integer').', '.
			$ilDB->quote($this->hasWaitingListAutoFill(),'integer').', '.
			$ilDB->quote($this->getStart() instanceof ilDate ? $this->getStart()->get(IL_CAL_UNIX) : null, 'integer').', '.
			$ilDB->quote($this->getEnd() instanceof ilDate ? $this->getEnd()->get(IL_CAL_UNIX) : null, 'integer').' '.
			")";
		$res = $ilDB->manipulate($query);

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
			"SET information = ".$ilDB->quote($this->getInformation() ,'text').", ".
			"grp_type = ".$ilDB->quote((int) $this->getGroupType() ,'integer').", ".
			"registration_type = ".$ilDB->quote($this->getRegistrationType() ,'integer').", ".
			"registration_enabled = ".$ilDB->quote($this->isRegistrationEnabled() ? 1 : 0 ,'integer').", ".
			"registration_unlimited = ".$ilDB->quote($this->isRegistrationUnlimited() ? 1 : 0 ,'integer').", ".
			"registration_start = ".$ilDB->quote(($this->getRegistrationStart() && !$this->getRegistrationStart()->isNull()) ? $this->getRegistrationStart()->get(IL_CAL_DATETIME,'') : null,'timestamp').", ".
			"registration_end = ".$ilDB->quote(($this->getRegistrationEnd() && !$this->getRegistrationEnd()->isNull()) ? $this->getRegistrationEnd()->get(IL_CAL_DATETIME,'') : null,'timestamp').", ".
			"registration_password = ".$ilDB->quote($this->getPassword() ,'text').", ".
//			"registration_membership_limited = ".$ilDB->quote((int) $this->isMembershipLimited() ,'integer').", ".
			"registration_mem_limit = ".$ilDB->quote((int) $this->isMembershipLimited() ,'integer').", ".
			"registration_max_members = ".$ilDB->quote($this->getMaxMembers() ,'integer').", ".
			"waiting_list = ".$ilDB->quote($this->isWaitingListEnabled() ? 1 : 0 ,'integer').", ".
			"latitude = ".$ilDB->quote($this->getLatitude() ,'text').", ".
			"longitude = ".$ilDB->quote($this->getLongitude() ,'text').", ".
			"location_zoom = ".$ilDB->quote($this->getLocationZoom() ,'integer').", ".
			"enablemap = ".$ilDB->quote((int) $this->getEnableGroupMap() ,'integer').", ".
			'reg_ac_enabled = '.$ilDB->quote($this->isRegistrationAccessCodeEnabled(),'integer').', '.
			'reg_ac = '.$ilDB->quote($this->getRegistrationAccessCode(),'text').', '.
			'view_mode = '.$ilDB->quote($this->getViewMode(),'integer').', '.
			'mail_members_type = '.$ilDB->quote($this->getMailToMembersType(),'integer').', '.				
			'leave_end = '.$ilDB->quote(($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null, 'integer').', '.			
			"registration_min_members = ".$ilDB->quote($this->getMinMembers() ,'integer').", ".
			"auto_wait = ".$ilDB->quote($this->hasWaitingListAutoFill() ,'integer').", ".
			"show_members = ".$ilDB->quote((int) $this->getShowMembers() ,'integer').", ".
			'grp_start = '.$ilDB->quote($this->getStart() instanceof ilDate ? $this->getStart()->get(IL_CAL_UNIX) : null).', '.
			'grp_end = '.$ilDB->quote($this->getEnd() instanceof ilDate ? $this->getEnd()->get(IL_CAL_UNIX) : null).' '.
			"WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer');
		$res = $ilDB->manipulate($query);
		
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
			"WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer');
		$res = $ilDB->manipulate($query);
		
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
			"WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer');
		
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->setInformation($row->information);
			$this->setGroupType($row->grp_type);
			$this->setRegistrationType($row->registration_type);
			$this->enableUnlimitedRegistration($row->registration_unlimited);
			$this->setRegistrationStart(new ilDateTime($row->registration_start,IL_CAL_DATETIME));
			$this->setRegistrationEnd(new ilDateTime($row->registration_end,IL_CAL_DATETIME));
			$this->setPassword($row->registration_password);
			$this->enableMembershipLimitation((bool) $row->registration_mem_limit);
			$this->setMaxMembers($row->registration_max_members);
			$this->enableWaitingList($row->waiting_list);
			$this->setLatitude($row->latitude);
			$this->setLongitude($row->longitude);
			$this->setLocationZoom($row->location_zoom);
			$this->setEnableGroupMap($row->enablemap);
			$this->enableRegistrationAccessCode($row->reg_ac_enabled);
			$this->setRegistrationAccessCode($row->reg_ac);
			$this->setViewMode($row->view_mode);
			$this->setMailToMembersType($row->mail_members_type);			
			$this->setCancellationEnd($row->leave_end ? new ilDate($row->leave_end, IL_CAL_UNIX) : null);
			$this->setMinMembers($row->registration_min_members);
			$this->setWaitingListAutoFill($row->auto_wait);
			$this->setShowMembers($row->show_members);
			$this->setStart($row->grp_start ? new ilDate($row->grp_start, IL_CAL_UNIX) : null);
			$this->setEnd($row->grp_end ? new ilDate($row->grp_end, IL_CAL_UNIX) : null);
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
	public function cloneObject($a_target_id,$a_copy_id = 0, $a_omit_tree = false)
	{
		global $ilDB,$ilUser, $ilSetting;

	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id, $a_omit_tree);
	 	$new_obj->setGroupType($this->getGroupType());
	 	$new_obj->initGroupStatus($this->getGroupType() ? $this->getGroupType() : $this->readGroupStatus());

	 	$this->cloneAutoGeneratedRoles($new_obj);

		$new_obj->setRegistrationType($this->getRegistrationType());
		$new_obj->setInformation($this->getInformation());
		$new_obj->setRegistrationStart($this->getRegistrationStart());
		$new_obj->setRegistrationEnd($this->getRegistrationEnd());
		$new_obj->enableUnlimitedRegistration($this->isRegistrationUnlimited());
		$new_obj->setPassword($this->getPassword());
		$new_obj->enableMembershipLimitation($this->isMembershipLimited());
		$new_obj->setMaxMembers($this->getMaxMembers());
		$new_obj->enableWaitingList($this->isWaitingListEnabled());
		$new_obj->setShowMembers($this->getShowMembers());

		// map
		$new_obj->setLatitude($this->getLatitude());
		$new_obj->setLongitude($this->getLongitude());
		$new_obj->setLocationZoom($this->getLocationZoom());
		$new_obj->setEnableGroupMap($this->getEnableGroupMap());
		$new_obj->enableRegistrationAccessCode($this->isRegistrationAccessCodeEnabled());
		include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
		$new_obj->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());

		$new_obj->setViewMode($this->getViewMode());
		$new_obj->setMailToMembersType($this->getMailToMembersType());
		
		$new_obj->setCancellationEnd($this->getCancellationEnd());
		$new_obj->setMinMembers($this->getMinMembers());
		$new_obj->setWaitingListAutoFill($this->hasWaitingListAutoFill());
		
		$new_obj->setStart($this->getStart());
		$new_obj->setEnd($this->getEnd());
		
		$new_obj->update();
		
		// #13008 - Group Defined Fields
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		ilCourseDefinedFieldDefinition::_clone($this->getId(),$new_obj->getId());
		
		// Assign user as admin
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$part = ilGroupParticipants::_getInstanceByObjId($new_obj->getId());
		$part->add($ilUser->getId(),IL_GRP_ADMIN);
		$part->updateNotification($ilUser->getId(),$ilSetting->get('mail_grp_admin_notification', true));

		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
		
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

		include_once('Services/Object/classes/class.ilObjectActivation.php');
		ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);

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
		global $rbacadmin,$rbacreview;

		$admin = $this->getDefaultAdminRole();
		$new_admin = $new_obj->getDefaultAdminRole();
		if(!$admin || !$new_admin || !$this->getRefId() || !$new_obj->getRefId())
		{
			ilLoggerFactory::getLogger('grp')->warning('Error cloning auto generated rol: il_grp_admin');
		}
		$rbacadmin->copyRolePermissions($admin,$this->getRefId(),$new_obj->getRefId(),$new_admin,true);
		ilLoggerFactory::getLogger('grp')->info('Finished copying of role il_grp_admin.');

		$member = $this->getDefaultMemberRole();
		$new_member = $new_obj->getDefaultMemberRole();
		if(!$member || !$new_member)
		{
			ilLoggerFactory::getLogger('grp')->warning('Error cloning auto generated rol: il_grp_member');
		}
		$rbacadmin->copyRolePermissions($member,$this->getRefId(),$new_obj->getRefId(),$new_member,true);
		ilLoggerFactory::getLogger('grp')->info('Finished copying of role il_grp_member.');
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
	public function getDefaultMemberRole()
	{
		$local_group_Roles = $this->getLocalGroupRoles();

		return $local_group_Roles["il_grp_member_".$this->getRefId()];
	}

	/**
	* returns object id of created default adminstrator role
	* @access	public
	*/
	public function getDefaultAdminRole()
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
  		
		while($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
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
	function getGroupAdminIds($a_grpId = "")
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

		$role_arr  = $rbacreview->getRolesOfRoleFolder($grp_id);

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
			$role_arr  = $rbacreview->getRolesOfRoleFolder($this->getRefId());

			foreach ($role_arr as $role_id)
			{
				if ($rbacreview->isAssignable($role_id,$this->getRefId()) == true)
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
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

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
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

		return $row["obj_id"];
	}
	
	/**
	 * 
	 * @global $ilDB $ilDB
	 * @param int $a_obj_id
	 * @return int
	 */
	public static function lookupGroupStatusTemplateId($a_obj_id)
	{
		global $ilDB;
		
		$type = self::lookupGroupTye($a_obj_id);
		if($type == GRP_TYPE_CLOSED)
		{
			$query = 'SELECT obj_id FROM object_data WHERE type = '.$ilDB->quote('rolt','text').' AND title = '.$ilDB->quote('il_grp_status_closed','text');
		}
		else
		{
			$query = 'SELECT obj_id FROM object_data WHERE type = '.$ilDB->quote('rolt','text').' AND title = '.$ilDB->quote('il_grp_status_open','text');
		}
		$res = $ilDB->query($query);
		$row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
		
		return isset($row['obj_id']) ? $row['obj_id'] : 0;
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
		
		// Delete parent roles with stopped inheritance
		foreach($real_parent_roles as $role_id)
		{
			// Delete local role
			$rbacadmin->deleteLocalRole($role_id,$this->getRefId());
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
		foreach($tree->getFilteredSubTree($this->getRefId(),array('grp')) as $subnode)
		{
			// Read template operations
			$template_ops = $rbacreview->getOperationsOfRole($template_id,$subnode['type'], ROLE_FOLDER_ID);
			
			// for all parent roles
			foreach($real_parent_roles as $role_id)
			{
				if($rbacreview->isProtected($parent_roles[$role_id]['parent'],$role_id))
				{
					continue;
				}

				$rbacadmin->deleteLocalRole($role_id,$subnode['child']);
				
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
						$subnode["child"],$role_id);
					$rbacadmin->assignRoleToFolder($role_id,$subnode['child'],"n");
					
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

		//define all relevant roles that rights are needed to be changed
		$arr_parentRoles = $rbacreview->getParentRoleIds($this->getRefId());

		$real_local_roles = $rbacreview->getRolesOfRoleFolder($this->getRefId(),false);
		$arr_relevantParentRoleIds = array_diff(array_keys($arr_parentRoles),$real_local_roles);

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

				// @todo refactor rolf
				$rbacadmin->deleteLocalRole($parentRole,$this->getRefId());

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
					$this->getRefId(), $parentRole
				);
				$rbacadmin->assignRoleToFolder($parentRole,$this->getRefId(),"false");
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

		$local_roles = $rbacreview->getRolesOfRoleFolder($this->getRefId());

		//get all relevant roles
		$arr_globalRoles = array_diff($local_roles, $this->getDefaultGroupRoles());

		//if one global role has no permission to join the group is officially closed !
		foreach ($arr_globalRoles as $globalRole)
		{
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
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role = ilObjRole::createDefaultRole(
				'il_grp_admin_'.$this->getRefId(),
				"Groupadmin group obj_no.".$this->getId(),
				'il_grp_admin',
				$this->getRefId()
		);
		$this->m_roleAdminId = $role->getId();
		
		$role = ilObjRole::createDefaultRole(
				'il_grp_member_'.$this->getRefId(),
				"Groupmember of group obj_no.".$this->getId(),
				'il_grp_member',
				$this->getRefId()
		);
		$this->m_roleMemberId = $role->getId();
		
		return array();
	}
	
	/**
	 * This method is called before "initDefaultRoles".
	 * Therefore no local group roles are created.
	 * 
	 * Grants permissions on the group object for all parent roles.
	 * Each permission is granted by computing the intersection of the 
	 * template il_grp_status and the permission template of the parent role.
	 * @param int parent ref id
	 */
	public function setParentRolePermissions($a_parent_ref)
	{
		$rbacadmin = $GLOBALS['DIC']->rbac()->admin();
		$rbacreview = $GLOBALS['DIC']->rbac()->review();
		
		$parent_roles = $rbacreview->getParentRoleIds($a_parent_ref);
		foreach((array) $parent_roles as $parent_role)
		{
			if($parent_role['parent'] == $this->getRefId())
			{
				continue;
			}
			if($rbacreview->isProtected($parent_role['parent'], $parent_role['rol_id']))
			{
				$operations = $rbacreview->getOperationsOfRole(
					$parent_role['obj_id'],
					$this->getType(),
					$parent_role['parent']
				);
				$rbacadmin->grantPermission(
					$parent_role['obj_id'],
					$operations,
					$this->getRefId()
				);
				continue;
			}

			$rbacadmin->initIntersectionPermissions(
				$this->getRefId(),
				$parent_role['obj_id'],
				$parent_role['parent'],
				$this->getGrpStatusOpenTemplateId(),
				ROLE_FOLDER_ID
			);
		}
	}
	
	
	/**
	 * Apply template
	 * @param int $a_tpl_id
	 */
	public function applyDidacticTemplate($a_tpl_id)
	{
		parent::applyDidacticTemplate($a_tpl_id);
		
		if(!$a_tpl_id)
		{
			// init default type
			$this->setParentRolePermissions($this->getRefId());
		}
		
	}
	

	public static function _lookupIdByTitle($a_title)
	{
		global $ilDB;

		$query = "SELECT * FROM object_data WHERE title = ".
			$ilDB->quote($a_title ,'text')." AND type = 'grp'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}
		return 0;
	}


	function _isMember($a_user_id,$a_ref_id,$a_field = '')
	{
		global $rbacreview,$ilObjDataCache,$ilDB;

		$local_roles = $rbacreview->getRolesOfRoleFolder($a_ref_id,false);
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

		$local_roles = $rbacreview->getRolesOfRoleFolder($ref_id,false);

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
	public function getViewMode($a_translate_inherit = true)
	{		
		$view = (int) $this->view_mode;
		
		if(!$view)
		{
			$view = ilContainer::VIEW_DEFAULT;
		}
		return $view;
	}

	/**
	 * Set group view mode
	 * @param int $a_view_mode
	 */
	public function setViewMode($a_view_mode)
	{		
		$this->view_mode = $a_view_mode;
	}

	/**
	 * lookup view mode
	 * @global $ilDB
	 */
	public static function lookupViewMode($a_obj_id)
	{
		global $ilDB;

		$query = 'SELECT view_mode FROM grp_settings '.
			'WHERE obj_id = '.$ilDB->quote($a_obj_id,'integer');
		$res = $ilDB->query($query);

		$view_mode = NULL;
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$view_mode = $row->view_mode;
		}
		return self::translateViewMode($a_obj_id,$view_mode);
	}
	
	/**
	 * translate view mode 
	 * @param int $a_obj_id
	 * @param int $a_view_mode
	 * @param int $a_ref_id
	 * @return int 
	 */
	public static function translateViewMode($a_obj_id,$a_view_mode,$a_ref_id = null)
	{
		global $tree;
		
		if(!$a_view_mode)
		{
			$a_view_mode = ilContainer::VIEW_DEFAULT;
		}
		
		// view mode is inherit => check for parent course
		if($a_view_mode == ilContainer::VIEW_INHERIT)
		{
			if(!$a_ref_id)
			{
				$ref = ilObject::_getAllReferences($a_obj_id);
				$a_ref_id = end($ref);
			}			

			$crs_ref = $tree->checkForParentType($a_ref_id, 'crs');
			if(!$crs_ref)
			{
				return ilContainer::VIEW_DEFAULT;
			}

			include_once './Modules/Course/classes/class.ilObjCourse.php';
			$view_mode = ilObjCourse::_lookupViewMode(ilObject::_lookupObjId($crs_ref));
			
			// validate course view mode
			if(!in_array($view_mode, array(ilContainer::VIEW_SESSIONS, 
				ilContainer::VIEW_BY_TYPE, ilContainer::VIEW_SIMPLE)))
			{
				return ilContainer::VIEW_DEFAULT;
			}
			
			return $view_mode;
		}
						
		return $a_view_mode;
	}			

	/**
	* Add additional information to sub item, e.g. used in
	* courses for timings information etc.
	*/
	function addAdditionalSubItemInformation(&$a_item_data)
	{		
		include_once './Services/Object/classes/class.ilObjectActivation.php';
		ilObjectActivation::addAdditionalSubItemInformation($a_item_data);
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
				
				$apps = array();
				if($this->getStart() && $this->getEnd())
				{
					$app = new ilCalendarAppointmentTemplate(self::CAL_START);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('grp_cal_start');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart($this->getStart());
					$app->setFullday(true);
					$apps[] = $app;

					$app = new ilCalendarAppointmentTemplate(self::CAL_END);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('grp_cal_end');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart($this->getEnd());
					$app->setFullday(true);
					$apps[] = $app;
				}
				if($this->isRegistrationUnlimited())
				{
					return $apps;
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
	
	/**
	 * Get members objects
	 * 
	 * @return ilGroupParticipants
	 */
	public function getMembersObject()
	{
		// #17886
		if(!$this->members_obj instanceof ilGroupParticipants)
		{
			$this->initParticipants();
		}
		return $this->members_obj;
	}
	
	/**
	 * @see interface.ilMembershipRegistrationCodes
	 * @return array obj ids
	 */
	public static function lookupObjectsByCode($a_code)
	{
		global $ilDB;
		
		$query = "SELECT obj_id FROM grp_settings ".
			"WHERE reg_ac_enabled = ".$ilDB->quote(1,'integer')." ".
			"AND reg_ac = ".$ilDB->quote($a_code,'text');
		$res = $ilDB->query($query);
		
		$obj_ids = array();
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$obj_ids[] = $row->obj_id;
		}
		return $obj_ids;
	}
	
	/**
	 * @see ilMembershipRegistrationCodes::register()
	 * @param int user_id
	 * @param int role
	 * @param bool force registration and do not check registration constraints.
	 */
	public function register($a_user_id,$a_role = IL_GRP_MEMBER, $a_force_registration = false)
	{
		include_once './Services/Membership/exceptions/class.ilMembershipRegistrationException.php';
		include_once "./Modules/Group/classes/class.ilGroupParticipants.php";
		$part = ilGroupParticipants::_getInstanceByObjId($this->getId());

		if($part->isAssigned($a_user_id))
		{
			return true;
		}
		
		if(!$a_force_registration)
		{
			// Availability
			if(!$this->isRegistrationEnabled())
			{
				include_once './Modules/Group/classes/class.ilObjGroupAccess.php';

				if(!ilObjGroupAccess::_usingRegistrationCode())
				{
					throw new ilMembershipRegistrationException('Cannot registrate to group '.$this->getId().
						', group subscription is deactivated.', ilMembershipRegistrationException::REGISTRATION_CODE_DISABLED);
				}
			}

			// Time Limitation
			if(!$this->isRegistrationUnlimited())
			{
				$start = $this->getRegistrationStart();
				$end = $this->getRegistrationEnd();
				$time = new ilDateTime(time(),IL_CAL_UNIX);

				if( !(ilDateTime::_after($time, $start) and ilDateTime::_before($time,$end)) )
				{
					throw new ilMembershipRegistrationException('Cannot registrate to group '.$this->getId().
					', group is out of registration time.', ilMembershipRegistrationException::OUT_OF_REGISTRATION_PERIOD);
				}
			}

			// Max members
			if($this->isMembershipLimited())
			{
				$free = max(0,$this->getMaxMembers() - $part->getCountMembers());
				include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
				$waiting_list = new ilGroupWaitingList($this->getId());
				if($this->isWaitingListEnabled() and (!$free or $waiting_list->getCountUsers()))
				{
					$this->lng->loadLanguageModule("grp");
					$waiting_list->addToList($a_user_id);

					$info = sprintf($this->lng->txt('grp_added_to_list'),
						$this->getTitle(),
						$waiting_list->getPosition($a_user_id));

					include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
					include_once('./Modules/Group/classes/class.ilGroupMembershipMailNotification.php');
					$participants = ilGroupParticipants::_getInstanceByObjId($this->getId());
					$participants->sendNotification(ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER,$a_user_id);

					throw new ilMembershipRegistrationException($info, ilMembershipRegistrationException::ADDED_TO_WAITINGLIST);
				}

				if(!$free or $waiting_list->getCountUsers())
				{
					throw new ilMembershipRegistrationException('Cannot registrate to group '.$this->getId().
						', membership is limited.', ilMembershipRegistrationException::OBJECT_IS_FULL);
				}
			}
		}
		
		$part->add($a_user_id,$a_role);
		$part->sendNotification(ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER, $a_user_id);
		$part->sendNotification(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION, $a_user_id);
		return true;
	}	
		
	public function handleAutoFill()
	{	
		if($this->isWaitingListEnabled() &&
			$this->hasWaitingListAutoFill())
		{
			$max = $this->getMaxMembers();
			$now = ilGroupParticipants::lookupNumberOfMembers($this->getRefId());
			if($max > $now)
			{
				// see assignFromWaitingListObject()
				include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
				$waiting_list = new ilGroupWaitingList($this->getId());

				foreach($waiting_list->getUserIds() as $user_id)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
					{
						continue;
					}
					if($this->getMembersObject()->isAssigned($user_id))
					{
						continue;
					}
					$this->getMembersObject()->add($user_id,IL_GRP_MEMBER); // #18213
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$this->getMembersObject()->sendNotification(ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,$user_id,true);
					$waiting_list->removeFromList($user_id);

					$now++;
					if($now >= $max)
					{
						break;
					}
				}
			}
		}		
	}	
	
	public static function mayLeave($a_group_id, $a_user_id = null, &$a_date = null)
	{
		global $ilUser, $ilDB;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$set = $ilDB->query("SELECT leave_end".
			" FROM grp_settings".
			" WHERE obj_id = ".$ilDB->quote($a_group_id, "integer"));
		$row = $ilDB->fetchAssoc($set);		
		if($row && $row["leave_end"])
		{
			// timestamp to date
			$limit = date("Ymd", $row["leave_end"]);			
			if($limit < date("Ymd"))
			{
				$a_date = new ilDate(date("Y-m-d", $row["leave_end"]), IL_CAL_DATE);		
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Minimum members check
	 * @global $ilDB $ilDB
	 * @return array
	 */
	public static function findGroupsWithNotEnoughMembers()
	{
		$ilDB = $GLOBALS['DIC']->database();
		$tree = $GLOBALS['DIC']->repositoryTree();
		
		$res = array();
		
		$now = date("Y-m-d H:i:s");
		
		include_once "Modules/Group/classes/class.ilGroupParticipants.php";
		
		$set = $ilDB->query("SELECT obj_id, registration_min_members".
			" FROM grp_settings".
			" WHERE registration_min_members > ".$ilDB->quote(0, "integer").
			" AND registration_mem_limit = ".$ilDB->quote(1, "integer"). // #17206				
			" AND ((leave_end IS NOT NULL".
				" AND leave_end < ".$ilDB->quote($now, "text").")".
				" OR (leave_end IS NULL".
				" AND registration_end IS NOT NULL".
				" AND registration_end < ".$ilDB->quote($now, "text")."))".
			" AND (grp_start IS NULL OR grp_start > ".$ilDB->quote($now, "integer").")" );
		while($row = $ilDB->fetchAssoc($set))
		{
			$refs = ilObject::_getAllReferences($row['obj_id']);
			$ref = end($refs);
			
			if($tree->isDeleted($ref))
			{
				continue;
			}
			
			$part = new ilGroupParticipants($row["obj_id"]);			
			$reci = $part->getNotificationRecipients();
			if(sizeof($reci))
			{
				$missing = (int)$row["registration_min_members"]-$part->getCountMembers();
				if($missing > 0)
				{
					$res[$row["obj_id"]] = array($missing, $reci);		
				}
			}			
		}
		
		return $res;
	}
	
} //END class.ilObjGroup
?>
