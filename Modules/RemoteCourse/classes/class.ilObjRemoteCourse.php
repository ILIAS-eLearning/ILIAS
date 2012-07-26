<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBase.php');

/** 
* Remote course app class
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ModulesRemoteCourse
*/

class ilObjRemoteCourse extends ilRemoteObjectBase
{
	const DB_TABLE_NAME = "remote_course_settings";
	
	const ACTIVATION_OFFLINE = 0;
	const ACTIVATION_UNLIMITED = 1;
	const ACTIVATION_LIMITED = 2;
	
	protected $availability_type;
	protected $end;
	protected $start;
	
	public function initType()
	{
		$this->type = "rcrs";
	}
	
	protected function getTableName()
	{
		return self::DB_TABLE_NAME;
	}
	
	/**
	 * Set Availability type
	 *
	 * @param int $a_type availability type
	 */
	public function setAvailabilityType($a_type)
	{
	 	$this->availability_type = $a_type;
	}
	
	/**
	 * get availability type
	 *
	 * @return int
	 */
	public function getAvailabilityType()
	{
	 	return $this->availability_type;
	}
	
	/**
	 * set starting time
	 *
	 * @param timestamp $a_time starting time
	 */
	public function setStartingTime($a_time)
	{
	 	$this->start = $a_time;
	}
	
	/**
	 * get starting time
	 *
	 * @return timestamp
	 */
	public function getStartingTime()
	{
	 	return $this->start;
	}

	/**
	 * set ending time
	 *
	 * @param timestamp $a_time ending time
	 */
	public function setEndingTime($a_time)
	{
	 	$this->end = $a_time;
	}
	
	/**
	 * get ending time
	 *
	 * @return timestamp
	 */
	public function getEndingTime()
	{
	 	return $this->end;
	}
		
	/**
	 * Lookup online
	 *
	 * @param int $a_obj_id obj_id
	 * @return bool
	 */
	public static function _lookupOnline($a_obj_id)
	{
		global $ilDB;
		
		$query = "SELECT * FROM ".self::DB_TABLE_NAME.
			" WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer')." ";
		$res = $ilDB->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
		switch($row->availability_type)
		{
			case self::ACTIVATION_UNLIMITED:
				return true;
				
			case self::ACTIVATION_OFFLINE:
				return false;
				
			case self::ACTIVATION_LIMITED:
				return time() > $row->r_start && time < $row->r_end;
				
			default:
				return false;
		}
		
		return false;
	}
	
	protected function doCreateCustomFields(array &$a_fields)
	{
		$a_fields["availability_type"] = array("integer", 0);
		$a_fields["r_start"] = array("integer", 0);
		$a_fields["r_end"] = array("integer", 0);		
	}

	protected function doUpdateCustomFields(array &$a_fields)
	{		
		$a_fields["availability_type"] = array("integer", $this->getAvailabilityType());
		$a_fields["r_start"] = array("integer", $this->getStartingTime());
		$a_fields["r_end"] = array("integer", $this->getEndingTime());			
	}

	protected function doReadCustomFields($a_row)
	{				
		$this->setAvailabilityType($a_row->availability_type);
		$this->setStartingTime($a_row->r_start);
		$this->setEndingTime($a_row->r_end);	
	}
	
	protected function updateCustomFromECSContent(ilECSEContent $a_ecs_content, ilECSDataMappingSettings $a_mappings)
	{		
		// Study courses
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'study_courses'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getStudyCourses());
			$value->save();
		}

		// Lecturer
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'lecturer'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getLecturers());
			$value->save();
		}
		// CourseType
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'courseType'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getCourseType());
			$value->save();
		}
		// CourseID
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'courseID'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getCourseID());
			$value->save();
		}		
		// Credits
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'credits'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getCredits());
			$value->save();
		}
		
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'semester_hours'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getSemesterHours());
			$value->save();
		}
		// Term
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'term'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getTerm());
			$value->save();
		}
		
		// TIME PLACE OBJECT ########################
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'begin'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			
			switch(ilAdvancedMDFieldDefinition::_lookupFieldType($field))
			{
				case ilAdvancedMDFieldDefinition::TYPE_DATE:
				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
					$value->setValue($a_ecs_content->getTimePlace()->getUTBegin());
					break;
				default:
					$value->setValue($a_ecs_content->getTimePlace()->getBegin());
					break;
			}
			$value->save();
		}
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'end'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			switch(ilAdvancedMDFieldDefinition::_lookupFieldType($field))
			{
				case ilAdvancedMDFieldDefinition::TYPE_DATE:
				case ilAdvancedMDFieldDefinition::TYPE_DATETIME:
					$value->setValue($a_ecs_content->getTimePlace()->getUTEnd());
					break;
				default:
					$value->setValue($a_ecs_content->getTimePlace()->getEnd());
					break;
			}
			$value->save();
		}
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'room'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getTimePlace()->getRoom());
			$value->save();
		}
		if($field = $a_mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_IMPORT_RCRS,'cycle'))
		{
			$value = ilAdvancedMDValue::_getInstance($this->getId(),$field);
			$value->toggleDisabledStatus(true); 
			$value->setValue($a_ecs_content->getTimePlace()->getCycle());
			$value->save();
		}
		
		// add custom values
		$this->setAvailabilityType($a_ecs_content->isOnline() ? self::ACTIVATION_UNLIMITED : self::ACTIVATION_OFFLINE);		
	}
	
		
	// 
	// no late static binding yet
	//
	
	public static function _lookupMID($a_obj_id)
	{
		return ilRemoteObjectBase::_lookupMID($a_obj_id, self::DB_TABLE_NAME);
	}
	
	public static function _lookupObjIdsByMID($a_mid)
	{
		return ilRemoteObjectBase::_lookupObjIdsByMID($a_mid, self::DB_TABLE_NAME);
	}
	
	public static function _lookupOrganization($a_obj_id)
	{
		return ilRemoteObjectBase::_lookupOrganization($a_obj_id, self::DB_TABLE_NAME);
	}
}

?>