<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
include_once './Services/Membership/classes/class.ilMembershipRegistrationSettings.php';

/**
* @defgroup ModulesSession Modules/Session
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilObjSession extends ilObject
{
	const LOCAL_ROLE_PARTICIPANT_PREFIX = 'il_sess_participant';
	
	const CAL_REG_START = 1;

	// cat-tms-patch start
	const TUTOR_CFG_MANUALLY = 0;
	const TUTOR_CFG_FROMCOURSE = 1;
	// cat-tms-patch end

	protected $db;

	protected $location;
	protected $name;
	protected $phone;
	protected $email;
	protected $details;
	protected $registration;
	protected $event_id;

	protected $reg_type = ilMembershipRegistrationSettings::TYPE_NONE;
	protected $reg_limited = 0;
	protected $reg_min_users = 0;
	protected $reg_limited_users = 0;
	protected $reg_waiting_list = 0;
	protected $reg_waiting_list_autofill; // [bool]

	protected $appointments;
	protected $files = array();
	
	/**
	 * @var ilLogger
	 */
	protected $session_logger = null;
	

	// cat-tms-patch start
	/**
	 * @var int TUTOR_CFG_MANUALLY|TUTOR_CFG_FROMCOURSE
	 */
	protected $tutor_source;

	/**
	 * @var array<int,ilObjUser>
	 */
	protected $assigned_tutors=array();
	// cat-tms-patch end

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $ilDB;
		
		$this->session_logger = $GLOBALS['DIC']->logger()->sess();

		// cat-tms-patch start
		$this->db = $ilDB;
		$this->type = "sess";
		$this->g_user = $GLOBALS['DIC']->user();
		parent::__construct($a_id,$a_call_by_reference);
		// cat-tms-patch end
	}

	/**
	 * lookup registration enabled
	 *
	 * @access public
	 * @param
	 * @return
	 * @static
	 */
	public static function _lookupRegistrationEnabled($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT reg_type FROM event ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			return (bool) $row->reg_type != ilMembershipRegistrationSettings::TYPE_NONE;
		}
		return false;
	}

	/**
	 * Get session data
	 * @param object $a_obj_id
	 * @return
	 */
	public static function lookupSession($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id);
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$data['location'] 	= $row->location ? $row->location : '';
			$data['details']	= $row->details ? $row->details : '';
			$data['name']		= $row->tutor_name ? $row->tutor_name : '';
			$data['email']		= $row->tutor_email ? $row->tutor_email : '';
			$data['phone']		= $row->tutor_phone ? $row->tutor_phone : '';
			// cat-tms-patch start
			$data['tutor_source'] = $row->tutor_source;
			if($row->tutor_source == self::TUTOR_CFG_FROMCOURSE) {
				$data['tutor_ids'] = self::lookupTutorReferences($a_obj_id);
				self::addTutorInformation($data);
			}
			// cat-tms-patch end
		}
		return (array) $data;
	}
	// cat-tms-patch start
	/**
	 * Get tutor data from event_tutor
	 *
	 * @param array 	$data
	 */
	protected static function addTutorInformation(&$data)
	{
		foreach ($data['tutor_ids'] as $tutor_id) {
			$tutor = new ilObjUser($tutor_id);
			$data['tutor']["name"][] = $tutor->getFullName();
		}
	}
	// cat-tms-patch end

	/**
	 * get title
	 * (overwritten from base class)
	 *
	 * @access public
	 * @return
	 */
	public function getPresentationTitle()
	{
		$date = new ilDate($this->getFirstAppointment()->getStart()->getUnixTime(),IL_CAL_UNIX);
		if($this->getTitle())
		{
			return ilDatePresentation::formatDate($this->getFirstAppointment()->getStart()).': '.$this->getTitle();
		}
		else
		{
			return ilDatePresentation::formatDate($date);
		}

	}
	
	/**
	 * Create local session participant role
	 */
	public function initDefaultRoles()
	{
		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		$role = ilObjRole::createDefaultRole(
			self::LOCAL_ROLE_PARTICIPANT_PREFIX.'_'.$this->getRefId(),
			'Participant of session obj_no.'.$this->getId(),
			self::LOCAL_ROLE_PARTICIPANT_PREFIX,
			$this->getRefId()
		);
		
		if(!$role instanceof ilObjRole)
		{
			$this->session_logger->warning('Could not create default session role.');
			$this->session_logger->logStack(ilLogLevel::WARNING);
		}
		return array();
	}
	
	/**
	 * sget event id
	 *
	 * @access public
	 * @return
	 */
	public function getEventId()
	{
		return $this->event_id;
	}

	/**
	 * set location
	 *
	 * @access public
	 * @param string location
	 */
	public function setLocation($a_location)
	{
		$this->location = $a_location;
	}

	/**
	 * get location
	 *
	 * @access public
	 * @return string location
	 */
	public function getLocation()
	{
		return $this->location;
	}

	/**
	 * set name
	 *
	 * @access public
	 * @param string name
	 */
	public function setName($a_name)
	{
		$this->name = $a_name;
	}

	/**
	 * get name
	 *
	 * @access public
	 * @return string name
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * set phone
	 *
	 * @access public
	 * @param string phone
	 */
	public function setPhone($a_phone)
	{
		$this->phone = $a_phone;
	}

	/**
	 * get phone
	 *
	 * @access public
	 * @return string phone
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * set email
	 *
	 * @access public
	 * @param string email
	 * @return
	 */
	public function setEmail($a_email)
	{
		$this->email = $a_email;
	}

	/**
	 * get email
	 *
	 * @access public
	 * @return string email
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * check if there any tutor settings
	 *
	 * @access public
	 */
	public function hasTutorSettings()
	{
		return strlen($this->getName()) or
			strlen($this->getEmail()) or
			strlen($this->getPhone())
		// cat-tms-patch start
			or $this->getTutorSource() === self::TUTOR_CFG_FROMCOURSE;
		// cat-tms-patch end
	}

	/**
	 * set details
	 *
	 * @access public
	 * @param string details
	 */
	public function setDetails($a_details)
	{
		$this->details = $a_details;
	}

	/**
	 * get details
	 *
	 * @access public
	 * @return string details
	 */
	public function getDetails()
	{
		return $this->details;
	}

	public function setRegistrationType($a_type)
	{
		$this->reg_type = $a_type;
	}

	public function getRegistrationType()
	{
		return $this->reg_type;
	}

	public function isRegistrationUserLimitEnabled()
	{
		return $this->reg_limited;
	}

	public function enableRegistrationUserLimit($a_limit)
	{
		$this->reg_limited = $a_limit;
	}

	public function getRegistrationMinUsers()
	{
		return $this->reg_min_users;
	}

	public function setRegistrationMinUsers($a_users)
	{
		$this->reg_min_users = $a_users;
	}

	public function getRegistrationMaxUsers()
	{
		return $this->reg_limited_users;
	}

	public function setRegistrationMaxUsers($a_users)
	{
		$this->reg_limited_users = $a_users;
	}

	public function isRegistrationWaitingListEnabled()
	{
		return $this->reg_waiting_list;
	}

	public function enableRegistrationWaitingList($a_stat)
	{
		$this->reg_waiting_list = $a_stat;
	}

	public function setWaitingListAutoFill($a_value)
	{
		$this->reg_waiting_list_autofill = (bool)$a_value;
	}

	public function hasWaitingListAutoFill()
	{
		return (bool)$this->reg_waiting_list_autofill;
	}

	/**
	 * is registration enabled
	 *
	 * @access public
	 * @return
	 */
	public function enabledRegistration()
	{
		return $this->reg_type != ilMembershipRegistrationSettings::TYPE_NONE;
	}

	/**
	 * get appointments
	 *
	 * @access public
	 * @return array
	 */
	public function getAppointments()
	{
		return $this->appointments ? $this->appointments : array();
	}

	/**
	 * add appointment
	 *
	 * @access public
	 * @param object ilSessionAppointment
	 * @return
	 */
	public function addAppointment($appointment)
	{
		$this->appointments[] = $appointment;
	}

	/**
	 * set appointments
	 *
	 * @access public
	 * @param array ilSessionAppointments
	 * @return
	 */
	public function setAppointments($appointments)
	{
		$this->appointments = $appointments;
	}

	/**
	 * get first appointment
	 *
	 * @access public
	 * @return  ilSessionAppointment
	 */
	public function getFirstAppointment()
	{
		return is_object($this->appointments[0]) ? $this->appointments[0] : ($this->appointments[0] = new ilSessionAppointment());
	}

	/**
	 * get files
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getFiles()
	{
		return $this->files ? $this->files : array();
	}

	/**
	 * validate
	 *
	 * @access public
	 * @param
	 * @return bool
	 */
	public function validate()
	{
		global $ilErr;

		// #17114
		if($this->isRegistrationUserLimitEnabled() &&
			!$this->getRegistrationMaxUsers())
		{
			$ilErr->appendMessage($this->lng->txt("sess_max_members_needed"));
			return false;
		}

		return true;
	}

	/**
	 * Clone course (no member data)
	 *
	 * @access public
	 * @param int target ref_id
	 * @param int copy id
	 *
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0, $a_omit_tree = false)
	{
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id, $a_omit_tree);

	 	$this->read();
	 	
		$this->cloneSettings($new_obj);
	 	$this->cloneMetaData($new_obj);
	 	
		
		// Clone appointment
		$new_app = $this->getFirstAppointment()->cloneObject($new_obj->getId());
		$new_obj->setAppointments(array($new_app));
		$new_obj->update(true);

		// Clone session files
		foreach($this->files as $file)
		{
			$file->cloneFiles($new_obj->getEventId());
		}

		// Raise update forn new appointments



		// Copy learning progress settings
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
		$obj_settings = new ilLPObjSettings($this->getId());
		$obj_settings->cloneSettings($new_obj->getId());
		unset($obj_settings);

		return $new_obj;
	}

	/**
	 * clone settings
	 *
	 * @access public
	 * @param ilObjSession
	 * @return
	 */
	public function cloneSettings(ilObjSession $new_obj)
	{
		// @var
		$new_obj->setLocation($this->getLocation());
		$new_obj->setName($this->getName());
		$new_obj->setPhone($this->getPhone());
		$new_obj->setEmail($this->getEmail());
		$new_obj->setDetails($this->getDetails());

		$new_obj->setRegistrationType($this->getRegistrationType());
		$new_obj->enableRegistrationUserLimit($this->isRegistrationUserLimitEnabled());
		$new_obj->enableRegistrationWaitingList($this->isRegistrationWaitingListEnabled());
		$new_obj->setWaitingListAutoFill($this->hasWaitingListAutoFill());
		$new_obj->setRegistrationMinUsers($this->getRegistrationMinUsers());
		$new_obj->setRegistrationMaxUsers($this->getRegistrationMaxUsers());
		
		$new_obj->update(true);
		
		return true;
	}

	/**
	 * Clone dependencies
	 *
	 * @param int target id ref_id of new session
	 * @param int copy_id
	 * @return
	 */
	public function cloneDependencies($a_target_id,$a_copy_id)
	{
		global $ilObjDataCache;

		parent::cloneDependencies($a_target_id,$a_copy_id);

		$target_obj_id = $ilObjDataCache->lookupObjId($a_target_id);

		include_once('./Modules/Session/classes/class.ilEventItems.php');
		$session_materials = new ilEventItems($target_obj_id);
		$session_materials->cloneItems($this->getId(),$a_copy_id);

		return true;
	}

	// cat-tms-patch end

	// for course creation
	/**
	 * Will be called after course creation with configuration options.
	 *
	 * @param	mixed	$config
	 * @return	void
	 */
	public function afterCourseCreation($config) {
		foreach ($config as $key => $value) {
			if($key == "session_time") {
				$this->updateFromConfig($value);
			} else if($key == "update_from_agenda") {
				$this->updateFromAgenda();
			}
			else {
				throw new \RuntimeException("Can't process configuration '$key'");
			}
		}
	}

	/**
	 * Update appointment from config values
	 *
	 * @param mixed 	$value
	 *
	 * @return void
	 */
	protected function updateFromConfig($value) {
		$appointment = $this->getFirstAppointment();
		$start_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "Y-m-d");
		$end_date = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "Y-m-d");

		$start_hh = $value["hh"];
		$start_mm = $value["mm"];

		$start_hour = $appointment->getStart()->get(IL_CAL_FKT_DATE, "H");
		$end_hour = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "H");
		$start_minutes = $appointment->getStart()->get(IL_CAL_FKT_DATE, "i");
		$end_minutes = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "i");

		$end_hh = (int)$start_hh + $end_hour - $start_hour;
		$end_mm = (int)$start_mm + $end_minutes - $start_minutes;

		if ($end_mm < 0) {
			$end_hh = $end_hh - 1;
			$end_mm = 60 + $end_mm;
		}

		if ($end_mm >= 60) {
			$end_hh = $end_hh + 1;
			$end_mm = $end_mm - 60;
		}

		$start_hh = str_pad($start_hh, 2, "0", STR_PAD_LEFT);
		$start_mm = str_pad($start_mm, 2, "0", STR_PAD_LEFT);
		$end_hh = str_pad($end_hh, 2, "0", STR_PAD_LEFT);
		$end_mm = str_pad($end_mm, 2, "0", STR_PAD_LEFT);

		$dt_start = $start_date." $start_hh:$start_mm:00";
		$new_start_date = new ilDateTime($dt_start, IL_CAL_DATETIME);

		$dt_end = $end_date." $end_hh:$end_mm:00";
		$new_end_date = new ilDateTime($dt_end, IL_CAL_DATETIME);

		$appointment->setStart($new_start_date);
		$appointment->setEnd($new_end_date);
		$appointment->update();
	}

	/**
	 * Updates the first appointmen according to references agenda
	 *
	 * @return void
	 */
	public function updateFromAgenda() {
		include_once('./Modules/Session/classes/class.ilEventItems.php');
		$event_items = (new \ilEventItems($this->getId()))->getItems();
		foreach ($event_items as $event_item) {
			if(\ilObject::_lookupType($event_item, true) == "xage") {
				$agenda = ilObjectFactory::getInstanceByRefId($event_item);
				$actions = $agenda->getAgendaEntryActions();
				$start_and_end = $actions->getDayStartAndEnd();
				if(is_null($start_and_end["start"]) || is_null($start_and_end["end"])) {
					continue;
				}

				$appointment = $this->getFirstAppointment();
				$start_date = $appointment->getStart()->get(IL_CAL_FKT_DATE, "Y-m-d");
				$end_date = $appointment->getEnd()->get(IL_CAL_FKT_DATE, "Y-m-d");

				$dt_start = $start_date." ".$start_and_end["start"];
				$new_start_date = new ilDateTime($dt_start, IL_CAL_DATETIME);

				$dt_end = $end_date." ".$start_and_end["end"];
				$new_end_date = new ilDateTime($dt_end, IL_CAL_DATETIME);

				$appointment->setStart($new_start_date);
				$appointment->setEnd($new_end_date);
				$appointment->update();
			}
		}
	}
	// cat-tms-patch end


	/**
	 * create new session
	 *
	 * @access public
	 */
	public function create($a_skip_meta_data = false)
	{
		global $ilDB;
		global $ilAppEventHandler;

		parent::create();
		
		if(!$a_skip_meta_data)
		{
			$this->createMetaData();
		}

		$next_id = $ilDB->nextId('event');
		$query = "INSERT INTO event (event_id,obj_id,location,tutor_name,tutor_phone,tutor_email,details,registration, ".
			// cat-tms-patch start
			//'reg_type, reg_limit_users, reg_limited, reg_waiting_list, reg_min_users, reg_auto_wait) '.
			'reg_type, reg_limit_users, reg_limited, reg_waiting_list, reg_min_users, reg_auto_wait, tutor_source) '.
			"VALUES( ".
			$ilDB->quote($next_id,'integer').", ".
			$this->db->quote($this->getId() ,'integer').", ".
			$this->db->quote($this->getLocation() ,'text').",".
			$this->db->quote($this->getName() ,'text').", ".
			$this->db->quote($this->getPhone() ,'text').", ".
			$this->db->quote($this->getEmail() ,'text').", ".
			$this->db->quote($this->getDetails() ,'text').",".
			$this->db->quote($this->enabledRegistration() ,'integer').", ".
			$this->db->quote($this->getRegistrationType(),'integer').', '.
			$this->db->quote($this->getRegistrationMaxUsers(),'integer').', '.
			$this->db->quote($this->isRegistrationUserLimitEnabled(),'integer').', '.
			$this->db->quote($this->isRegistrationWaitingListEnabled(),'integer').', '.
			$this->db->quote($this->getRegistrationMinUsers(),'integer').', '.
			// cat-tms-patch start
			//$this->db->quote($this->hasWaitingListAutoFill(),'integer').' '.
			$this->db->quote($this->hasWaitingListAutoFill(),'integer').', '.
			$this->db->quote($this->getTutorSource(),'integer')." ".
			// cat-tms-patch end
			")";
		$res = $ilDB->manipulate($query);

		// cat-tms-patch start
		if($this->getTutorSource() === self::TUTOR_CFG_FROMCOURSE) {
			$this->storeTutorReferences();
		}
		// cat-tms-patch end

		$this->event_id = $next_id;

		$ilAppEventHandler->raise('Modules/Session',
			'create',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareCalendarAppointments('create')));

		return $this->getId();
	}

	/**
	 * update object
	 *
	 * @access public
	 * @param
	 * @return bool success
	 */
	public function update($a_skip_meta_update = false)
	{
		global $ilDB;
		global $ilAppEventHandler;

		if(!parent::update())
		{
			return false;
		}
		if(!$a_skip_meta_update)
		{
			$this->updateMetaData();
		}
		
		$query = "UPDATE event SET ".
			"location = ".$this->db->quote($this->getLocation() ,'text').",".
			"tutor_name = ".$this->db->quote($this->getName() ,'text').", ".
			"tutor_phone = ".$this->db->quote($this->getPhone() ,'text').", ".
			"tutor_email = ".$this->db->quote($this->getEmail() ,'text').", ".
			"details = ".$this->db->quote($this->getDetails() ,'text').", ".
			"registration = ".$this->db->quote($this->enabledRegistration() ,'integer').", ".
			"reg_type = ".$this->db->quote($this->getRegistrationType() ,'integer').", ".
			"reg_limited = ".$this->db->quote($this->isRegistrationUserLimitEnabled() ,'integer').", ".
			"reg_limit_users = ".$this->db->quote($this->getRegistrationMaxUsers() ,'integer').", ".
			"reg_min_users = ".$this->db->quote($this->getRegistrationMinUsers() ,'integer').", ".
			"reg_waiting_list = ".$this->db->quote($this->isRegistrationWaitingListEnabled(),'integer').", ".
			"reg_auto_wait = ".$this->db->quote($this->hasWaitingListAutoFill(),'integer').", ".
			// cat-tms-patch start
			"tutor_source = ".$this->db->quote($this->getTutorSource(),'integer')." ".
			// cat-tms-patch end
			"WHERE obj_id = ".$this->db->quote($this->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		// cat-tms-patch start
		if($this->getTutorSource() === self::TUTOR_CFG_FROMCOURSE) {
			$this->storeTutorReferences();
		}
		// cat-tms-patch end



		$ilAppEventHandler->raise('Modules/Session',
			'update',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareCalendarAppointments('update')));
		return true;
	}

	/**
	 * delete session and all related data
	 *
	 * @access public
	 * @return bool
	 */
	public function delete()
	{
		global $ilDB;
		global $ilAppEventHandler;

		if(!parent::delete())
		{
			return false;
		}
		
		// delete meta data
		$this->deleteMetaData();
		
		$query = "DELETE FROM event ".
			"WHERE obj_id = ".$this->db->quote($this->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);

		// cat-tms-patch start
		$query = "DELETE FROM event_tutors ".
			"WHERE obj_id = ".$this->db->quote($this->getId() ,'integer')." ";
		$res = $ilDB->manipulate($query);
		// cat-tms-patch end

		include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
		ilSessionAppointment::_deleteBySession($this->getId());

		include_once('./Modules/Session/classes/class.ilEventItems.php');
		ilEventItems::_delete($this->getId());

		include_once('./Modules/Session/classes/class.ilEventParticipants.php');
		ilEventParticipants::_deleteByEvent($this->getId());

		foreach($this->getFiles() as $file)
		{
			$file->delete();
		}

		$ilAppEventHandler->raise('Modules/Session',
			'delete',
			array('object' => $this,
				'obj_id' => $this->getId(),
				'appointments' => $this->prepareCalendarAppointments('delete')));


		return true;
	}

	/**
	 * read session data
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function read()
	{
		parent::read();

		$query = "SELECT * FROM event WHERE ".
			"obj_id = ".$this->db->quote($this->getId() ,'integer')." ";
		$res = $this->db->query($query);

		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT))
		{
			$this->setLocation($row->location);
			$this->setName($row->tutor_name);
			$this->setPhone($row->tutor_phone);
			$this->setEmail($row->tutor_email);
			$this->setDetails($row->details);
			$this->setRegistrationType($row->reg_type);
			$this->enableRegistrationUserLimit($row->reg_limited);
			$this->enableRegistrationWaitingList($row->reg_waiting_list);
			$this->setWaitingListAutoFill($row->reg_auto_wait);
			$this->setRegistrationMaxUsers($row->reg_limit_users);
			$this->setRegistrationMinUsers($row->reg_min_users);
			$this->event_id = $row->event_id;
			// cat-tms-patch start
			$this->setTutorSource((int)$row->tutor_source);
			// cat-tms-patch end
		}
		// cat-tms-patch start
		$tids = $this->readTutorReferences();
		$this->setAssignedTutors($tids);
		// cat-tms-patch end

		$this->initAppointments();
		$this->initFiles();
	}


	/**
	 * init appointments
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initAppointments()
	{
		// get assigned appointments
		include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
		$this->appointments = ilSessionAppointment::_readAppointmentsBySession($this->getId());
	}

	/**
	 * init files
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	public function initFiles()
	{
		include_once('./Modules/Session/classes/class.ilSessionFile.php');
		$this->files = ilSessionFile::_readFilesByEvent($this->getEventId());
	}


	/**
	 * Prepare calendar appointments
	 *
	 * @access public
	 * @param string mode UPDATE|CREATE|DELETE
	 * @return
	 */
	public function prepareCalendarAppointments($a_mode = 'create')
	{
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php');

		switch($a_mode)
		{
			case 'create':
			case 'update':

				$app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
				$app->setTranslationType(IL_CAL_TRANSLATION_NONE);
				$app->setTitle($this->getTitle() ? $this->getTitle() : $this->lng->txt('obj_sess'));
				$app->setDescription($this->getLongDescription());

				$sess_app = $this->getFirstAppointment();
				$app->setFullday($sess_app->isFullday());
				$app->setStart($sess_app->getStart());
				$app->setEnd($sess_app->getEnd());
				$apps[] = $app;

				return $apps;

			case 'delete':
				// Nothing to do: The category and all assigned appointments will be deleted.
				return array();
		}
	}
	
	/**
	 * Handle auto fill for session members
	 */
	public function handleAutoFill()
	{	
		if(
			!$this->isRegistrationWaitingListEnabled() || 
			!$this->hasWaitingListAutoFill()
		)
		{
			$this->session_logger->debug('Waiting list or auto fill is disabled.');
			return true;
		}
		
		$parts = ilSessionParticipants::_getInstanceByObjId($this->getId());
		$current = $parts->getCountParticipants();
		$max = $this->getRegistrationMaxUsers();
		
		if($max <= $current)
		{
			$this->session_logger->debug('Maximum number of participants not reached.');
			$this->session_logger->debug('Maximum number of members: ' . $max);
			$this->session_logger->debug('Current number of members: ' . $current);
			return true;
		}
		
		$session_waiting_list = new ilSessionWaitingList($this->getId());
		foreach($session_waiting_list->getUserIds() as $user_id)
		{
			$user = ilObjectFactory::getInstanceByObjId($user_id);
			if(!$user instanceof ilObjUser)
			{
				$this->session_logger->warning('Found invalid user id on waiting list: ' . $user_id);
				continue;
			}
			if(in_array($user_id, $parts->getParticipants()))
			{
				$this->session_logger->notice('User on waiting list already session member: ' . $user_id);
			}
			
			if($this->enabledRegistration())
			{
				$this->session_logger->debug('Registration enabled: register user');
				$parts->register($user_id);
			}
			else
			{
				$this->session_logger->debug('Registration disabled: set user status to participated.');
				$parts->getEventParticipants()->updateParticipation($user_id, true);
			}
			
			$session_waiting_list->removeFromList($user_id);
			
			$current++;
			if($current >= $max)
			{
				break;
			}
		}
	}
	
	/**
	 * Get mail to members type
	 * @return int
	 */
	public function getMailToMembersType()
	{
		return false;
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
		include_once('./Modules/Session/classes/class.ilSessionParticipants.php');
		$this->members_obj = ilSessionParticipants::_getInstanceByObjId($this->getId());
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
	

	// cat-tms-patch start
	/**
	 * Checks whether this object is a child element of a course object.
	 * If there is an group object first in tree it returns false.
	 *
	 * @return int | null
	 */
	public function isCourseOrCourseChild($ref_id)
	{
		global $DIC;
		$g_tree = $DIC->repositoryTree();
		$tree = array_reverse($g_tree->getPathFull($ref_id));
		foreach ($tree as $leaf)
		{
			if($leaf['type'] === "grp")
			{
				return null;
			}
			if($leaf['type'] === "crs")
			{
				return $leaf['ref_id'];
			}
		}
		return null;
	}

	/**
	 * Calculate the start and endtime of a session object
	 * depending on parent course and offset
	 *
	 * @param int $offset - 1 means first day of course
	 * @param int $hour_start
	 * @param int $minute_start
	 * @param int $hour_end
	 * @param int $minute_end
	 * @return 	ilDateTime[]
	 */
	public function getStartTimeDependingOnCourse($offset, $hour_start, $minute_start, $hour_end, $minute_end)
	{
		$ref_id = $this->getRefId();
		//during creation:
		if(! $ref_id) {
			$ref_id = $_GET['ref_id'];
		}
		$crs_id = $this->isCourseOrCourseChild($ref_id);
		$crs = ilObjectFactory::getInstanceByRefId($crs_id);
		$start = $crs->getCourseStart();
		if($start === null)
		{
			return $this->getTodayWithTimes($hour_start, $minute_start, $hour_end, $minute_end);
		}
		return $this->calcCourseDateTime($start, $offset, $hour_start, $minute_start, $hour_end, $minute_end);
	}

	/**
	 * Get start and end date of today
	 *
	 * @param int 	$hour_start
	 * @param int 	$minute_start
	 * @param int 	$hour_end
	 * @param int 	$minute_end
	 *
	 * @return ilDateTime[]
	 */
	protected function getTodayWithTimes($hour_start, $minute_start, $hour_end, $minute_end) {
		$start = $this->getDateWithTime(date("Y-m-d"), $hour_start, $minute_start);
		$end = $this->getDateWithTime(date("Y-m-d"), $hour_end, $minute_end);

		return [$start, $end];
	}

	/**
	 * Calculate the start and endtime of a session object
	 * depending on days_offset
	 *
	 * @param ilDateTime 	$start
	 * @param ilDateTime 	$end
	 * @param int $offset - 1 means first day of course
	 * @param int 	$hour_start
	 * @param int 	$minute_start
	 * @param int 	$hour_end
	 * @param int 	$minute_end
	 *
	 * @return ilDateTime[]
	 */
	private function calcCourseDateTime(ilDateTime $start, $offset, $hour_start, $minute_start, $hour_end, $minute_end)
	{
		$offset--;

		$start = $this->getDateWithTime($start->get(IL_CAL_FKT_DATE, "Y-m-d"), $hour_start, $minute_start);
		$end = $this->getDateWithTime($start->get(IL_CAL_FKT_DATE, "Y-m-d"), $hour_end, $minute_end);

		if ($offset != 0) {
			$start->increment(ilDateTime::DAY, $offset);
			$end->increment(ilDateTime::DAY, $offset);
		}

		return [$start, $end];
	}

	/**
	 * Create a datetime with times
	 *
	 * @param string 	$date
	 * @param int 	$hour
	 * @param int 	$minute
	 *
	 * @return ilDateTime
	 */
	protected function getDateWithTime($date, $hour, $minute) {
		$start_datetime = $date." ".$this->addLeading($hour).":".$this->addLeading($minute).":00";
		return new ilDateTime($start_datetime, IL_CAL_DATETIME, $this->g_user->getTimeZone());
	}

	/**
	 * Adds leading 0
	 *
	 * @param string | int	$value
	 * @param string 	$leading
	 *
	 * @return string
	 */
	protected function addLeading($value) {
		return str_pad((string)$value, 2, "0", STR_PAD_LEFT);
	}
	// cat-tms-patch end

	/**
	 * How should the tutors be configured?
	 *
	 * @param int $tutor_source
	 */
	public function setTutorSource($tutor_source) {
		if($tutor_source !== self::TUTOR_CFG_MANUALLY
			&& $tutor_source !== self::TUTOR_CFG_FROMCOURSE) {
			throw new \InvalidArgumentException('ilObjSession::setTutorSource - invalid source: ' .$tutor_source);
		}
		$this->tutor_source = $tutor_source;
	}

	/**
	 * How are the tutors configured?
	 *
	 * @return int
	 */
	public function getTutorSource() {
		// cat-tms-patch start
		if(is_null($this->tutor_source)) {
			$this->setTutorSource(self::TUTOR_CFG_MANUALLY);
		}
		// cat-tms-patch end
		return $this->tutor_source;
	}

	/**
	 * Get a list of users that are assigned as tutors at the course
	 * this session lives in.
	 *
	 * @global type $tree
	 * @return ilObjUser[]
	 */
	public function getParentCourseTutors() {
		global $tree;

		$ref_id = $this->getRefId();
		//during creation:
		if(! $ref_id) {
			$ref_id = $_GET['ref_id'];
		}

		$parent = $tree->getNodeData($ref_id);

		while($parent['type'] !== 'crs') {
			if(! $parent['ref_id'] || $parent['type']=='root') {
				return [];
			}
			$parent = $tree->getParentNodeData($parent['ref_id']);
		}

		$crs = ilObjectFactory::getInstanceByObjId($parent['obj_id'],false);
		$members = $crs->getMembersObject();
		$tutors = []; 
		foreach($members->getTutors() as $user_id) {
			$tutors[] = ilObjectFactory::getInstanceByObjId($user_id,false);
		}
		return $tutors;
	}

	/**
	 * Add a tutor.
	 *
	 * @param int $usr_id
	 */
	public function addAssignedTutor($usr_id) {
		assert('is_integer($usr_id)');
		$this->assigned_tutors[$usr_id] = \ilObjectFactory::getInstanceByObjId($usr_id, false);
	}

	/**
	 * Re-set tutors.
	 *
	 * @param int[] $usr_ids
	 */
	public function setAssignedTutors(array $usr_ids) {
		$this->assigned_tutors = array();
		foreach ($usr_ids as $usr_id) {
			$this->addAssignedTutor((int)$usr_id);
		}
	}

	/**
	 * Get all assigned tutors.
	 *
	 * @return ilObjUser[]
	 */
	public function getAssignedTutors() {
		return array_values($this->assigned_tutors);
	}
	/**
	 * Get ids of all assigned tutors.
	 *
	 * @return int[]
	 */
	public function getAssignedTutorsIds() {
		return array_keys($this->assigned_tutors);
	}

	/**
	 * Get assigned tutors from DB.
	 *
	 * @return int[]
	 */
	private function readTutorReferences() {
		return self::lookupTutorReferences($this->getId());
	}

	/**
	 * Get assigned tutors from DB.
	 *
	 * @param int $a_obj_id
	 * @return int[]
	 */
	private static function lookupTutorReferences($a_obj_id) {
		global $ilDB;
		$tids = array();
		$query = "SELECT usr_id FROM event_tutors WHERE ".
			"obj_id = ".$ilDB->quote($a_obj_id,'integer')." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
			$tids[] = (int)$row->usr_id;
		}
		return $tids;
	}

	/**
	 * Store assigned tutors in DB
	 */
	private function storeTutorReferences() {
		global $ilDB;
		$query = "DELETE FROM event_tutors WHERE obj_id = ".$this->db->quote($this->getId() ,'integer');
		$this->db->manipulate($query);

		foreach ($this->getAssignedTutors() as $usr) {
			$query = "INSERT INTO  event_tutors (id, obj_id, usr_id) VALUES ("
				.$ilDB->nextId('event_tutors')
				.', '.$this->db->quote($this->getId() ,'integer')
				.', '.$this->db->quote($usr->getId() ,'integer')
				.")";
			$this->db->manipulate($query);
		}
	}
	// cat-tms-patch end
}

?>
