<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TEP event listener. Listens to events of other components (course).
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesTEP
 */
class ilTEPAppEventListener
{			
	// :TODO:
	const COURSE_ENTRY_TYPE = "crs";
	
	public static function handleEvent($a_component, $a_event, $a_parameter)
	{		
		if($a_component == "Modules/Course")
		{						
			require_once "Services/TEP/classes/class.ilTEPCourseEntries.php";			
			
			switch($a_event)
			{
				case "create":
					// params: object, obj_id, appointments					
					// :TODO: no course period yet - nothing we can do
					return;
				
				case "update":
					// params: object, obj_id, appointments					
					self::syncCourse($a_parameter["object"]);
					break;
				
				case "delete":
					// params: object, obj_id, appointments					
					$course_entries = ilTEPCourseEntries::getInstance($a_parameter["object"]);
					$course_entries->deleteEntry();
					break;
				
				case "addParticipant":
					// params: obj_id, usr_id, role_id							
					self::syncCourseByObjectId($a_parameter["obj_id"]);					
					break;
				
				case "deleteParticipant":
					// params: obj_id, usr_id					
					self::syncCourseByObjectId($a_parameter["obj_id"]);	
					break;
			}						
		}
		else if($a_component == "Services/User" && $a_event == "deleteUser")
		{
			// params: usr_id
			require_once "Services/TEP/classes/class.ilTEP.php";
			ilTEP::deleteUser($a_parameter["usr_id"]);
		}
	}
	
	protected static function syncCourse(ilObjCourse $a_course)
	{						
		$course_entries = ilTEPCourseEntries::getInstance( $a_course);
		if(!$course_entries->getCourseEntryId())
		{
			// try if entry can be created (missing period, etc)
			$course_entries->createEntry(self::COURSE_ENTRY_TYPE);
		}
		else
		{
			$course_entries->updateEntry();
		}
	}
	
	protected static function syncCourseByObjectId($a_course_obj_id)
	{
		require_once "Modules/Course/classes/class.ilObjCourse.php";
		$course = new ilObjCourse($a_course_obj_id, false);
		return self::syncCourse($course);					
	}
}
