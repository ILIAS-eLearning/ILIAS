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

include_once('./Modules/Session/classes/class.ilSessionAppointment.php');

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
	const CAL_REG_START = 1;
	
	protected $db;
	
	protected $location;
	protected $name;
	protected $phone;
	protected $email;
	protected $details;
	protected $registration;
	protected $event_id;
	
	protected $appointments;
	protected $files = array();
	

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		global $ilDB;
		
		$this->type = "sess";
		parent::__construct($a_id,$a_call_by_reference);
		
		$this->db = $ilDB;
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
		
		$query = "SELECT registration FROM event ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return (bool) $row->registration;
		}
		return false;
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
			strlen($this->getPhone());
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
	
	/**
	 * enable registration
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function enableRegistration($a_registration)
	{
		$this->registration = (bool) $a_registration;
	}
	
	/**
	 * is registration enabled
	 *
	 * @access public
	 * @return
	 */
	public function enabledRegistration()
	{
		return (bool) $this->registration;
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
	public function setAppointments()
	{
		$this->appointments = $appointments;
	}

	/**
	 * get first appointment
	 *
	 * @access public
	 * @return object ilSessionAppointment
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
		
		if(!strlen($this->getTitle()))
		{
			$ilErr->appendMessage($this->lng->txt('fill_out_all_required_fields'));
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
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB,$ilUser;
		
	 	$new_obj = parent::cloneObject($a_target_id,$a_copy_id);
	 	
	 	$this->read();
	 	
		// Copy settings
		$this->cloneSettings($new_obj);
		
		// Clone appointment
		$this->getFirstAppointment()->cloneObject($new_obj->getId());

		// Clone session files
		foreach($this->files as $file)
		{
			$file->cloneFiles($new_obj->getEventId());
		}
	
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
	 * @param
	 * @return
	 */
	public function cloneSettings($new_obj)
	{
		$new_obj->setLocation($this->getLocation());
		$new_obj->setName($this->getName());
		$new_obj->setPhone($this->getPhone());
		$new_obj->setEmail($this->getEmail());
		$new_obj->setDetails($this->getDetails());
		$new_obj->enableRegistration($this->enabledRegistration());
		$new_obj->update();
		
		return true;
	}
	
	
	
	/**
	 * create new session
	 *
	 * @access public
	 */
	public function create()
	{
		global $ilAppEventHandler;
	
		parent::create();
		
		$query = "INSERT INTO event SET ".
			"obj_id = ".$this->db->quote($this->getId()).", ".
			"location = ".$this->db->quote($this->getLocation()).",".
			"tutor_name = ".$this->db->quote($this->getName()).", ".
			"tutor_phone = ".$this->db->quote($this->getPhone()).", ".
			"tutor_email = ".$this->db->quote($this->getEmail()).", ".
			"details = ".$this->db->quote($this->getDetails()).",".
			"registration = ".$this->db->quote($this->enabledRegistration())." ";

		$this->db->query($query);
		$this->event_id = $this->db->getLastInsertId();
		
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
	public function update()
	{
		global $ilAppEventHandler;

		if(!parent::update())
		{
			return false;
		}
		$query = "UPDATE event SET ".
			"location = ".$this->db->quote($this->getLocation()).",".
			"tutor_name = ".$this->db->quote($this->getName()).", ".
			"tutor_phone = ".$this->db->quote($this->getPhone()).", ".
			"tutor_email = ".$this->db->quote($this->getEmail()).", ".
			"details = ".$this->db->quote($this->getDetails()).", ".
			"registration = ".$this->db->quote($this->enabledRegistration())." ".
			"WHERE obj_id = ".$this->db->quote($this->getId())." ";

		$this->db->query($query);
		
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
		global $ilAppEventHandler;
		
		if(!parent::delete())
		{
			return false;
		}
		$query = "DELETE FROM event ".
			"WHERE obj_id = ".$this->db->quote($this->getId())." ";
		$this->db->query($query);
		
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
			"obj_id = ".$this->db->quote($this->getId())." ";
		$res = $this->db->query($query);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setLocation($row->location);
			$this->setName($row->tutor_name);
			$this->setPhone($row->tutor_phone);
			$this->setEmail($row->tutor_email);
			$this->setDetails($row->details);
			$this->enableRegistration((bool) $row->registration);
			$this->event_id = $row->event_id;
		}

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
	protected function initFiles()
	{
		include_once('./Modules/Session/classes/class.ilSessionFile.php');
		$this->files = ilSessionFile::_readFilesByEvent($this->getEventId());
	}
	
	
	/**
	 * Prepare calendar appointments
	 *
	 * @access protected
	 * @param string mode UPDATE|CREATE|DELETE
	 * @return
	 */
	protected function prepareCalendarAppointments($a_mode = 'create')
	{
		include_once('./Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php');
		
		switch($a_mode)
		{
			case 'create':
			case 'update':

				$app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
				$app->setTranslationType(IL_CAL_TRANSLATION_NONE);
				$app->setTitle($this->getTitle());
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
	
}

?>