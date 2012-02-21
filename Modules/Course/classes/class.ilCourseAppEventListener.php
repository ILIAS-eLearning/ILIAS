<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Course Pool listener. Listens to events of other components.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
* @ingroup ModulesMediaPool
*/
class ilCourseAppEventListener
{	
	static protected $course_mode = array();
	
	/**
	* Handle an event in a listener.
	*
	* @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
	* @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
	* @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
	*/
	static function handleEvent($a_component, $a_event, $a_parameter)
	{
		global $ilUser;

		if($a_component == "Services/Tracking" && $a_event == "updateStatus")
		{
			$obj_id = $a_parameter["obj_id"];
			$user_id = $a_parameter["usr_id"];
			$status = $a_parameter["status"];
			
			if($obj_id && $user_id)
			{				
				if (ilObject::_lookupType($obj_id) != "crs")
				{
					return;
				}				
				
				// determine couse setting only once
				if(!isset(self::$course_mode[$obj_id]))
				{
					include_once("./Modules/Course/classes/class.ilObjCourse.php");
					$crs = new ilObjCourse($obj_id, false);
					if($crs->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP)
					{
						include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
						$lp_settings = new ilLPObjSettings($obj_id);
						$mode = $lp_settings->getMode();
					}
					else
					{
						$mode = false;
					}
					self::$course_mode[$obj_id] = $mode;
				}
				
				$is_completed = ($status == LP_STATUS_COMPLETED_NUM);
				
				// we are NOT using the members object because of performance issues
				switch(self::$course_mode[$obj_id])
				{
					case LP_MODE_MANUAL_BY_TUTOR:
						include_once "Modules/Course/classes/class.ilCourseParticipants.php";
						ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed, $ilUser->getId());						    										
						break;

					case LP_MODE_COLLECTION:
					case LP_MODE_OBJECTIVES:
						if($is_completed)
						{
							include_once "Modules/Course/classes/class.ilCourseParticipants.php";
							ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed, -1);	
						}
						break;
				}										
			}
		}
	}
}

?>