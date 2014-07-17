<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/TEP/classes/class.ilTEPEntry.php";

/**
 * TEP course entries application class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
class ilTEPCourseEntries 
{	
	protected $course; // [ilObjCourse]
	
	static protected $instances = array();
	
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
		$crs_id = $a_course->getId();
		
		if(!array_key_exists($crs_id, self::$instances))
		{
			self::$instances[$crs_id] = new self($a_course);
		}
		
		return self::$instances[$crs_id];
	}
	
	/**
	 * Init operation days instance 
	 *
	 * @throws ilException
	 * @return ilTEPOperationDays
	 */
	public function getOperationsDaysInstance()
	{
		require_once "./Services/TEP/classes/class.ilTEPOperationDays.php";
				
		$entry_id = $this->getCourseEntryId();
		if(!$entry_id)
		{
			throw new ilException("ilTEPCourseEntries - course needs TEP entry for operation days");
		}
				
		return new ilTEPOperationDays(
			ilTEPEntry::OPERATION_DAY_ID
			,$entry_id
			,$this->getCourseStart()
			,$this->getCourseEnd());
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
	
	/**
	 * Get course entry id
	 * 
	 * @return int
	 */
	public function getCourseEntryId()
	{
		return ilTEPEntry::getEntryByContextId($this->getCourse()->getId());
	}
	
	
	//
	// course meta
	//
	
	/**
	 * Get course start date
	 * 
	 * @return ilDate
	 */
	public function getCourseStart()
	{
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		return gevCourseUtils::getInstance($this->getCourse()->getId())->getStartDate();
	}
	
	/**
	 * Get course end date
	 * 
	 * @return ilDate
	 */
	public function getCourseEnd()
	{
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		return gevCourseUtils::getInstance($this->getCourse()->getId())->getStartDate();
	}
	
	/**
	 * Get course venue
	 * 
	 * @return string
	 */
	public function getCourseVenue()
	{
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		return gevCourseUtils::getInstance($this->getCourse()->getId())->getVenueTitle();
	}
	
	
	// 
	// CRUD
	//

	const SYNC_NO_CHANGE = 0;
	const SYNC_UPDATED = 0;
	const SYNC_DELETED = 0;

	/**
	 * Sync course settings/metadata with calendar entry
	 *
	 * @param ilTEPEntry $a_entry
	 * @return boolean
	 */
	protected function syncEntry(ilTEPEntry $a_entry)
	{
		$course = $this->getCourse();
		
		$start = $this->getCourseStart();
		$end = $this->getCourseEnd();

		
		if ($start !== null && $end !== null) {
			// course settings
			$changed = self::SYNC_NO_CHANGE;
			if($course->getTitle() != $a_entry->getTitle())
			{
				$a_entry->setTitle($course->getTitle());
				$changed = self::SYNC_UPDATED;
			}
			if($course->getDescription() != $a_entry->getDescription())
			{
				$a_entry->setDescription($course->getDescription());
				$changed = self::SYNC_UPDATED;
			}
			// course period
			if($start->get(IL_CAL_DATE) != $a_entry->getStart()->get(IL_CAL_DATE))
			{
				$a_entry->setStart($start);
				$changed = self::SYNC_UPDATED;
			}
			if($end->get(IL_CAL_DATE) != $a_entry->getEnd()->get(IL_CAL_DATE))
			{
				$a_entry->setEnd($end);
				$changed = self::SYNC_UPDATED;
			}		 
			
			// course venue
			if($this->getCourseVenue() != $a_entry->getLocation())
			{
				$a_entry->setLocation($this->getCourseVenue());
				$changed = self::SYNC_UPDATED;
			}
		}
		else {
			$a_entry->delete();
			$changed = self::SYNC_DELETED;
		}
		
		return $changed;
	}
		
	/**
	 * Create derived entry for course member
	 * 
	 * @param int $a_user_id
	 * @return int
	 */
	protected function deriveEntryForUser($a_user_id)
	{
		$master_id = $this->getCourseEntryId();
		$cat_id = ilTEP::getPersonalCalendarId($a_user_id);
		
		if(!$master_id ||
			!$cat_id)
		{
			return;
		}

		$entry = new ilCalDerivedEntry();
		$entry->setMasterEntryId($master_id);
		$entry->setCategoryId($cat_id);
		$entry->create();
		
		return $entry->getId();
	}

	/**
	 * Create entry
	 * 
	 * @param string $a_entry_type
	 * @return bool
	 */
	public function createEntry($a_entry_type)
	{				
		if($this->getCourseStart() &&
			$this->getCourseEnd())
		{		
			$crs_entry = new ilTEPEntry();
			$crs_entry->setOwnerId(0);

			$crs_entry->setFullday(true);
			$crs_entry->setContextId($this->getCourse()->getId());		
			$crs_entry->setType($a_entry_type);		 	
			$this->syncEntry($crs_entry);
						
			// see below - we are handling derived entries ourselves
			if($crs_entry->save(false))
			{				
				// add to course category/calendar
				$cal_ass = new ilCalendarCategoryAssignments($crs_entry->getEntryId());
				$cal_ass->addAssignment(ilTEP::getCourseCalendarId());

				// derive for all tutors
				$tutor_ids = $this->getCourse()->getMembersObject()->getTutors();
				if(sizeof($tutor_ids))
				{			
					foreach($tutor_ids as $tutor_id)
					{
						$this->deriveEntryForUser($tutor_id);
					}
				}
				
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Update entry
	 */
	public function updateEntry()
	{
		$master_id = $this->getCourseEntryId();
		if(!$master_id)
		{
			return;
		}
		
		$crs_entry = new ilTEPEntry($master_id);
		$syn_res = $this->syncEntry($crs_entry);
		if($syn_res == self::SYNC_UPDATED)
		{			
			// see below - we are handling derived entries ourselves
			if(!$crs_entry->update(false))
			{
				return;
			}
		}
		if ($syn_res == self::SYNC_DELETED) {
			return;
		}
		
		$tutor_entries = ilCalDerivedEntry::getUserIdsByMasterEntryIds(array($master_id));
		$tutor_entries = (array)$tutor_entries[$master_id];	
		
		foreach($this->getCourse()->getMembersObject()->getTutors() as $tutor_id)
		{			
			// new tutor
			if(!array_key_exists($tutor_id, $tutor_entries))
			{
				$this->deriveEntryForUser($tutor_id);			
			}
			// existing
			else
			{				
				unset($tutor_entries[$tutor_id]);
			}
		}

		// removed tutor(s)
		if(sizeof($tutor_entries))
		{
			foreach($tutor_entries as $entry_id)
			{
				$entry = new ilCalDerivedEntry($entry_id);
				$entry->delete();
			}
		}
	}
	
	/**
	 * Delete course entry
	 */
	public function deleteEntry()
	{
		$master_id = $this->getCourseEntryId();
		if(!$master_id)
		{
			return;
		}
		
		// this also deleted derived entries and operation days
		$crs_entry = new ilTEPEntry($master_id);
		$crs_entry->delete();						
	}	
}
