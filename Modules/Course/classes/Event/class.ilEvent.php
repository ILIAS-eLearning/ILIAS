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


/**
* class ilEvent
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
*/

include_once 'Modules/Course/classes/Event/class.ilEventAppointment.php';
include_once 'Modules/Course/classes/Event/class.ilEventFile.php';

class ilEvent
{
	var $ilErr;
	var $ilDB;
	var $tree;
	var $lng;

	var $event_id = null;
	var $appointments = array();


	function ilEvent($a_event_id = 0)
	{
		global $ilErr,$ilDB,$lng,$tree;

		$this->ilErr =& $ilErr;
		$this->db  =& $ilDB;
		$this->lng =& $lng;

		$this->event_id = $a_event_id;
		$this->__read();
	}

	function setObjId($a_obj_id)
	{
		$this->obj_id = $a_obj_id;
	}
	function getObjId()
	{
		return $this->obj_id;
	}

	function getEventId()
	{
		return $this->event_id;
	}
	function setEventId($a_event_id)
	{
		$this->event_id = $a_event_id;
	}
	function getTitle()
	{
		return $this->title;
	}
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}
	function getDescription()
	{
		return $this->description;
	}
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	function getLocation()
	{
		return $this->location;
	}
	function setLocation($a_location)
	{
		$this->location = $a_location;
	}
	function setName($a_name)
	{
		$this->name = $a_name;
	}
	function getName()
	{
		return $this->name;
	}

	function getFirstname()
	{
		return $this->firstname;
	}
	function setFirstname($a_firstname)
	{
		$this->firstname = $a_firstname;
	}
	function getLastname()
	{
		return $this->lastname;
	}
	function setLastname($a_lastname)
	{
		$this->lastname = $a_lastname;
	}
	function getPTitle()
	{
		return $this->ptitle;
	}
	function setPTitle($a_ptitle)
	{
		$this->ptitle = $a_ptitle;
	}
	function getEmail()
	{
		return $this->mail;
	}
	function setEmail($a_mail)
	{
		$this->mail = $a_mail;
	}
	function getPhone()
	{
		return $this->phone;
	}
	function setPhone($a_phone)
	{
		$this->phone = $a_phone;
	}
	function setDetails($a_details)
	{
		$this->details = $a_details;
	}
	function getDetails()
	{
		return $this->details;
	}
	function enabledRegistration()
	{
		return (bool) $this->registration;
	}
	function enableRegistration($a_status)
	{
		$this->registration = $a_status;
	}
	function enabledParticipation()
	{
		return true;
	}
	function enableParticipation($a_status)
	{
		$this->participation = $a_status;
	}
	

	function &getAppointments()
	{
		return $this->appointments ? $this->appointments : array();
	}
	function addAppointment(&$appointment)
	{
		$this->appointments[] =& $appointment;
	}
	function setAppointments(&$appointments)
	{
		$this->appointments =& $appointments;
	}
	function &getFirstAppointment()
	{
		return is_object($this->appointments[0]) ? $this->appointments[0] : ($this->appointments[0] =& new ilEventAppointment());
	}

	function validate()
	{
		if(!strlen($this->getTitle()))
		{
			$this->ilErr->appendMessage($this->lng->txt('fill_out_all_required_fields'));
			return false;
		}
		return true;
	}

	function getFiles()
	{
		return $this->files ? $this->files : array();
	}
	
	/**
	 * Clone events
	 *
	 * @access public
	 * @static
	 *
	 * @param int source id
	 * @param int target id
	 * @param int copy id
	 */
	public static function _cloneEvent($a_source_id,$a_target_id,$a_copy_id)
	{
		include_once('Modules/Course/classes/Event/class.ilEventItems.php');
		
		include_once('Services/Tracking/classes/class.ilLPEventCollections.php');
		$old_event_collection = new ilLPEventCollections($a_source_id);
		$new_event_collection = new ilLPEventCollections($a_target_id);
		
		foreach(ilEvent::_getEvents($a_source_id) as $event_obj)
		{
			$new_event = new ilEvent();
			$new_event->setObjId($a_target_id);
			$new_event->setTitle($event_obj->getTitle());
			$new_event->setDescription($event_obj->getDescription());
			$new_event->setLocation($event_obj->getLocation());
			$new_event->setName($event_obj->getName());
			$new_event->setPhone($event_obj->getPhone());
			$new_event->setEmail($event_obj->getEmail());
			$new_event->setDetails($event_obj->getDetails());
			$new_event->enableRegistration($event_obj->enabledRegistration());
			$new_event->enableParticipation($event_obj->enabledParticipation());
			$new_event->create();
			
			// Copy appointments
			foreach($event_obj->getAppointments() as $app_obj)
			{
				$new_app = new ilEventAppointment();
				$new_app->setEventId($new_event->getEventId());
				$new_app->setStartingTime($app_obj->getStartingTime());
				$new_app->setEndingTime($app_obj->getEndingTime());
				$new_app->toggleFullTime($app_obj->enabledFullTime());
				$new_app->create();
			}
			// Copy files
			foreach($event_obj->getFiles() as $file_obj)
			{
				$file_obj->cloneFiles($new_event->getEventId());
			}
			
			// Copy lp collections
			if($old_event_collection->isAssigned($event_obj->getEventId()))
			{
				$new_event_collection->add($new_event->getEventId());
			}
			
			// Copy assigned materials
			$new_event_items = new ilEventItems($new_event->getEventId());
			$new_event_items->cloneItems($event_obj->getEventId(),$a_copy_id);
		}
	}

	function create()
	{
		global $ilDB;
		
		$query = "INSERT INTO event SET ".
			"obj_id = ".$ilDB->quote($this->getObjId()).", ".
			"title = ".$ilDB->quote($this->getTitle()).", ".
			"description = ".$ilDB->quote($this->getDescription()).", ".
			"location = ".$ilDB->quote($this->getLocation()).",".
			#"tutor_firstname = '".ilUtil::prepareDBString($this->getFirstname())."', ".
			#"tutor_lastname = '".ilUtil::prepareDBString($this->getLastname())."', ".
			"tutor_name = ".$ilDB->quote($this->getName()).", ".
			#"tutor_title = '".ilUtil::prepareDBString($this->getPTitle())."', ".
			"tutor_phone = ".$ilDB->quote($this->getPhone()).", ".
			"tutor_email = ".$ilDB->quote($this->getEmail()).", ".
			"details = ".$ilDB->quote($this->getDetails()).",".
			"registration = ".$ilDB->quote($this->enabledRegistration()).", ".
			"participation = ".$ilDB->quote($this->enabledParticipation())."";

		$this->db->query($query);
		$this->setEventId($this->db->getLastInsertId());

		return $this->getEventId();
	}

	function update()
	{
		global $ilDB;
		
		if(!$this->event_id)
		{
			return false;
		}

		$query = "UPDATE event SET ".
			"title = ".$ilDB->quote($this->getTitle()).", ".
			"description = ".$ilDB->quote($this->getDescription()).", ".
			"location = ".$ilDB->quote($this->getLocation()).",".
			#"tutor_firstname = '".ilUtil::prepareDBString($this->getFirstname())."', ".
			"tutor_name = ".$ilDB->quote($this->getName()).", ".
			#"tutor_title = '".ilUtil::prepareDBString($this->getPTitle())."', ".
			"tutor_phone = ".$ilDB->quote($this->getPhone()).", ".
			"tutor_email = ".$ilDB->quote($this->getEmail()).", ".
			"details = ".$ilDB->quote($this->getDetails()).", ".
			"registration = ".$ilDB->quote($this->enabledRegistration()).", ".
			"participation = ".$ilDB->quote($this->enabledParticipation())." ".
			"WHERE event_id = ".$ilDB->quote($this->getEventId())." ";

		$this->db->query($query);
		return true;
	}

	function delete()
	{
		ilEvent::_delete($this->getEventId());
		return true;
	}

	function readFiles()
	{
		$this->files = ilEventFile::_readFilesByEvent($this->getEventId());
	}

	function hasTutorSettings()
	{
		return strlen($this->getFullname()) or 
			strlen($this->getEmail()) or
			strlen($this->getPhone());
	}

	function getFullname()
	{
		return $this->getName();
	}

	function _delete($a_event_id)
	{
		global $ilDB;

		$query = "DELETE FROM event WHERE event_id = ".$ilDB->quote($a_event_id)." ";
		$ilDB->query($query);

		ilEventAppointment::_deleteByEvent($a_event_id);
		ilEventFile::_deleteByEvent($a_event_id);
		
		include_once 'Modules/Course/classes/Event/class.ilEventItems.php';
		ilEventItems::_delete($a_event_id);

		include_once 'Modules/Course/classes/Event/class.ilEventParticipants.php';
		ilEventParticipants::_deleteByEvent($a_event_id);

		return true;
	}
	
	function _deleteAll($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			ilEvent::_delete($row->event_id);
		}
		return true;
	}

	function _exists($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event WHERE event_id = ".$ilDB->quote($a_event_id)." ";
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}

	function _lookupCourseId($a_event_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event WHERE event_id = ".$ilDB->quote($a_event_id)."";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			return $row->obj_id;
		}
		return false;
	}

	function &_getEvents($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT * FROM event ".
			"JOIN event_appointment ON event.event_id = event_appointment.event_id ".
			"WHERE event.obj_id = ".$ilDB->quote($a_obj_id)." ".
			"ORDER BY starting_time";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$events[] =& new ilEvent($row->event_id);
		}
		return $events ? $events : array();
	}

	function &_getEventsAsArray($a_obj_id)
	{
		foreach(ilEvent::_getEvents($a_obj_id) as $event_obj)
		{
			$item[$event_obj->getEventId()]['title'] = $event_obj->getTitle();
			$item[$event_obj->getEventId()]['description'] = $event_obj->getDescription();
			$item[$event_obj->getEventId()]['type'] = 'event';
			$item[$event_obj->getEventId()]['event_id'] = $event_obj->getEventId();

			$event_appointment =& $event_obj->getFirstAppointment();
			$item[$event_obj->getEventId()]['start'] = $event_appointment->getStartingTime();
			$item[$event_obj->getEventId()]['end'] = $event_appointment->getEndingTime();
			$item[$event_obj->getEventId()]['fulltime'] = $event_appointment->enabledFullTime();
		}

		return $item ? $item : array();
	}

	// PRIVATE
	function __read()
	{
		global $ilDB;
		
		if(!$this->event_id)
		{
			return true;
		}

		// read event data
		$query = "SELECT * FROM event WHERE event_id = ".$ilDB->quote($this->event_id)." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))
		{
			$this->setObjId($row->obj_id);
			$this->setTitle($row->title);
			$this->setDescription($row->description);
			$this->setLocation($row->location);
			
			#$this->setPTitle($row->tutor_title);
			#$this->setFirstname($row->tutor_firstname);
			$this->setName($row->tutor_name);
			$this->setPhone($row->tutor_phone);
			$this->setEmail($row->tutor_email);
			$this->setDetails($row->details);
			$this->enableRegistration($row->registration);
			$this->enableParticipation($row->participation);
		}

		// get assigned appointments
		$this->appointments =& ilEventAppointment::_readAppointmentsByEvent($this->event_id);

		// get assigned files
		$this->readFiles();

		return true;
	}
		
}
?>