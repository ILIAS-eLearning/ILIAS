<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Accomodations/classes/class.ilAccomodationsHelper.php";

/**
 * Accomodations application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesAccomodations
 */
class ilAccomodations
{
	protected $course; // [ilObjCourse]
	
	protected static $instances = array(); // [array]
	protected static $waiting_instances = array(); // [array]
	
	/**
	 * Constructor
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */	
	protected function __construct(ilObjCourse $a_course)
	{
		$this->setCourse($a_course);				
	}
	
	/**
	 * Factory
	 * 
	 * @param ilObjCourse $a_course
	 * @return self
	 */
	public static function getInstance(ilObjCourse $a_course)
	{
		$crs_ref_id = $a_course->getRefId();
		
		if(!array_key_exists($crs_ref_id, self::$instances))
		{
			self::$instances[$crs_ref_id] = new self($a_course);
		}
		
		return self::$instances[$crs_ref_id];
	}
	
	/**
	 * Factory
	 * 
	 * @param int $a_course_ref_id
	 * @return self
	 */
	public static function getInstanceByRefId($a_course_ref_id)
	{		
		global $tree;
		
		if(array_key_exists($a_course_ref_id, self::$instances))
		{
			return self::$instances[$a_course_ref_id];
		}		
		
		if(ilObject::_lookupType($a_course_ref_id, true) != "crs" ||
			$tree->isDeleted($a_course_ref_id))
		{
			throw new ilException("ilAccomodations - needs course ref id");
		}
		
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_course_ref_id);
		
		return self::getInstance($course);		
	}
	
	/**
	 * Init waiting list instance for course
	 * 
	 * @return ilCourseWaitingList
	 */
	protected function getWaitingListInstance()
	{
		$crs_id = $this->getCourse()->getId();
		
		if(!array_key_exists($crs_id, self::$waiting_instances))
		{
			require_once "Modules/Course/classes/class.ilCourseWaitingList.php";
			self::$waiting_instances[$crs_id] = new ilCourseWaitingList($crs_id);
		}
		
		return self::$waiting_instances[$crs_id];
	}
	
	
	//
	// properties
	//
	
	/**
	 * Set course
	 * 
	 * @param ilObjCourse $a_course
	 */
	protected function setCourse(ilObjCourse $a_course)
	{
		$this->course = $a_course;
	}
	
	/**
	 * Get course
	 * 
	 * @return ilObjCourse 
	 */	
	protected function getCourse()
	{
		return $this->course;
	}
	
	
	//
	// course info
	// 
	
	/**
	 * Get course start
	 * 
	 * @return ilDate
	 */
	public function getCourseStart()
	{
		return ilAccomodationsHelper::getInstance($this->getCourse())->getCourseStart();		
	}
	
	/**
	 * Get course end
	 * 
	 * @return ilDate
	 */
	public function getCourseEnd()
	{
		return ilAccomodationsHelper::getInstance($this->getCourse())->getCourseEnd();				
	}
	
	/**
	 * Get possible nights from course period
	 * 
	 * @return array
	 */
	public function getPossibleAccomodationNights()
	{
		$res = array();
	
		// each result day represents the night AFTER that day
		
		$end = $this->getCourseEnd();
		$start = $this->getCourseStart();
		
		if($end === null || $start === null) {
			return array();
		}
				
		$current = clone $start;			
		$current->increment(IL_CAL_DAY, -1); // night before course start
		
		$counter = 0;
		while((ilDate::_before($current, $end) || ilDate::_equals($current, $end)) && 
			$counter < 100)
		{			
			$res[] = clone $current;
			
			$current->increment(IL_CAL_DAY, 1);
			$counter++;			
		}
		
		return $res;				
	}
	
	/**
	 * Get valid user ids for course
	 * 
	 * @return array
	 */
	public function getValidUserIds()
	{
		$members = $this->getCourse()->getMembersObject();
		$res = array_merge($members->getTutors(), $members->getMembers());
	
		$waiting = $this->getWaitingListInstance();
		return array_merge($res, $waiting->getUserIds());
	}
	
	/**
	 * Is user member, tutor of course or at least on waiting list?
	 * 
	 * @param int $a_user_id
	 * @return bool
	 */
	public function validateUser($a_user_id)
	{
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		$status = gevCourseUtils::getInstanceByObj($this->getCourse())
								->getBookingStatusOf($a_user_id);
		if (in_array($status, array( ilCourseBooking::STATUS_BOOKED
								   , ilCourseBooking::STATUS_WAITING
								   )
			))	 {
			return true;
		}
		
		$members = $this->getCourse()->getMembersObject();
		if($members->isMember($a_user_id) ||
			$members->isTutor($a_user_id))
		{
			return true;
		}
		
		$waiting = $this->getWaitingListInstance();
		if($waiting->isOnList($a_user_id))
		{
			return true;
		}
		
		return false;
	}
		
	/**
	 * Are all given dates valid for course?
	 * 
	 * @param array $a_nights
	 * @return bool
	 */
	public function validateAccomodations(array $a_nights)
	{
		$valid = array();
		foreach($this->getPossibleAccomodationNights() as $night)
		{
			$valid[] = $night->get(IL_CAL_DATE);
		}
		
		foreach($a_nights as $night)
		{
			$night = $night->get(IL_CAL_DATE);
			if(!in_array($night, $valid))
			{
				return false;
			}
		}
		
		return true;
	}
	
	
	//
	// CRUD
	// 
	
	/**
	 * Get accomodations for given user
	 * 
	 * @param int $a_user_id
	 * @return array
	 */
	public function getAccomodationsOfUser($a_user_id)
	{
		$res = $this->getAccomodationsOfUsers(array($a_user_id));
		return (array)$res[$a_user_id];		
	}
	
	/**
	 * Get accomodations for given users
	 * 
	 * @param array $a_user_ids
	 * @return array
	 */
	public function getAccomodationsOfUsers(array $a_user_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT user_id, night FROM crs_acco".
			" WHERE crs_id = ".$ilDB->quote($this->getCourse()->getId(), "integer").
			" AND ".$ilDB->in("user_id", $a_user_ids, "", "integer");
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["user_id"]][] = new ilDate($row["night"], IL_CAL_DATE);
		}
		
		return $res;
	}
	
	/**
	 * Set accomodations for given user
	 * 
	 * @param int $a_user_id
	 * @param array $a_accomodations
	 * @return bool
	 */
	public function setAccomodationsOfUser($a_user_id, array $a_accomodations)
	{
		global $ilDB;
		//gev patch start #2351
		global $log, $ilUser;
		$by_usr = "";
		if($ilUser) {
			$by_usr = " by ".$ilUser->getId();
		}
		//gev patch end
		if(!$this->validateUser($a_user_id) ||
			!$this->validateAccomodations($a_accomodations))
		{
			return false;
		}
				
		$course_id = $this->getCourse()->getId();
		
		$changed = false;
		
		$old = array();
		$tmp = $this->getAccomodationsOfUser($a_user_id);
		if(sizeof($tmp))
		{
			//gev patch start #2351
			$msg = "####course accomodations at ".$course_id." for ".$a_user_id." old:";
			//gev patch end
			foreach($tmp as $night)
			{
				$night = $night->get(IL_CAL_DATE);
				$old[$night] = $night;
				//gev patch start #2351
				$msg.= $night.",";
				//gev patch end
			}
			//gev patch start #2351
			$log->write($msg.$by_usr);
			$msg = null;
			//gev patch end
		}
		//gev patch start #2351
		$msg = "####course accomodations at ".$course_id." for ".$a_user_id." new:";
		//gev patch end
		foreach($a_accomodations as $night)
		{

			$night = $night->get(IL_CAL_DATE);
			//gev patch start #2351
			$msg.= $night.",";
			//gev patch end
			if(!in_array($night, $old))
			{
				$fields = array(
					"crs_id" => array("integer", $course_id)
					,"user_id" => array("integer", $a_user_id)
					,"night" => array("date", $night)
				);				
				$ilDB->insert("crs_acco", $fields);				
				
				$changed = true;
			}
			else
			{
				unset($old[$night]);
			}			
		}
		if($msg) {
			$log->write($msg.$by_usr);
		}
		if(sizeof($old))
		{
			// remove obsolete entries
			$ilDB->manipulate("DELETE FROM crs_acco".
				" WHERE crs_id = ".$ilDB->quote($course_id, "integer").
				" AND user_id = ".$ilDB->quote($a_user_id, "integer").
				" AND ".$ilDB->in("night", array_values($old), "", "date"));
			
			$changed = true;
		}
		
		if($changed)
		{
			self::raiseEvent("set", $course_id, $a_user_id);
		}
		
		return true;
	}
	
	/**
	 * Delete accomodations for given user
	 * 
	 * @param int $a_user_id
	 */
	public function deleteAccomodations($a_user_id)
	{
		global $ilDB;
		
		$course_id = $this->getCourse()->getId();
		
		if(!$course_id || !(int)$a_user_id)
		{
			return;
		}
		
		$ilDB->manipulate("DELETE FROM crs_acco".
			" WHERE crs_id = ".$ilDB->quote($course_id, "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));
		// gev patch start
		self::raiseEvent("delete", $course_id, $a_user_id);
		// gev patch end

		//gev patch start #2351
		global $log,$ilUser;
		$msg = "####course accomodations at ".$course_id.": deleted for ".$a_user_id;
		if($ilUser) {
			$msg .= " by ".$ilUser->getId();
		}
		$log->write($msg);
		//gev patch end
	}
	
	
	//
	// events
	// 
	
	/**
	 * Raise event
	 * 	 
	 * @param string $a_event
	 * @param int $a_course_obj_id
	 * @param int $a_user_id
	 */
	protected static function raiseEvent($a_event, $a_course_obj_id = null, $a_user_id = null)
	{
		global $ilAppEventHandler;
		
		$params = null;
		if($a_course_obj_id || $a_user_id)
		{
			$params = array();
			if($a_course_obj_id)
			{
				$params["crs_obj_id"] = $a_course_obj_id;
			}
			if($a_user_id)
			{
				$params["user_id"] = $a_user_id;
			}
		}
		
		$ilAppEventHandler->raise("Services/Accomodations", $a_event, $params);
	}
		
	
	//
	// destructor
	// 
	
	/**
	 * Delete all entries for user
	 * 
	 * @param int $a_user_id
	 */
	public static function deleteByUserId($a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM crs_acco".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer"));
	}
	
	/**
	 * Delete all entries for course
	 * 
	 * @param int $a_course_obj_id
	 */
	public static function deleteByCourse($a_course_obj_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM crs_acco".
			" WHERE crs_id = ".$ilDB->quote($a_course_obj_id, "integer"));		
	}
	
	
	//
	// presentation
	//
	
	/**
	 * Get user name presentation
	 *
	 * @param array $a_user_ids
	 * @param bool $a_only_lastnames
	 * @return array
	 */
	public static function getUserNames(array $a_user_ids, $a_only_lastnames = false)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT usr_id,lastname,firstname".
			" FROM usr_data".
			" WHERE ".$ilDB->in("usr_id", array_unique($a_user_ids), "", "integer").
			" ORDER BY lastname, firstname, usr_id";
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			if(!$a_only_lastnames)
			{
				$res[$row["usr_id"]] = $row["lastname"].", ".$row["firstname"];
			}
			else
			{
				$res[$row["usr_id"]] = $row["lastname"];
			}
		}
		
		return $res;
	}
}
