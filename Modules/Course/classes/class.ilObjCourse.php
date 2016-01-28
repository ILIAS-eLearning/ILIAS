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
	| but WITHOUT ANY WARRANTY; without ceven the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

require_once "./Services/Container/classes/class.ilContainer.php";
include_once './Modules/Course/classes/class.ilCourseConstants.php';
include_once './Services/Membership/interfaces/interface.ilMembershipRegistrationCodes.php';

/**
* Class ilObjCourse
*
* @author Stefan Meyer <meyer@leifos.com> 
* @version $Id$
* 
*/
class ilObjCourse extends ilContainer implements ilMembershipRegistrationCodes
{

	const CAL_REG_START = 1;
	const CAL_REG_END = 2;
	const CAL_ACTIVATION_START = 3;
	const CAL_ACTIVATION_END = 4;
	const CAL_COURSE_START = 5;
	const CAL_COURSE_END = 6;
	
	const STATUS_DETERMINATION_LP = 1;
	const STATUS_DETERMINATION_MANUAL = 2;

	private $member_obj = null;
	private $members_obj = null;
	var $archives_obj;
	
	private $latitude = '';
	private $longitude = '';
	private $locationzoom = 0;
	private $enablemap = 0;
	
	private $session_limit = 0;
	private $session_prev = -1;
	private $session_next = -1;
	
	private $reg_access_code = '';
	private $reg_access_code_enabled = false;
	private $status_dt = null;
	
	private $mail_members = ilCourseConstants::MAIL_ALLOWED_ALL;
	
	protected $crs_start; // [ilDate]
	protected $crs_end; // [ilDate]
	protected $leave_end; // [ilDate]
	protected $min_members; // [int]
	protected $auto_fill_from_waiting; // [bool]
	
	/**
	 *
	 * 
	 *
	 * @var boolean
	 * @access private
	 * 
	 */
	private $auto_notification = true;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjCourse($a_id = 0,$a_call_by_reference = true)
	{
		
		#define("ILIAS_MODULE","course");
		#define("KEEP_IMAGE_PATH",1);

		$this->SUBSCRIPTION_DEACTIVATED = 1;
		$this->SUBSCRIPTION_CONFIRMATION = 2;
		$this->SUBSCRIPTION_DIRECT = 3;
		$this->SUBSCRIPTION_PASSWORD = 4;
		$this->SUBSCRIPTION_AUTOSUBSCRIPTION = 5;
		$this->ARCHIVE_DISABLED = 1;
		$this->ARCHIVE_READ = 2;
		$this->ARCHIVE_DOWNLOAD = 3;
		$this->ABO_ENABLED = 1;
		$this->ABO_DISABLED = 0;
		$this->SHOW_MEMBERS_ENABLED = 1;
		$this->SHOW_MEMBERS_DISABLED = 0;
		$this->setStatusDetermination(self::STATUS_DETERMINATION_LP);

		$this->type = "crs";

		parent::__construct($a_id,$a_call_by_reference);

	}
	
	/**
	 * Check if show member is enabled
	 * @param int $a_obj_id
	 * @return bool
	 */
	public static function lookupShowMembersEnabled($a_obj_id)
	{
		$query = 'SELECT show_members FROM crs_settings '.
				'WHERE obj_id = '.$GLOBALS['ilDB']->quote($a_obj_id,'integer');
		$res = $GLOBALS['ilDB']->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (bool) $row->show_members;
		}
		return false;
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

	function getImportantInformation()
	{
		return $this->important;
	}
	function setImportantInformation($a_info)
	{
		$this->important = $a_info;
	}
	function getSyllabus()
	{
		return $this->syllabus;
	}
	function setSyllabus($a_syllabus)
	{
		$this->syllabus = $a_syllabus;
	}
	function getContactName()
	{
		return $this->contact_name;
	}
	function setContactName($a_cn)
	{
		$this->contact_name = $a_cn;
	}
	function getContactConsultation()
	{
		return $this->contact_consultation;
	}
	function setContactConsultation($a_value)
	{
		$this->contact_consultation = $a_value;
	}
	function getContactPhone()
	{
		return $this->contact_phone;
	}
	function setContactPhone($a_value)
	{
		$this->contact_phone = $a_value;
	}
	function getContactEmail()
	{
		return $this->contact_email;
	}
	function setContactEmail($a_value)
	{
		$this->contact_email = $a_value;
	}
	function getContactResponsibility()
	{
		return $this->contact_responsibility;
	}
	function setContactResponsibility($a_value)
	{
		$this->contact_responsibility = $a_value;
	}
	function getActivationType()
	{
		return (int) $this->activation_type;
	}
	function setActivationType($a_type)
	{
		// offline is separate property now
		if($a_type == IL_CRS_ACTIVATION_OFFLINE)
		{
			$this->setOfflineStatus(true);
			$a_type = IL_CRS_ACTIVATION_UNLIMITED;
		}
		
		$this->activation_type = $a_type;
	}
	function getActivationUnlimitedStatus()
	{
		return $this->activation_type == IL_CRS_ACTIVATION_UNLIMITED;		
	} 	
	function getActivationStart()
	{
		return $this->activation_start ? $this->activation_start : time();
	}
	function setActivationStart($a_value)
	{
		$this->activation_start = $a_value;
	}
	function getActivationEnd()
	{
		return $this->activation_end ? $this->activation_end : mktime(0,0,0,12,12,date("Y",time())+2);
	}
	function setActivationEnd($a_value)
	{
		$this->activation_end = $a_value;
	}
	function getOfflineStatus()
	{
		return (bool)$this->activation_offline;
	}
	function setOfflineStatus($a_value)
	{
		$this->activation_offline = (bool) $a_value;
	}
	function setActivationVisibility($a_value)
	{
		$this->activation_visibility = (bool) $a_value;
	}
	function getActivationVisibility()
	{
		return $this->activation_visibility;
	}

	function getSubscriptionLimitationType()
	{
		return $this->subscription_limitation_type;
	}
	function setSubscriptionLimitationType($a_type)
	{
		$this->subscription_limitation_type = $a_type;
	}
	function getSubscriptionUnlimitedStatus()
	{
		return $this->subscription_limitation_type == IL_CRS_SUBSCRIPTION_UNLIMITED;
	} 
	function getSubscriptionStart()
	{
		return $this->subscription_start ? $this->subscription_start : time();
	}
	function setSubscriptionStart($a_value)
	{
		$this->subscription_start = $a_value;
	}
	function getSubscriptionEnd()
	{
		return $this->subscription_end ? $this->subscription_end : mktime(0,0,0,12,12,date("Y",time())+2);
	}
	function setSubscriptionEnd($a_value)
	{
		$this->subscription_end = $a_value;
	}
	function getSubscriptionType()
	{
		return $this->subscription_type ? $this->subscription_type : IL_CRS_SUBSCRIPTION_DIRECT;
		#return $this->subscription_type ? $this->subscription_type : $this->SUBSCRIPTION_DEACTIVATED;
	}
	function setSubscriptionType($a_value)
	{
		$this->subscription_type = $a_value;
	}
	function getSubscriptionPassword()
	{
		return $this->subscription_password;
	}
	function setSubscriptionPassword($a_value)
	{
		$this->subscription_password = $a_value;
	}
	function enabledObjectiveView()
	{
		return $this->view_mode == IL_CRS_VIEW_OBJECTIVE;
	}

	function enabledWaitingList()
	{
		return (bool) $this->waiting_list;
	}

	function enableWaitingList($a_status)
	{
		$this->waiting_list = (bool) $a_status;
	}

	function inSubscriptionTime()
	{
		if($this->getSubscriptionUnlimitedStatus())
		{
			return true;
		}
		if(time() > $this->getSubscriptionStart() and time() < $this->getSubscriptionEnd())
		{
			return true;
		}
		return false;
	}
	
	/**
	 * en/disable limited number of sessions 
	 * @return 
	 * @param object $a_status
	 */
	public function enableSessionLimit($a_status)
	{
		$this->session_limit = $a_status;
	}
	
	public function isSessionLimitEnabled()
	{
		return (bool) $this->session_limit;
	}
	
	/**
	 * enable max members
	 *
	 * @access public
	 * @param bool status
	 * @return
	 */
	public function enableSubscriptionMembershipLimitation($a_status)
	{
		$this->subscription_membership_limitation = $a_status;
	}

	/**
	 * Set number of previous sessions
	 * @return 
	 * @param int $a_num
	 */
	public function setNumberOfPreviousSessions($a_num)
	{
		$this->session_prev = $a_num;
	}
	
	/**
	 * Set number of previous sessions
	 * @return 
	 */
	public function getNumberOfPreviousSessions()
	{
		return $this->session_prev;
	}
	
	/**
	 * Set number of previous sessions
	 * @return 
	 * @param int $a_num
	 */
	public function setNumberOfNextSessions($a_num)
	{
		$this->session_next = $a_num;
	}
	
	/**
	 * Set number of previous sessions
	 * @return 
	 */
	public function getNumberOfNextSessions()
	{
		return $this->session_next;
	}
	/**
	 * is membership limited
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function isSubscriptionMembershipLimited()
	{
		return (bool) $this->subscription_membership_limitation;
	}

	function getSubscriptionMaxMembers()
	{
		return $this->subscription_max_members;
	}
	function setSubscriptionMaxMembers($a_value)
	{
		$this->subscription_max_members = $a_value;
	}
	
	/**
	 * Check if subscription notification is enabled
	 *
	 * @access public
	 * @static
	 *
	 * @param int course_id
	 */
	public static function _isSubscriptionNotificationEnabled($a_course_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_course_id ,'integer')." ".
			"AND sub_notify = 1";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * Get subitems of container
	 * @param bool $a_admin_panel_enabled[optional]
	 * @param bool $a_include_side_block[optional]
	 * @return array 
	 */
	public function getSubItems($a_admin_panel_enabled = false, $a_include_side_block = false)
	{
		global $ilUser;

		// Caching
		if (is_array($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]))
		{
			return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
		}
		
		// Results are stored in $this->items
		parent::getSubItems($a_admin_panel_enabled,$a_include_side_block);
		
		$limit_sess = false;		
		if(!$a_admin_panel_enabled &&
			!$a_include_side_block &&
			$this->items['sess'] &&
			is_array($this->items['sess']) &&
			$this->isSessionLimitEnabled() &&
			$this->getViewMode() == ilContainer::VIEW_SESSIONS) // #16686
		{
			$limit_sess = true;
		}
		
		if(!$limit_sess)
		{
			return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
		}
				
		
		// do session limit		
	
		// @todo move to gui class
		if(isset($_GET['crs_prev_sess']))
		{
			$ilUser->writePref('crs_sess_show_prev_'.$this->getId(), (string) (int) $_GET['crs_prev_sess']);
		}
		if(isset($_GET['crs_next_sess']))
		{
			$ilUser->writePref('crs_sess_show_next_'.$this->getId(), (string) (int) $_GET['crs_next_sess']);
		}
		
		$sessions = ilUtil::sortArray($this->items['sess'],'start','ASC',true,false);
		$today = new ilDate(date('Ymd',time()),IL_CAL_DATE);
		$previous = $current = $next = array();
		foreach($sessions as $key => $item)
		{
			$start = new ilDateTime($item['start'],IL_CAL_UNIX);
			$end = new ilDateTime($item['end'],IL_CAL_UNIX);
			
			if(ilDateTime::_within($today, $start, $end, IL_CAL_DAY))
			{
				$current[] = $item;
			}
			elseif(ilDateTime::_before($start, $today, IL_CAL_DAY))
			{
				$previous[] = $item;
			}
			elseif(ilDateTime::_after($start, $today, IL_CAL_DAY))
			{
				$next[] = $item;
			}
		}
		$num_previous_remove = max(
				count($previous) - $this->getNumberOfPreviousSessions(), 
				0
		);
		while($num_previous_remove--)
		{
			if(!$ilUser->getPref('crs_sess_show_prev_'.$this->getId()))
			{
				array_shift($previous);
			}
			$this->items['sess_link']['prev']['value'] = 1;
		}
		
		$num_next_remove = max(
				count($next) - $this->getNumberOfNextSessions(),
				0
		);
		while($num_next_remove--)
		{
			if(!$ilUser->getPref('crs_sess_show_next_'.$this->getId()))
			{
				array_pop($next);
			}
			// @fixme
			$this->items['sess_link']['next']['value'] = 1;
		}
		
		$sessions = array_merge($previous,$current,$next);
		$this->items['sess'] = $sessions;
		
		// #15389 - see ilContainer::getSubItems()
		include_once('Services/Container/classes/class.ilContainerSorting.php');
		$sort = ilContainerSorting::_getInstance($this->getId());				
		$this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block] = $sort->sortItems($this->items);
		
		return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
	}
	
	function getSubscriptionNotify()
	{
		return true;
		return $this->subscription_notify ? true : false;
	}
	function setSubscriptionNotify($a_value)
	{
		$this->subscription_notify = $a_value ? true : false;
	}

	function setViewMode($a_mode)
	{
		$this->view_mode = $a_mode;
	}
	function getViewMode()
	{
		return $this->view_mode;
	}

	function _lookupViewMode($a_id)
	{
		global $ilDB;

		$query = "SELECT view_mode FROM crs_settings WHERE obj_id = ".$ilDB->quote($a_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->view_mode;
		}
		return false;
	}

	function _lookupAboStatus($a_id)
	{
		global $ilDB;

		$query = "SELECT abo FROM crs_settings WHERE obj_id = ".$ilDB->quote($a_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->abo;
		}
		return false;
	}

	function getArchiveStart()
	{
		return $this->archive_start ? $this->archive_start : time();
	}
	function setArchiveStart($a_value)
	{
		$this->archive_start = $a_value;
	}
	function getArchiveEnd()
	{
		return $this->archive_end ? $this->archive_end : mktime(0,0,0,12,12,date("Y",time())+2);
	}
	function setArchiveEnd($a_value)
	{
		$this->archive_end = $a_value;
	}
	function getArchiveType()
	{
		return $this->archive_type ? IL_CRS_ARCHIVE_DOWNLOAD : IL_CRS_ARCHIVE_NONE;
	}
	function setArchiveType($a_value)
	{
		$this->archive_type = $a_value;
	}
	function setAboStatus($a_status)
	{
		$this->abo = $a_status;
	}
	function getAboStatus()
	{
		return $this->abo;
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

	function isActivated($a_check_archive = false)
	{
		if($a_check_archive)
		{
			if($this->isArchived())
			{
				return true;
			}
		}
		if($this->getOfflineStatus())
		{
			return false;
		}
		if($this->getActivationUnlimitedStatus())
		{
			return true;
		}
		if(time() < $this->getActivationStart() or
		   time() > $this->getActivationEnd())
		{
			return false;
		}
		return true;
	}

	/**
	 * Is activated. Method is in Access class, since it is needed by Access/ListGUI.
	 *
	 * @param int id of user
	 * @return boolean
	 */
	function _isActivated($a_obj_id)
	{
		include_once("./Modules/Course/classes/class.ilObjCourseAccess.php");
		return ilObjCourseAccess::_isActivated($a_obj_id);
	}

	/**
	 * Registration enabled? Method is in Access class, since it is needed by Access/ListGUI.
	 *
	 * @param int id of user
	 * @return boolean
	 */
	function _registrationEnabled($a_obj_id)
	{
		include_once("./Modules/Course/classes/class.ilObjCourseAccess.php");
		return ilObjCourseAccess::_registrationEnabled($a_obj_id);
	}

	function isArchived()
	{
		if($this->getViewMode() != IL_CRS_VIEW_ARCHIVE)
		{
			return false;
		}
		if(time() < $this->getArchiveStart() or time() > $this->getArchiveEnd())
		{
			return false;
		}
		return true;
	}

	function allowAbo()
	{
		return $this->ABO == $this->ABO_ENABLED;
	}

	function read($a_force_db = false)
	{
		parent::read($a_force_db);

		include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
		$this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));

		$this->__readSettings();
	}
	function create($a_upload = false)
	{
		global $ilAppEventHandler;
		
		parent::create($a_upload);

		if(!$a_upload)
		{
			$this->createMetaData();
		}
		$this->__createDefaultSettings();
		
		$ilAppEventHandler->raise('Modules/Course',
			'create',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareAppointments('create')));
		
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
	* Set Enable Course Map.
	*
	* @param	boolean	$a_enablemap	Enable Course Map
	*/
	function setEnableCourseMap($a_enablemap)
	{
		$this->enablemap = $a_enablemap;
	}

	/**
	* Get Enable Course Map.
	*
	* @return	boolean	Enable Course Map
	*/
	function getEnableCourseMap()
	{
		return $this->enablemap;
	}
	
	function setCourseStart(ilDate $a_value = null)
	{		
		$this->crs_start = $a_value;
	}
	
	function getCourseStart()
	{		
		return $this->crs_start;
	}
	
	function setCourseEnd(ilDate $a_value = null)
	{		
		$this->crs_end = $a_value;
	}
	
	function getCourseEnd()
	{		
		return $this->crs_end;
	}
	
	function setCancellationEnd(ilDate $a_value = null)
	{		
		$this->leave_end = $a_value;
	}
	
	function getCancellationEnd()
	{		
		return $this->leave_end;
	}	
	
	function setSubscriptionMinMembers($a_value)
	{
		if($a_value !== null)
		{
			$a_value = (int)$a_value;
		}
		$this->min_members = $a_value;
	}
	
	function getSubscriptionMinMembers()
	{
		return $this->min_members;
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
	 * Clone course (no member data)
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
	 	
	 	$this->cloneAutoGeneratedRoles($new_obj);
	 	$this->cloneMetaData($new_obj);
	 	
	 	// Assign admin
		$new_obj->getMemberObject()->add($ilUser->getId(),IL_CRS_ADMIN);
		// cognos-blu-patch: begin
		$new_obj->getMemberObject()->updateContact($ilUser->getId(), 1);
		// cognos-blu-patch: end
		
			
		// #14596		
		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);		
		if($cwo->isRootNode($this->getRefId()))
		{
			$this->setOfflineStatus(true);
		}				
		
		// Copy settings
		$this->cloneSettings($new_obj);
	
		// Course Defined Fields
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		ilCourseDefinedFieldDefinition::_clone($this->getId(),$new_obj->getId());
		
		// Clone course files
		include_once('Modules/Course/classes/class.ilCourseFile.php');
		ilCourseFile::_cloneFiles($this->getId(),$new_obj->getId());
		
		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);
		
		// clone icons
		global $ilLog;
		$ilLog->write(__METHOD__.': '.$this->getBigIconPath().' '.$this->getSmallIconPath());
		$new_obj->saveIcons($this->getBigIconPath(),
			$this->getSmallIconPath(),
			$this->getTinyIconPath());
		
		// clone certificate (#11085)
		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
		$cert = new ilCertificate(new ilCourseCertificateAdapter($this));
		$newcert = new ilCertificate(new ilCourseCertificateAdapter($new_obj));
		$cert->cloneCertificate($newcert);
				
		return $new_obj;
	}
	
	/**
	 * Clone object dependencies (start objects, preconditions)
	 *
	 * @access public
	 * @param int target ref id of new course
	 * @param int copy id
	 * 
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{		
		parent::cloneDependencies($a_target_id,$a_copy_id);
		
	 	// Clone course start objects
	 	include_once('Services/Container/classes/class.ilContainerStartObjects.php');
	 	$start = new ilContainerStartObjects($this->getRefId(),$this->getId());
	 	$start->cloneDependencies($a_target_id,$a_copy_id);
	 	
	 	// Clone course item settings
		include_once('Services/Object/classes/class.ilObjectActivation.php');
		ilObjectActivation::cloneDependencies($this->getRefId(),$a_target_id,$a_copy_id);
		
		// clone objective settings
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		ilLOSettings::cloneSettings($a_copy_id, $this->getId(), ilObject::_lookupObjId($a_target_id));

		// Clone course learning objectives
		include_once('Modules/Course/classes/class.ilCourseObjective.php');
		$crs_objective = new ilCourseObjective($this);
		$crs_objective->ilClone($a_target_id,$a_copy_id);
		
	 	return true;
	}
	
	/**
	 * Clone automatic genrated roles (permissions and template permissions)
	 *
	 * @access public
	 * @param object new course object
	 * 
	 */
	public function cloneAutoGeneratedRoles($new_obj)
	{
		global $ilLog,$rbacadmin,$rbacreview;
		
		$admin = $this->getDefaultAdminRole();
		$new_admin = $new_obj->getDefaultAdminRole();
		
		if(!$admin || !$new_admin || !$this->getRefId() || !$new_obj->getRefId())
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_crs_admin');
		}
		$rbacadmin->copyRolePermissions($admin,$this->getRefId(),$new_obj->getRefId(),$new_admin,true);
		$ilLog->write(__METHOD__.' : Finished copying of role crs_admin.');
		
		$tutor = $this->getDefaultTutorRole();
		$new_tutor = $new_obj->getDefaultTutorRole();
		if(!$tutor || !$new_tutor)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_crs_tutor');
		}
		$rbacadmin->copyRolePermissions($tutor,$this->getRefId(),$new_obj->getRefId(),$new_tutor,true);
		$ilLog->write(__METHOD__.' : Finished copying of role crs_tutor.');
		
		$member = $this->getDefaultMemberRole();
		$new_member = $new_obj->getDefaultMemberRole();
		if(!$member || !$new_member)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_crs_member');
		}
		$rbacadmin->copyRolePermissions($member,$this->getRefId(),$new_obj->getRefId(),$new_member,true);
		$ilLog->write(__METHOD__.' : Finished copying of role crs_member.');
		
		return true;
	}
	

	function validate()
	{
		$this->setMessage('');

		#if(($this->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED) and
		#   $this->getSubscriptionType() == )
		#{
		#	$this->appendMessage($this->lng->txt('crs_select_registration_type'));
		#}

		if(($this->getActivationType() == IL_CRS_ACTIVATION_LIMITED) and
		   $this->getActivationEnd() < $this->getActivationStart())
		{
			$this->appendMessage($this->lng->txt("activation_times_not_valid"));
		}
		if(($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED) and
		   $this->getSubscriptionStart() > $this->getSubscriptionEnd())
		{
			$this->appendMessage($this->lng->txt("subscription_times_not_valid"));
		}
		#if((!$this->getActivationUnlimitedStatus() and
		#	!$this->getSubscriptionUnlimitedStatus()) and
		#	($this->getSubscriptionStart() > $this->getActivationEnd() or
		#	 $this->getSubscriptionStart() < $this->getActivationStart() or
		#	 $this->getSubscriptionEnd() > $this->getActivationEnd() or
		#	 $this->getSubscriptionEnd() <  $this->getActivationStart()))
		#   
		#{
		#	$this->appendMessage($this->lng->txt("subscription_time_not_within_activation"));
		#}
		if($this->getSubscriptionType() == IL_CRS_SUBSCRIPTION_PASSWORD and !$this->getSubscriptionPassword())
		{
			$this->appendMessage($this->lng->txt("crs_password_required"));
		}
		if($this->isSubscriptionMembershipLimited())
		{			
			if($this->getSubscriptionMinMembers() <= 0 && $this->getSubscriptionMaxMembers() <= 0)
			{
				$this->appendMessage($this->lng->txt("crs_max_and_min_members_needed"));
			}
			if($this->getSubscriptionMaxMembers() <= 0 && $this->enabledWaitingList())
			{
				$this->appendMessage($this->lng->txt("crs_max_members_needed"));
			}
			if($this->getSubscriptionMaxMembers() > 0 && $this->getSubscriptionMinMembers() > $this->getSubscriptionMaxMembers())
			{
				$this->appendMessage($this->lng->txt("crs_max_and_min_members_invalid"));
			}
		}
		if(($this->getViewMode() == IL_CRS_VIEW_ARCHIVE) and
		   $this->getArchiveStart() > $this->getArchiveEnd())
		{
			$this->appendMessage($this->lng->txt("archive_times_not_valid"));
		}
		if(!$this->getTitle() || !$this->getStatusDetermination())
		{
			$this->appendMessage($this->lng->txt('err_check_input'));
		}
		
		if($this->getCourseStart() && 
			$this->getCourseStart()->get(IL_CAL_UNIX) > $this->getCourseEnd()->get(IL_CAL_UNIX))
		{
			$this->appendMessage($this->lng->txt("crs_course_period_not_valid"));
		}

		return $this->getMessage() ? false : true;
	}

	function validateInfoSettings()
	{
		global $ilErr;
		$error = false;
		if($this->getContactEmail()) {
  		$emails = split(",",$this->getContactEmail());
			
			foreach ($emails as $email) {
				$email = trim($email);
				if (!(ilUtil::is_email($email) or ilObjUser::getUserIdByLogin($email)))
				{
					$ilErr->appendMessage($this->lng->txt('contact_email_not_valid')." '".$email."'");
					$error = true;
				}
			}			
		}
		return !$error;
	}

	function hasContactData()
	{
		return strlen($this->getContactName()) or
			strlen($this->getContactResponsibility()) or
			strlen($this->getContactEmail()) or
			strlen($this->getContactPhone()) or
			strlen($this->getContactConsultation());
	}
			

	/**
	* delete course and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilAppEventHandler;
		
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delete meta data
		$this->deleteMetaData();

		// put here course specific stuff

		$this->__deleteSettings();

		include_once('Modules/Course/classes/class.ilCourseParticipants.php');
		ilCourseParticipants::_deleteAllEntries($this->getId());

		$this->initCourseArchiveObject();
		$this->archives_obj->deleteAll();

		include_once './Modules/Course/classes/class.ilCourseObjective.php';
		ilCourseObjective::_deleteAll($this->getId());

		include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';
		ilObjCourseGrouping::_deleteAll($this->getId());

		include_once './Modules/Course/classes/class.ilCourseFile.php';
		ilCourseFile::_deleteByCourse($this->getId());
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		ilCourseDefinedFieldDefinition::_deleteByContainer($this->getId());
		
		$ilAppEventHandler->raise('Modules/Course',
			'delete',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareAppointments('delete')));
		
		
		return true;
	}



	/**
	* update complete object
	*/
	function update()
	{
		global $ilAppEventHandler,$ilLog;

		include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
		$sorting = new ilContainerSortingSettings($this->getId());
		$sorting->setSortMode($this->getOrderType());
		$sorting->update();

		$this->updateMetaData();
		$this->updateSettings();
		parent::update();
		
		$ilAppEventHandler->raise('Modules/Course',
			'update',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareAppointments('update')));
		
	}

	function updateSettings()
	{
		global $ilDB;

		// Due to a bug 3.5.alpha maybe no settings exist. => create default settings

		$query = "SELECT * FROM crs_settings WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer')." ";
		$res = $ilDB->query($query);

		if(!$res->numRows())
		{
			$this->__createDefaultSettings();
		}
		
		$query = "UPDATE crs_settings SET ".
			"syllabus = ".$ilDB->quote($this->getSyllabus() ,'text').", ".
			"contact_name = ".$ilDB->quote($this->getContactName() ,'text').", ".
			"contact_responsibility = ".$ilDB->quote($this->getContactResponsibility() ,'text').", ".
			"contact_phone = ".$ilDB->quote($this->getContactPhone() ,'text').", ".
			"contact_email = ".$ilDB->quote($this->getContactEmail() ,'text').", ".
			"contact_consultation = ".$ilDB->quote($this->getContactConsultation() ,'text').", ".
			"activation_type = ".$ilDB->quote(!$this->getOfflineStatus() ,'integer').", ".			
			"sub_limitation_type = ".$ilDB->quote($this->getSubscriptionLimitationType() ,'integer').", ".
			"sub_start = ".$ilDB->quote($this->getSubscriptionStart() ,'integer').", ".
			"sub_end = ".$ilDB->quote($this->getSubscriptionEnd() ,'integer').", ".
			"sub_type = ".$ilDB->quote($this->getSubscriptionType() ,'integer').", ".
			"sub_password = ".$ilDB->quote($this->getSubscriptionPassword() ,'text').", ".
			"sub_mem_limit = ".$ilDB->quote((int) $this->isSubscriptionMembershipLimited() ,'integer').", ".
			"sub_max_members = ".$ilDB->quote($this->getSubscriptionMaxMembers() ,'integer').", ".
			"sub_notify = ".$ilDB->quote($this->getSubscriptionNotify() ,'integer').", ".
			"view_mode = ".$ilDB->quote($this->getViewMode() ,'integer').", ".
			"archive_start = ".$ilDB->quote($this->getArchiveStart() ,'integer').", ".
			"archive_end = ".$ilDB->quote($this->getArchiveEnd() ,'integer').", ".
			"archive_type = ".$ilDB->quote($this->getArchiveType() ,'integer').", ".
			"abo = ".$ilDB->quote($this->getAboStatus() ,'integer').", ".
			"waiting_list = ".$ilDB->quote($this->enabledWaitingList() ,'integer').", ".
			"important = ".$ilDB->quote($this->getImportantInformation() ,'text').", ".
			"show_members = ".$ilDB->quote($this->getShowMembers() ,'integer').", ".
			"latitude = ".$ilDB->quote($this->getLatitude() ,'text').", ".
			"longitude = ".$ilDB->quote($this->getLongitude() ,'text').", ".
			"location_zoom = ".$ilDB->quote($this->getLocationZoom() ,'integer').", ".
			"enable_course_map = ".$ilDB->quote((int) $this->getEnableCourseMap() ,'integer').", ".
			'session_limit = '.$ilDB->quote($this->isSessionLimitEnabled(),'integer').', '.
			'session_prev = '.$ilDB->quote($this->getNumberOfPreviousSessions(),'integer').', '.
			'session_next = '.$ilDB->quote($this->getNumberOfNextSessions(),'integer').', '.
			'reg_ac_enabled = '.$ilDB->quote($this->isRegistrationAccessCodeEnabled(),'integer').', '.
			'reg_ac = '.$ilDB->quote($this->getRegistrationAccessCode(),'text').', '.
			'auto_notification = '.$ilDB->quote( (int)$this->getAutoNotification(), 'integer').', '.
			'status_dt = '.$ilDB->quote((int) $this->getStatusDetermination()).', '.
			'mail_members_type = '.$ilDB->quote((int) $this->getMailToMembersType(),'integer').', '.					
			'crs_start = '.$ilDB->quote(($this->getCourseStart() && !$this->getCourseStart()->isNull()) ? $this->getCourseStart()->get(IL_CAL_UNIX) : null, 'integer').', '.
			'crs_end = '.$ilDB->quote(($this->getCourseEnd() && !$this->getCourseEnd()->isNull()) ? $this->getCourseEnd()->get(IL_CAL_UNIX) : null, 'integer').', '.
			'auto_wait = '.$ilDB->quote((int) $this->hasWaitingListAutoFill(),'integer').', '.
			'leave_end = '.$ilDB->quote(($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null, 'integer').', '.
			'min_members = '.$ilDB->quote((int) $this->getSubscriptionMinMembers(),'integer').'  '.				
			"WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer')."";		
				
		$res = $ilDB->manipulate($query);
		
		// moved activation to ilObjectActivation
		if($this->ref_id)
		{
			include_once "./Services/Object/classes/class.ilObjectActivation.php";		
			ilObjectActivation::getItem($this->ref_id);
			
			$item = new ilObjectActivation;			
			if($this->getActivationUnlimitedStatus())
			{
				$item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
			}
			else
			{				
				$item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
				$item->setTimingStart($this->getActivationStart());
				$item->setTimingEnd($this->getActivationEnd());
				$item->toggleVisible($this->getActivationVisibility());
			}						
			
			$item->update($this->ref_id);		
		}
	}
	
	/**
	 * Clone entries in settings table
	 *
	 * @access public
	 * @param ilObjCourse new course object
	 * 
	 */
	public function cloneSettings($new_obj)
	{
		$new_obj->setSyllabus($this->getSyllabus());
		$new_obj->setContactName($this->getContactName());
		$new_obj->setContactResponsibility($this->getContactResponsibility());
		$new_obj->setContactPhone($this->getContactPhone());
		$new_obj->setContactEmail($this->getContactEmail());
		$new_obj->setContactConsultation($this->getContactConsultation());
		$new_obj->setOfflineStatus($this->getOfflineStatus()); // #9914
		$new_obj->setActivationType($this->getActivationType());
		$new_obj->setActivationStart($this->getActivationStart());
		$new_obj->setActivationEnd($this->getActivationEnd());
		$new_obj->setActivationVisibility($this->getActivationVisibility());
		$new_obj->setSubscriptionLimitationType($this->getSubscriptionLimitationType());
		$new_obj->setSubscriptionStart($this->getSubscriptionStart());
		$new_obj->setSubscriptionEnd($this->getSubscriptionEnd());
		$new_obj->setSubscriptionType($this->getSubscriptionType());
		$new_obj->setSubscriptionPassword($this->getSubscriptionPassword());
		$new_obj->enableSubscriptionMembershipLimitation($this->isSubscriptionMembershipLimited());
		$new_obj->setSubscriptionMaxMembers($this->getSubscriptionMaxMembers());
		$new_obj->setSubscriptionNotify($this->getSubscriptionNotify());
		$new_obj->setViewMode($this->getViewMode());
		$new_obj->setOrderType($this->getOrderType());
		$new_obj->setArchiveStart($this->getArchiveStart());
		$new_obj->setArchiveEnd($this->getArchiveEnd());
		$new_obj->setArchiveType($this->getArchiveType());
		$new_obj->setAboStatus($this->getAboStatus());
		$new_obj->enableWaitingList($this->enabledWaitingList());
		$new_obj->setImportantInformation($this->getImportantInformation());
		$new_obj->setShowMembers($this->getShowMembers());
		$new_obj->enableSessionLimit($this->isSessionLimitEnabled());
		$new_obj->setNumberOfPreviousSessions($this->getNumberOfPreviousSessions());
		$new_obj->setNumberOfNextSessions($this->getNumberOfNextSessions());
		$new_obj->setAutoNotification( $this->getAutoNotification() );
		$new_obj->enableRegistrationAccessCode($this->isRegistrationAccessCodeEnabled());
		include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
		$new_obj->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
		$new_obj->setStatusDetermination($this->getStatusDetermination());
		$new_obj->setMailToMembersType($this->getMailToMembersType());
		$new_obj->setCourseStart($this->getCourseStart());
		$new_obj->setCourseEnd($this->getCourseEnd());
		$new_obj->setCancellationEnd($this->getCancellationEnd());
		$new_obj->setWaitingListAutoFill($this->hasWaitingListAutoFill());
		$new_obj->setSubscriptionMinMembers($this->getSubscriptionMinMembers());
		
		// #10271
		$new_obj->setEnableCourseMap($this->getEnableCourseMap());
		$new_obj->setLatitude($this->getLatitude());
		$new_obj->setLongitude($this->getLongitude());
		$new_obj->setLocationZoom($this->getLocationZoom());
		
		$new_obj->update();
	}

	function __createDefaultSettings()
	{
		global $ilDB;
		
		include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
		$this->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());

		$query = "INSERT INTO crs_settings (obj_id,syllabus,contact_name,contact_responsibility,".
			"contact_phone,contact_email,contact_consultation,activation_type,activation_start,".
			"activation_end,sub_limitation_type,sub_start,sub_end,sub_type,sub_password,sub_mem_limit,".
			"sub_max_members,sub_notify,view_mode,archive_start,archive_end,archive_type,abo," .
			"latitude,longitude,location_zoom,enable_course_map,waiting_list,show_members, ".
			"session_limit,session_prev,session_next, reg_ac_enabled, reg_ac, auto_notification, status_dt,mail_members_type) ".
			"VALUES( ".
			$ilDB->quote($this->getId() ,'integer').", ".
			$ilDB->quote($this->getSyllabus() ,'text').", ".
			$ilDB->quote($this->getContactName() ,'text').", ".
			$ilDB->quote($this->getContactResponsibility() ,'text').", ".
			$ilDB->quote($this->getContactPhone() ,'text').", ".
			$ilDB->quote($this->getContactEmail() ,'text').", ".
			$ilDB->quote($this->getContactConsultation() ,'text').", ".
			$ilDB->quote(0 ,'integer').", ".
			$ilDB->quote($this->getActivationStart() ,'integer').", ".
			$ilDB->quote($this->getActivationEnd() ,'integer').", ".
			$ilDB->quote(IL_CRS_SUBSCRIPTION_DEACTIVATED ,'integer').", ".
			$ilDB->quote($this->getSubscriptionStart() ,'integer').", ".
			$ilDB->quote($this->getSubscriptionEnd() ,'integer').", ".
			$ilDB->quote(IL_CRS_SUBSCRIPTION_DIRECT ,'integer').", ".
			$ilDB->quote($this->getSubscriptionPassword() ,'text').", ".
			"0, ".
			$ilDB->quote($this->getSubscriptionMaxMembers() ,'integer').", ".
			"1, ".
			"0, ".
			$ilDB->quote($this->getArchiveStart() ,'integer').", ".
			$ilDB->quote($this->getArchiveEnd() ,'integer').", ".
			$ilDB->quote(IL_CRS_ARCHIVE_NONE ,'integer').", ".
			$ilDB->quote($this->ABO_ENABLED ,'integer').", ".
			$ilDB->quote($this->getLatitude() ,'text').", ".
			$ilDB->quote($this->getLongitude() ,'text').", ".
			$ilDB->quote($this->getLocationZoom() ,'integer').", ".
			$ilDB->quote($this->getEnableCourseMap() ,'integer').", ".
			#"objective_view = '0', ".
			"1, ".
			"1,".
			$ilDB->quote($this->isSessionLimitEnabled(),'integer').', '.
			$ilDB->quote($this->getNumberOfPreviousSessions(),'integer').', '.
			$ilDB->quote($this->getNumberOfPreviousSessions(),'integer').', '.
			$ilDB->quote($this->isRegistrationAccessCodeEnabled(),'integer').', '.
			$ilDB->quote($this->getRegistrationAccessCode(),'text').', '.
			$ilDB->quote((int)$this->getAutoNotification(),'integer').', '.
			$ilDB->quote((int)$this->getStatusDetermination(),'integer').', '.
			$ilDB->quote((int) $this->getMailToMembersType(),'integer').' '.
			")";
			
		$res = $ilDB->manipulate($query);
		$this->__readSettings();

		include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
		$sorting = new ilContainerSortingSettings($this->getId());
		$sorting->setSortMode(ilContainer::SORT_MANUAL);
		$sorting->update();
	}
	

	function __readSettings()
	{
		global $ilDB;

		$query = "SELECT * FROM crs_settings WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer')."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setSyllabus($row->syllabus);
			$this->setContactName($row->contact_name);
			$this->setContactResponsibility($row->contact_responsibility);
			$this->setContactPhone($row->contact_phone);
			$this->setContactEmail($row->contact_email);
			$this->setContactConsultation($row->contact_consultation);
			$this->setOfflineStatus(!(bool)$row->activation_type); // see below
			$this->setSubscriptionLimitationType($row->sub_limitation_type);
			$this->setSubscriptionStart($row->sub_start);
			$this->setSubscriptionEnd($row->sub_end);
			$this->setSubscriptionType($row->sub_type);
			$this->setSubscriptionPassword($row->sub_password);
			$this->enableSubscriptionMembershipLimitation($row->sub_mem_limit);
			$this->setSubscriptionMaxMembers($row->sub_max_members);
			$this->setSubscriptionNotify($row->sub_notify);
			$this->setViewMode($row->view_mode);
			$this->setArchiveStart($row->archive_start);
			$this->setArchiveEnd($row->archive_end);
			$this->setArchiveType($row->archive_type);
			$this->setAboStatus($row->abo);
			$this->enableWaitingList($row->waiting_list);
			$this->setImportantInformation($row->important);
			$this->setShowMembers($row->show_members);
			$this->setLatitude($row->latitude);
			$this->setLongitude($row->longitude);
			$this->setLocationZoom($row->location_zoom);
			$this->setEnableCourseMap($row->enable_course_map);
			$this->enableSessionLimit($row->session_limit);
			$this->setNumberOfPreviousSessions($row->session_prev);
			$this->setNumberOfNextSessions($row->session_next);
			$this->enableRegistrationAccessCode($row->reg_ac_enabled);
			$this->setRegistrationAccessCode($row->reg_ac);
			$this->setAutoNotification($row->auto_notification == 1 ? true : false);
			$this->setStatusDetermination((int) $row->status_dt);
			$this->setMailToMembersType($row->mail_members_type);
			$this->setCourseStart($row->crs_start ? new ilDate($row->crs_start, IL_CAL_UNIX) : null);
			$this->setCourseEnd($row->crs_end ? new ilDate($row->crs_end, IL_CAL_UNIX) : null);
			$this->setCancellationEnd($row->leave_end ? new ilDate($row->leave_end, IL_CAL_UNIX) : null);
			$this->setWaitingListAutoFill($row->auto_wait);
			$this->setSubscriptionMinMembers($row->min_members ? $row->min_members : null);			
		}
		
		// moved activation to ilObjectActivation
		if($this->ref_id)
		{
			include_once "./Services/Object/classes/class.ilObjectActivation.php";
			$activation = ilObjectActivation::getItem($this->ref_id);			
			switch($activation["timing_type"])
			{				
				case ilObjectActivation::TIMINGS_ACTIVATION:
					$this->setActivationType(IL_CRS_ACTIVATION_LIMITED);					
					$this->setActivationStart($activation["timing_start"]);
					$this->setActivationEnd($activation["timing_end"]);
					$this->setActivationVisibility($activation["visible"]);
					break;
				
				default:
					$this->setActivationType(IL_CRS_ACTIVATION_UNLIMITED);
					break;							
			}
		}
		else
		{
			// #13176 - there should always be default
			$this->setActivationType(IL_CRS_ACTIVATION_UNLIMITED);
		}
		
		return true;
	}

	function initWaitingList()
	{
		include_once "./Modules/Course/classes/class.ilCourseWaitingList.php";

		if(!is_object($this->waiting_list_obj))
		{
			$this->waiting_list_obj = new ilCourseWaitingList($this->getId());
		}
		return true;
	}
		

	/**
	 * Init course member object
	 * @global ilObjUser $ilUser
	 * @return <type>
	 */
	protected function initCourseMemberObject()
	{
		global $ilUser;

		include_once "./Modules/Course/classes/class.ilCourseParticipant.php";
		$this->member_obj = ilCourseParticipant::_getInstanceByObjId($this->getId(),$ilUser->getId());
		return true;
	}

	/**
	 * Init course member object
	 * @global ilObjUser $ilUser
	 * @return <type>
	 */
	protected function initCourseMembersObject()
	{
		global $ilUser;

		include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
		$this->members_obj = ilCourseParticipants::_getInstanceByObjId($this->getId());
		return true;
	}

	/**
	 * Get course member object
	 * @return ilCourseParticipant
	 */
	public function getMemberObject()
	{
		if(!$this->member_obj instanceof ilCourseParticipant)
		{
			$this->initCourseMemberObject();
		}
		return $this->member_obj;
	}

	/**
	 * @deprecated
	 * @return ilCourseParticipants
	 */
	public function getMembersObject()
	{
		if(!$this->members_obj instanceof ilCourseParticipants)
		{
			$this->initCourseMembersObject();
		}
		return $this->members_obj;
	}

	function initCourseArchiveObject()
	{
		include_once "./Modules/Course/classes/class.ilCourseArchives.php";

		if(!is_object($this->archives_obj))
		{
			$this->archives_obj =& new ilCourseArchives($this);
		}
		return true;
	}
		


	// RBAC METHODS
	function initDefaultRoles()
	{
		global $rbacadmin,$rbacreview,$ilDB;

		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role = ilObjRole::createDefaultRole(
				'il_crs_admin_'.$this->getRefId(),
				"Admin of crs obj_no.".$this->getId(),
				'il_crs_admin',
				$this->getRefId()
		);
		$role = ilObjRole::createDefaultRole(
				'il_crs_tutor_'.$this->getRefId(),
				"Tutor of crs obj_no.".$this->getId(),
				'il_crs_tutor',
				$this->getRefId()
		);
		$role = ilObjRole::createDefaultRole(
				'il_crs_member_'.$this->getRefId(),
				"Member of crs obj_no.".$this->getId(),
				'il_crs_member',
				$this->getRefId()
		);
		
		// Break inheritance, create local roles and initialize permission
		// settings depending on course status.
		$this->__setCourseStatus();

		return array();
	}

	/**
	* set course status
	*
	* Grants permissions on the course object for all parent roles.  
	* Each permission is granted by computing the intersection of the role 
	* template il_crs_non_member and the permission template of 
	* the parent role.
	*
	* Creates linked roles in the local role folder object for all 
	* parent roles and initializes their permission templates.
	* Each permission template is initialized by computing the intersection 
	* of the role template il_crs_non_member and the permission
	* template of the parent role.
	*
	* @access	private
	*/
	function __setCourseStatus()
	{
		global $rbacadmin, $rbacreview, $rbacsystem;


		//define all relevant roles for which rights are needed to be changed
		$arr_parentRoles = $rbacreview->getParentRoleIds($this->getRefId());
		$arr_relevantParentRoleIds = array_diff(array_keys($arr_parentRoles),$this->getDefaultCourseRoles());

		$template_id = $this->__getCrsNonMemberTemplateId();

		//get defined operations from template
		if (is_null($template_id))
		{
			$template_ops = array();
		} 
		else 
		{
			$template_ops = $rbacreview->getOperationsOfRole($template_id, 'crs', ROLE_FOLDER_ID);
		}

		foreach ($arr_relevantParentRoleIds as $parentRole)
		{
			if ($rbacreview->isProtected($arr_parentRoles[$parentRole]['parent'],$parentRole))
			{
				continue;
			}
				
			$granted_permissions = array();

			// Delete the linked role for the parent role
			// (just in case if it already exists).
			$rbacadmin->deleteLocalRole($parentRole,$this->getRefId());

			// Grant permissions on the course object for 
			// the parent role. In the foreach loop we
			// compute the intersection of the role     
			// template il_crs_non_member and the 
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
			// il_crs_non_member and the permission
			// template of the parent role
			if (! is_null($template_id))
			{
				$rbacadmin->copyRolePermissionIntersection(
					$template_id, ROLE_FOLDER_ID, 
					$parentRole, $arr_parentRoles[$parentRole]['parent'], 
					$this->getRefId(), $parentRole
				);
			}
			$rbacadmin->assignRoleToFolder($parentRole,$this->getRefId(),"false");
		}//END foreach
	}

	/**
	* get course non-member template
	* @access	private
	* @param	return obj_id of roletemplate containing permissionsettings for 
	*           non-member roles of a course.
	*/
	function __getCrsNonMemberTemplateId()
	{
		global $ilDB;
		
		$q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_crs_non_member'";
		$res = $this->ilias->db->query($q);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);

		return $row["obj_id"];
	}
	
	/**
	* get ALL local roles of course, also those created and defined afterwards
	* only fetch data once from database. info is stored in object variable
	* @access	public
	* @return	return array [title|id] of roles...
	*/
	public function getLocalCourseRoles($a_translate = false)
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
					$role_Obj = $this->ilias->obj_factory->getInstanceByObjId($role_id);

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
	* get default course roles, returns the defaultlike create roles 
	* il_crs_tutor, il_crs_admin and il_crs_member
	* @access	public
	* @param 	returns the obj_ids of course specific roles in an associative
    *           array.
	*			key=descripiton of the role (i.e. "il_crs_tutor", "il_crs_admin", "il_crs_member".
	*			value=obj_id of the role
	*/
	public function getDefaultCourseRoles($a_crs_id = "")
	{
		global $rbacadmin, $rbacreview;

		if (strlen($a_crs_id) > 0)
		{
			$crs_id = $a_crs_id;
		}
		else
		{
			$crs_id = $this->getRefId();
		}

		$role_arr  = $rbacreview->getRolesOfRoleFolder($crs_id);

		foreach ($role_arr as $role_id)
		{
			$role_Obj =& $this->ilias->obj_factory->getInstanceByObjId($role_id);

			$crs_Member ="il_crs_member_".$crs_id;
			$crs_Admin  ="il_crs_admin_".$crs_id;
			$crs_Tutor  ="il_crs_tutor_".$crs_id;

			if (strcmp($role_Obj->getTitle(), $crs_Member) == 0 )
			{
				$arr_crsDefaultRoles["crs_member_role"] = $role_Obj->getId();
			}

			if (strcmp($role_Obj->getTitle(), $crs_Admin) == 0)
			{
				$arr_crsDefaultRoles["crs_admin_role"] = $role_Obj->getId();
			}

			if (strcmp($role_Obj->getTitle(), $crs_Tutor) == 0)
			{
				$arr_crsDefaultRoles["crs_tutor_role"] = $role_Obj->getId();
			}
		}

		return $arr_crsDefaultRoles;
	}
	
	function __getLocalRoles()
	{
		global $rbacreview;

		// GET role_objects of predefined roles
		
		return $rbacreview->getRolesOfRoleFolder($this->getRefId(),false);
	}

	function __deleteSettings()
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_settings ".
			"WHERE obj_id = ".$ilDB->quote($this->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		return true;
	}
	
	
	function getDefaultMemberRole()
	{
		$local_roles = $this->__getLocalRoles();

		foreach($local_roles as $role_id)
		{
			$title = ilObject::_lookupTitle($role_id);
			if(substr($title,0,8) == 'il_crs_m')
			{
				return $role_id;
			}
		}
		return 0;
	}
	function getDefaultTutorRole()
	{
		$local_roles = $this->__getLocalRoles();

		foreach($local_roles as $role_id)
		{
			if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false))
			{
				if(!strcmp($tmp_role->getTitle(),"il_crs_tutor_".$this->getRefId()))
				{
					return $role_id;
				}
			}
		}
		return false;
	}
	function getDefaultAdminRole()
	{
		$local_roles = $this->__getLocalRoles();

		foreach($local_roles as $role_id)
		{
			if($tmp_role =& ilObjectFactory::getInstanceByObjId($role_id,false))
			{
				if(!strcmp($tmp_role->getTitle(),"il_crs_admin_".$this->getRefId()))
				{
					return $role_id;
				}
			}
		}
		return false;
	}

	function _deleteUser($a_usr_id)
	{
		// Delete all user related data
		// delete lm_history
		include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
		ilCourseLMHistory::_deleteUser($a_usr_id);

		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		ilCourseParticipants::_deleteUser($a_usr_id);

		// Course objectives
		include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
		ilLOUserResults::deleteResultsForUser($a_usr_id);		
	}
	
	/**
	 * Overwriten Metadata update listener for ECS functionalities
	 *
	 * @access public
	 * 
	 */
	public function MDUpdateListener($a_element)
	{
	 	global $ilLog;
	 	
	 	parent::MDUpdateListener($a_element);
	 	
	 	switch($a_element)
	 	{
	 		case 'General':				
				// Update ecs content
				include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
				$ecs = new ilECSCourseSettings($this);
				$ecs->handleContentUpdate();
	 			break;
				
	 		default:
	 			return true;
	 	}
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
		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		switch($a_mode)
		{
			case 'create':
			case 'update':
				if(!$this->getActivationUnlimitedStatus() and !$this->getOfflineStatus())
				{
					$app = new ilCalendarAppointmentTemplate(self::CAL_ACTIVATION_START);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('crs_cal_activation_start');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart(new ilDateTime($this->getActivationStart(),IL_CAL_UNIX));
					$apps[] = $app;

					$app = new ilCalendarAppointmentTemplate(self::CAL_ACTIVATION_END);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('crs_cal_activation_end');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart(new ilDateTime($this->getActivationEnd(),IL_CAL_UNIX));
					$apps[] = $app;
				}
				if($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED)
				{
					$app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('crs_cal_reg_start');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart(new ilDateTime($this->getSubscriptionStart(),IL_CAL_UNIX));
					$apps[] = $app;

					$app = new ilCalendarAppointmentTemplate(self::CAL_REG_END);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('crs_cal_reg_end');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart(new ilDateTime($this->getSubscriptionEnd(),IL_CAL_UNIX));
					$apps[] = $app;
				}
				if($this->getCourseStart() && $this->getCourseEnd())
				{
					$app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_START);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('crs_start');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart($this->getCourseStart());
					$app->setFullday(true);
					$apps[] = $app;

					$app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_END);
					$app->setTitle($this->getTitle());
					$app->setSubtitle('crs_end');
					$app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
					$app->setDescription($this->getLongDescription());	
					$app->setStart($this->getCourseEnd());
					$app->setFullday(true);
					$apps[] = $app;
				}
				
				
				return $apps ? $apps : array();
				
			case 'delete':
				// Nothing to do: The category and all assigned appointments will be deleted.
				return array();
		}
	}
	
	###### Interface ilMembershipRegistrationCodes
	/**
	 * @see interface.ilMembershipRegistrationCodes
	 * @return array obj ids
	 */
	public static function lookupObjectsByCode($a_code)
	{
		global $ilDB;
		
		$query = "SELECT obj_id FROM crs_settings ".
			"WHERE reg_ac_enabled = ".$ilDB->quote(1,'integer')." ".
			"AND reg_ac = ".$ilDB->quote($a_code,'text');
		$res = $ilDB->query($query);
		
		$obj_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
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
	public function register($a_user_id,$a_role = ilCourseConstants::CRS_MEMBER, $a_force_registration = false)
	{
		global $ilCtrl, $tree;
		include_once './Services/Membership/exceptions/class.ilMembershipRegistrationException.php';
		include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
		$part = ilCourseParticipants::_getInstanceByObjId($this->getId());

		if($part->isAssigned($a_user_id))
		{
			return true;
		}
		
		if(!$a_force_registration)
		{
			// Availability
			if($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_DEACTIVATED)
			{
				include_once './Modules/Group/classes/class.ilObjGroupAccess.php';

				if(!ilObjCourseAccess::_usingRegistrationCode())
				{
					throw new ilMembershipRegistrationException('Cant registrate to course '.$this->getId().
						', course subscription is deactivated.', '456');
				}
			}

			// Time Limitation
			if($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED)
			{
				if( !$this->inSubscriptionTime() )
				{
					throw new ilMembershipRegistrationException('Cant registrate to course '.$this->getId().
						', course is out of registration time.', '789');
				}
			}

			// Max members
			if($this->isSubscriptionMembershipLimited())
			{
				$free = max(0,$this->getSubscriptionMaxMembers() - $part->getCountMembers());
				include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
				$waiting_list = new ilCourseWaitingList($this->getId());
				if($this->enabledWaitingList() and (!$free or $waiting_list->getCountUsers()))
				{
					$waiting_list->addToList($a_user_id);
					$this->lng->loadLanguageModule("crs");
					$info = sprintf($this->lng->txt('crs_added_to_list'),
						$waiting_list->getPosition($a_user_id));
					include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
					$participants = ilCourseParticipants::_getInstanceByObjId($this->getId());
					$participants->sendNotification($participants->NOTIFY_WAITING_LIST,$a_user_id);

					throw new ilMembershipRegistrationException($info, '124');
				}

				if(!$this->enabledWaitingList() && !$free)
				{
					throw new ilMembershipRegistrationException('Cant registrate to course '.$this->getId().
						', membership is limited.', '123');
				}
			}
		}
		
		$part->add($a_user_id,$a_role);
		$part->sendNotification($part->NOTIFY_ACCEPT_USER, $a_user_id);
		$part->sendNotification($part->NOTIFY_ADMINS,$a_user_id);
		
		
		include_once './Modules/Forum/classes/class.ilForumNotification.php';
		ilForumNotification::checkForumsExistsInsert($this->getRefId(), $a_user_id);
		
		return true;
	}

	/**
	 * Returns automatic notification status from 
	 * $this->auto_notification
	 * 
	 * @return boolean
	 */
	public function getAutoNotification()
	{
		return $this->auto_notification;
	}


	/**
	 * Sets automatic notification status in $this->auto_notification,
	 * using given $status.
	 *
	 * @param mixed boolean
	 */
	public function setAutoNotification($value)
	{
		$this->auto_notification = $value;
	}
	
	/**
	 * Set status determination mode
	 * 
	 * @param int $a_value 
	 */
	public function setStatusDetermination($a_value)
	{
		$a_value = (int)$a_value;
		
		// #13905
		if($a_value == self::STATUS_DETERMINATION_LP)				
		{
			include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
			if(!ilObjUserTracking::_enabledLearningProgress())
			{			
				$a_value = self::STATUS_DETERMINATION_MANUAL;
			}
		}
		
		$this->status_dt = $a_value;
	}
	
	/**
	 * Get status determination mode
	 * 
	 * @return int
	 */
	public function getStatusDetermination()
	{
		return $this->status_dt;
	}	
		
	/**
	 * Set course status for all members by lp status
	 */
	public function syncMembersStatusWithLP()
	{
		include_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";
		foreach($this->getMembersObject()->getParticipants() as $user_id)
		{
		    // #15529 - force raise on sync
		    ilLPStatusWrapper::_updateStatus($this->getId(), $user_id, null, false, true);			
		}				
	}
			
	/**
	 * sync course status from lp 
	 * 
	 * as lp data is not deleted on course exit new members may already have lp completed
	 * 
	 * @param int $a_member_id
	 */
	public function checkLPStatusSync($a_member_id)
	{
		// #11113
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress() &&
			$this->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP)
		{			
			include_once("Services/Tracking/classes/class.ilLPStatus.php");	
			// #13811 - we need to suppress creation if status entry
			$has_completed = (ilLPStatus::_lookupStatus($this->getId(), $a_member_id, false) == ilLPStatus::LP_STATUS_COMPLETED_NUM);
			$this->getMembersObject()->updatePassed($a_member_id, $has_completed, false, true);					
		}		
	}		
	
	function getOrderType()
	{
		if($this->enabledObjectiveView())
		{
			return ilContainer::SORT_MANUAL;
		}
		return parent::getOrderType();
	}
	
	public function handleAutoFill()
	{	
		if($this->enabledWaitingList() &&
			$this->hasWaitingListAutoFill())
		{
			$max = $this->getSubscriptionMaxMembers();
			$now = ilCourseParticipants::lookupNumberOfMembers($this->getRefId());
			if($max > $now)
			{
				// see assignFromWaitingListObject()
				include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
				$waiting_list = new ilCourseWaitingList($this->getId());

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
					$this->getMembersObject()->add($user_id,IL_CRS_MEMBER);
					$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_USER,$user_id);
					$waiting_list->removeFromList($user_id);

					$this->checkLPStatusSync($user_id);

					$now++;
					if($now >= $max)
					{
						break;
					}
				}
			}
		}		
	}
	
	public static function mayLeave($a_course_id, $a_user_id = null, &$a_date = null)
	{
		global $ilUser, $ilDB;
		
		if(!$a_user_id)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$set = $ilDB->query("SELECT leave_end".
			" FROM crs_settings".
			" WHERE obj_id = ".$ilDB->quote($a_course_id, "integer"));
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
	
	public static function findCoursesWithNotEnoughMembers()
	{
		global $ilDB;
		
		$res = array();
		
		$now = time();
		
		include_once "Modules/Course/classes/class.ilCourseParticipants.php";
		
		$set = $ilDB->query("SELECT obj_id, min_members".
			" FROM crs_settings".
			" WHERE min_members > ".$ilDB->quote(0, "integer").
			" AND sub_mem_limit = ".$ilDB->quote(1, "integer"). // #17206
			" AND ((leave_end IS NOT NULL".
				" AND leave_end < ".$ilDB->quote($now, "text").")".
				" OR (leave_end IS NULL".
				" AND sub_end IS NOT NULL".
				" AND sub_end < ".$ilDB->quote($now, "text")."))".
			" AND (crs_start IS NULL OR crs_start > ".$ilDB->quote($now, "integer").")");
		while($row = $ilDB->fetchAssoc($set))
		{
			$part = new ilCourseParticipants($row["obj_id"]);
			$reci = $part->getNotificationRecipients();
			if(sizeof($reci))
			{
				$missing = (int)$row["min_members"]-$part->getCountMembers();
				if($missing > 0)
				{
					$res[$row["obj_id"]] = array($missing, $reci);		
				}
			}
		}
		
		return $res;
	}
	
} //END class.ilObjCourse
?>
