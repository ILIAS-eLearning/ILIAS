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

/** 
* Representation of ECS EContent 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesWebServicesECS 
*/
class ilECSEContent
{
	private $obj_id;
	
	// ECS JSON variables
	// All exportable fields have to be public
	public $url = '';
	public $title = '';
	public $eligibleMembers = array();
	public $etype = 'application/ecs-course';
	public $status = 'offline';
	public $lang = 'en_EN';
	public $abstract = '';
	public $study_courses = array();
	public $owner = 0;
	public $credits = '';
	public $semesterHours = '';
	public $lecturer = array();
	public $courseType = '';
	public $courseID = '';
	public $eid = 0;
	public $term = '';
	public $timePlace = null;


	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function __construct($a_eid = 0)
	{
	 	include_once('./Services/WebServices/ECS/classes/class.ilECSTimePlace.php');

	 	if($a_eid)
	 	{
	 		$this->eid = $a_eid;
	 	}
	 	$this->timePlace = new ilECSTimePlace();
	}
	
	/**
	 * get econtent id
	 *
	 * @access public
	 * 
	 */
	public function getEContentId()
	{
	 	return $this->eid;
	}
	
	/**
	 * set ILIAS obj_id
	 *
	 * @access public
	 * @param int obj_id
	 * 
	 */
	public function setObjId($a_id)
	{
	 	$this->obj_id = $a_id;
	}
	
	/**
	 * get obj_id
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getObjId()
	{
	 	return $this->obj_id;
	}
	
	/**
	 * get organization
	 *
	 * @access public
	 * 
	 */
	public function getOrganization()
	{
	 	return $this->organization;
	}
	
	/**
	 * get time place
	 *
	 * @access public
	 * @param ilECSTimePlace
	 * 
	 */
	public function getTimePlace()
	{
	 	return $this->timePlace;
	}
	
	/**
	 * set title
	 *
	 * @access public
	 * @param string title
	 * 
	 */
	public function setTitle($a_title)
	{
	 	$this->title = $a_title;
	}
	
	/**
	 * get title
	 *
	 * @access public
	 * 
	 */
	public function getTitle()
	{
	 	return $this->title;
	}
	
	/**
	 * set info
	 *
	 * @access public
	 * @param string info (ILIAS description)
	 * 
	 */
	public function setInfo($a_info)
	{
	 	$this->abstract = $a_info;
	}
	
	/**
	 * getInfo
	 *
	 * @access public
	 * 
	 */
	public function getInfo()
	{
	 	return $this->abstract;
	}
	
	/**
	 * set url
	 *
	 * @access public
	 * @param string url to resource
	 * 
	 */
	public function setURL($a_url)
	{
	 	$this->url = $a_url;
	}
	
	/**
	 * get URL
	 *
	 * @access public
	 * 
	 */
	public function getURL()
	{
	 	return $this->url;
	}
	
	/**
	 * set language 
	 *
	 * @access public
	 * @param string ilias language key (e.g 'en')
	 * 
	 */
	public function setLanguage($a_key)
	{
	 	if(strlen($a_key))
	 	{
	 		$this->lang = ($a_key.'_'.strtoupper($a_key));
	 	}
	}
	
	/**
	 * get language
	 *
	 * @access public
	 * 
	 */
	public function getLanguage()
	{
	 	if(strlen($this->lang))
	 	{
	 		return substr($this->lang,0,2);
	 	}	
	}
	
	/**
	 * set eligible members
	 *
	 * @access public
	 * @param array array of mids 
	 */
	public function setEligibleMembers($a_members)
	{
	 	$this->eligibleMembers = array();
	 	foreach($a_members as $member)
	 	{
	 		$this->eligibleMembers[] = (int) $member;
	 	}
	}
	
	/**
	 * get eligible members
	 *
	 * @access public
	 * 
	 */
	public function getEligibleMembers()
	{
	 	return $this->eligibleMembers ? $this->eligibleMembers : array();
	}
	
	/**
	 * get owner
	 *
	 * @access public
	 */
	public function getOwner()
	{
	 	return $this->owner ? $this->owner : 0;
	}
	
	/**
	 * set owner
	 *
	 * @access public
	 * @param int mid (publish as)
	 * 
	 */
	public function setOwner($a_owner)
	{
	 	$this->owner = (int) $a_owner;
	}
	
	/**
	 * get participants
	 *
	 * @access public
	 * 
	 */
	public function getParticipants()
	{
		return $this->getEligibleMembers();
	}
	
	/**
	 * set status
	 *
	 * @access public
	 * @param string status 'online' or 'offline'
	 * 
	 */
	public function setStatus($a_status)
	{
	 	switch($a_status)
	 	{
	 		case 'online':
	 			$this->status = 'online';
	 			break;
	 			
	 		default:
	 			$this->status = 'offline';
	 			break;
	 	}
	}
	
	/**
	 * get Status
	 *
	 * @access public
	 * @return string 'online' or 'offline'
	 * 
	 */
	public function getStatus()
	{
		return $this->status == 'online' ? $this->status : 'offline'; 	
	}
	
	/**
	 * is online
	 *
	 * @access public
	 * 
	 */
	public function isOnline()
	{
	 	return $this->status == 'online' ? true : false;
	}
	
	/**
	 * set credits
	 *
	 * @access public
	 * @param string credits
	 * 
	 */
	public function setCredits($a_credits)
	{
	 	$this->credits = $a_credits;
	}
	
	/**
	 * get credits
	 *
	 * @access public
	 * 
	 */
	public function getCredits()
	{
	 	return $this->credits;
	}
	
	/**
	 * set semester hours
	 *
	 * @access public
	 * @param string semester hours 
	 * 
	 */
	public function setSemesterHours($a_semester_hours)
	{
	 	$this->semesterHours = $a_semester_hours;
	}
	
	/**
	 * get semester hours
	 *
	 * @access public
	 * 
	 */
	public function getSemesterHours()
	{
	 	return $this->semesterHours;
	}

	/**
	 * set Lecturer 
	 * In ILIAS lecturers are stored in AdvancedMetaData.
	 * Multiple lecturers should be comma seperated.
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function setLecturers($a_lecturer)
	{
	 	$lecturer_arr = explode(',',$a_lecturer);
	 	$this->lecturer = array();
	 	foreach($lecturer_arr as $lecturer)
	 	{
	 		$this->lecturer[] = trim($lecturer);
	 	}
	 	return true;
	}
	
	/**
	 * get Lecturer
	 *
	 * @access public
	 * @return string string presentation of lecturers (comma seperated) 
	 */
	public function getLecturers()
	{
	 	return implode(', ',$this->lecturer);
	}
	
	/**
	 * setStudyCourses
	 * In ILIAS study_courses are stored in AdvancedMetaData.
	 * Multiple courses should be comma seperated.
	 *
	 * @access public
	 * @param string comma seperated study courses
	 * 
	 */
	public function setStudyCourses($a_courses)
	{
	 	$courses_arr = explode(',',$a_courses);
	 	$this->study_courses = array();
	 	foreach($courses_arr as $course)
	 	{
	 		$this->study_courses[] = trim($course);
	 	}
	 	return true;
	}
	
	/**
	 * get study courses
	 *
	 * @access public
	 * 
	 */
	public function getStudyCourses()
	{
	 	return implode(', ',$this->study_courses);
	}

	/**
	 * set course type
	 *
	 * @access public
	 * @param string course type
	 * 
	 */
	public function setCourseType($a_type)
	{
	 	$this->courseType = $a_type;
	}
	
	/**
	 * get courseType
	 *
	 * @access public
	 * 
	 */
	public function getCourseType()
	{
		return $this->courseType;	 	
	}
	
	/**
	 * set course ID
	 *
	 * @access public
	 * @param string course id
	 * 
	 */
	public function setCourseID($a_id)
	{
	 	$this->courseID = $a_id;
	}
	
	/**
	 * get course id
	 *
	 * @access public
	 * 
	 */
	public function getCourseID()
	{
	 	return $this->courseID;
	}
	
	/**
	 * set term
	 *
	 * @access public
	 * @param string term
	 * 
	 */
	public function setTerm($a_term)
	{
	 	$this->term = $a_term;
	}
	
	/**
	 * get Term
	 *
	 * @access public
	 * 
	 */
	public function getTerm()
	{
	 	return $this->term;
	}

	/**
	 * Load from JSON object
	 *
	 * @access public
	 * @param object JSON object
	 * @throws ilECSReaderException
	 */
	public function loadFromJSON($a_json)
	{
		global $ilLog;
		
		if(!is_object($a_json))
		{
		 	include_once('./Services/WebServices/ECS/classes/class.ilECSReaderException.php');
			$ilLog->write(__METHOD__.': Cannot load from JSON. No object given.');
			throw new ilECSReaderException('Cannot parse ECSContent.');
		}
		$this->organization = $a_json->organization;
		$this->study_courses = $a_json->study_courses ? $a_json->study_courses : array();
		$this->owner = $a_json->owner;
		$this->title = $a_json->title;
		$this->abstract = $a_json->abstract;
		$this->credits = $a_json->credits;
		$this->semesterHours = $a_json->semesterHours;
		$this->lecturer = $a_json->lecturer ? $a_json->lecturer : array();
		$this->etype = $a_json->etype;
		$this->status = $a_json->status;
		
		$this->courseID = $a_json->courseID;
		$this->courseType = $a_json->courseType;
		$this->eid = $a_json->eid;
		$this->term = $a_json->term;
		$this->url = $a_json->url;
		$this->lang = $a_json->lang;
		
		if(is_object($a_json->timePlace))
		{
			$this->timePlace = new ilECSTimePlace();
			$this->timePlace->loadFromJSON($a_json->timePlace);
		}
		else
		{
			$this->timePlace = new ilECSTimePlace();
		}
		$this->eligibleMembers = $a_json->eligibleMembers ? $a_json->eligibleMembers : array(); 
	}
}

?>