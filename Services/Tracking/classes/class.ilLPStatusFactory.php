<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLPStatusFactory
* Creates status class instances for learning progress modes of an object.
* E.g obj_id of course returns an instance of ilLPStatusManual, ilLPStatusObjectives ...
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesTracking
*
*/
class ilLPStatusFactory
{
	static private $class_by_obj_id = array();
	
	function _getClassById($a_obj_id, $a_mode = NULL)
	{
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

		if($a_mode === NULL)
		{
			$a_mode = ilLPObjSettings::_lookupMode($a_obj_id);
			
			// please keep the cache in this if-block, otherwise default values
			// will not trigger the include_once calls
			if (isset(self::$class_by_obj_id[$a_obj_id]))
			{
				return self::$class_by_obj_id[$a_obj_id];
			}
		}

		switch($a_mode)
		{
			case LP_MODE_VISITS:
				include_once 'Services/Tracking/classes/class.ilLPStatusVisits.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusVisits';
				
			case LP_MODE_COLLECTION:
				include_once 'Services/Tracking/classes/class.ilLPStatusCollection.php';
				return self::$class_by_obj_id[$a_obj_id] ='ilLPStatusCollection';

			case LP_MODE_TLT:
				include_once 'Services/Tracking/classes/class.ilLPStatusTypicalLearningTime.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusTypicalLearningTime';

			case LP_MODE_SCORM:
				include_once 'Services/Tracking/classes/class.ilLPStatusSCORM.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusSCORM';

			case LP_MODE_DEACTIVATED:
				include_once 'Services/Tracking/classes/class.ilLPStatus.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatus';

			case LP_MODE_TEST_FINISHED:
				include_once 'Services/Tracking/classes/class.ilLPStatusTestFinished.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusTestFinished';

			case LP_MODE_TEST_PASSED:
				include_once 'Services/Tracking/classes/class.ilLPStatusTestPassed.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusTestPassed';

			case LP_MODE_MANUAL:
				include_once 'Services/Tracking/classes/class.ilLPStatusManual.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusManual';

			case LP_MODE_MANUAL_BY_TUTOR:
				include_once 'Services/Tracking/classes/class.ilLPStatusManualByTutor.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusManualByTutor';

			case LP_MODE_EXERCISE_RETURNED:
				include_once 'Services/Tracking/classes/class.ilLPStatusExerciseReturned.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusExerciseReturned';

			case LP_MODE_OBJECTIVES:
				include_once 'Services/Tracking/classes/class.ilLPStatusObjectives.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusObjectives';

			case LP_MODE_SCORM_PACKAGE:
				include_once 'Services/Tracking/classes/class.ilLPStatusSCORMPackage.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusSCORMPackage';
				
			case LP_MODE_EVENT:
				include_once('./Services/Tracking/classes/class.ilLPStatusEvent.php');
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusEvent';

			case LP_MODE_UNDEFINED:
				$type = ilObject::_lookupType($a_obj_id);
				$mode = ilLPObjSettings::__getDefaultMode($a_obj_id, $type);
				if($mode != LP_MODE_UNDEFINED)
				{
					return self::$class_by_obj_id[$a_obj_id] = self::_getClassById($a_obj_id, $mode);
				}
				// fallthrough

			default:
				echo "ilLPStatusFactory: unknown type ".ilLPObjSettings::_lookupMode($a_obj_id);
				exit;
		}
	}

	function _getClassByIdAndType($a_obj_id,$a_type)
	{
		// id is ignored in the moment
		switch($a_type)
		{
			case 'event':
				include_once 'Services/Tracking/classes/class.ilLPStatusEvent.php';
				return 'ilLPStatusEvent';

			default:
				echo "ilLPStatusFactory: unknown type: ".$a_type;
				exit;
		}
	}

	function &_getInstance($a_obj_id, $a_mode = NULL)
	{
		include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

		if($a_mode === NULL)
		{
			$a_mode = ilLPObjSettings::_lookupMode($a_obj_id);
		}
		
		switch($a_mode)
		{
			case LP_MODE_VISITS:
				include_once 'Services/Tracking/classes/class.ilLPStatusVisits.php';
				return new ilLPStatusVisits($a_obj_id);

			case LP_MODE_COLLECTION:
				include_once 'Services/Tracking/classes/class.ilLPStatusCollection.php';
				return new ilLPStatusCollection($a_obj_id);

			case LP_MODE_TLT:
				include_once 'Services/Tracking/classes/class.ilLPStatusTypicalLearningTime.php';
				return new ilLPStatusTypicalLearningTime($a_obj_id);

			case LP_MODE_SCORM:
				include_once 'Services/Tracking/classes/class.ilLPStatusSCORM.php';
				return new ilLPStatusSCORM($a_obj_id);

			case LP_MODE_TEST_FINISHED:
				include_once 'Services/Tracking/classes/class.ilLPStatusTestFinished.php';
				return new ilLPStatusTestFinished($a_obj_id);

			case LP_MODE_TEST_PASSED:
				include_once 'Services/Tracking/classes/class.ilLPStatusTestPassed.php';
				return new ilLPStatusTestPassed($a_obj_id);

			case LP_MODE_MANUAL:
				include_once 'Services/Tracking/classes/class.ilLPStatusManual.php';
				return new ilLPStatusManual($a_obj_id);

			case LP_MODE_MANUAL_BY_TUTOR:
				include_once 'Services/Tracking/classes/class.ilLPStatusManualByTutor.php';
				return new ilLPStatusManualByTutor($a_obj_id);

			case LP_MODE_EXERCISE_RETURNED:
				include_once 'Services/Tracking/classes/class.ilLPStatusExerciseReturned.php';
				return new ilLPStatusExerciseReturned($a_obj_id);

			case LP_MODE_OBJECTIVES:
				include_once 'Services/Tracking/classes/class.ilLPStatusObjectives.php';
				return new ilLPStatusObjectives($a_obj_id);
				
			case LP_MODE_EVENT:
				include_once 'Services/Tracking/classes/class.ilLPStatusEvent.php';
				return new ilLPStatusEvent($a_obj_id);
				
			case LP_MODE_UNDEFINED:
				$type = ilObject::_lookupType($a_obj_id);
				$mode = ilLPObjSettings::__getDefaultMode($a_obj_id, $type);
				if($mode != LP_MODE_UNDEFINED)
				{
					return self::_getInstance($a_obj_id, $mode);
				}
				// fallthrough		

			default:
				echo "ilLPStatusFactory: unknown type ".ilLPObjSettings::_lookupMode($a_obj_id);
				exit;
		}
	}
}
?>