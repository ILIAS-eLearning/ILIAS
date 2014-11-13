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
			// #13905
			include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
			if(!ilObjUserTracking::_enabledLearningProgress())
			{
				return;
			}
			
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
						include_once './Services/Object/classes/class.ilObjectLP.php';
						$olp = ilObjectLP::getInstance($obj_id);
						$mode = $olp->getCurrentMode();
					}
					else
					{
						$mode = false;
					}
					self::$course_mode[$obj_id] = $mode;
				}
				
				$is_completed = ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM);
				
				// we are NOT using the members object because of performance issues
				switch(self::$course_mode[$obj_id])
				{
					case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
						// #11600
						include_once "Modules/Course/classes/class.ilCourseParticipants.php";
						ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed, true);						    										
						break;

					case ilLPObjSettings::LP_MODE_COLLECTION:
					case ilLPObjSettings::LP_MODE_OBJECTIVES:						
						// overwrites course passed status if it was set automatically (full sync)
						// or toggle manually set passed status to completed (1-way-sync)						
						$do_update = $is_completed;
						include_once "Modules/Course/classes/class.ilCourseParticipants.php";
						if(!$do_update)
						{
							$part = new ilCourseParticipants($obj_id);
							$passed = $part->getPassedInfo($user_id);	
							if(!is_array($passed) || 
								$passed["user_id"] == -1)
							{
								$do_update = true;
							}									
						}
						if($do_update)
						{						
							ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed);	
						}
						break;
				}										
			}
		}
	}
}

?>