<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Tracking/classes/class.ilLPObjSettings.php';

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
	
	const PATH = 'Services/Tracking/classes/status/';
	
	function _getClassById($a_obj_id, $a_mode = NULL)
	{		
		if($a_mode === NULL)
		{
			include_once 'Services/Object/classes/class.ilObjectLP.php';
			$olp = ilObjectLP::getInstance($a_obj_id);					
			$a_mode = $olp->getCurrentMode();
			
			// please keep the cache in this if-block, otherwise default values
			// will not trigger the include_once calls
			if (isset(self::$class_by_obj_id[$a_obj_id]))
			{
				return self::$class_by_obj_id[$a_obj_id];
			}
		}

		switch($a_mode)
		{
			case ilLPObjSettings::LP_MODE_VISITS:
				include_once self::PATH.'class.ilLPStatusVisits.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusVisits';
				
			case ilLPObjSettings::LP_MODE_COLLECTION:
				include_once self::PATH.'class.ilLPStatusCollection.php';
				return self::$class_by_obj_id[$a_obj_id] ='ilLPStatusCollection';

			case ilLPObjSettings::LP_MODE_TLT:
				include_once self::PATH.'class.ilLPStatusTypicalLearningTime.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusTypicalLearningTime';

			case ilLPObjSettings::LP_MODE_SCORM:
				include_once self::PATH.'class.ilLPStatusSCORM.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusSCORM';

			case ilLPObjSettings::LP_MODE_TEST_FINISHED:
				include_once self::PATH.'class.ilLPStatusTestFinished.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusTestFinished';

			case ilLPObjSettings::LP_MODE_TEST_PASSED:
				include_once self::PATH.'class.ilLPStatusTestPassed.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusTestPassed';

			case ilLPObjSettings::LP_MODE_MANUAL:
				include_once self::PATH.'class.ilLPStatusManual.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusManual';

			case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
				include_once self::PATH.'class.ilLPStatusManualByTutor.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusManualByTutor';

			case ilLPObjSettings::LP_MODE_EXERCISE_RETURNED:
				include_once self::PATH.'class.ilLPStatusExerciseReturned.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusExerciseReturned';

			case ilLPObjSettings::LP_MODE_OBJECTIVES:
				include_once self::PATH.'class.ilLPStatusObjectives.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusObjectives';

			case ilLPObjSettings::LP_MODE_SCORM_PACKAGE:
				include_once self::PATH.'class.ilLPStatusSCORMPackage.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusSCORMPackage';
				
			case ilLPObjSettings::LP_MODE_EVENT:
				include_once self::PATH.'class.ilLPStatusEvent.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusEvent';
				
			case ilLPObjSettings::LP_MODE_PLUGIN:
				include_once self::PATH.'class.ilLPStatusPlugin.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusPlugin';
				
			case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
				include_once self::PATH.'class.ilLPStatusCollectionTLT.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusCollectionTLT';
				
			case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
				include_once self::PATH.'class.ilLPStatusCollectionManual.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusCollectionManual';
				
			case ilLPObjSettings::LP_MODE_QUESTIONS:
				include_once self::PATH.'class.ilLPStatusQuestions.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatusQuestions';

			case ilLPObjSettings::LP_MODE_DEACTIVATED:
				include_once 'Services/Tracking/classes/class.ilLPStatus.php';
				return self::$class_by_obj_id[$a_obj_id] = 'ilLPStatus';

			case ilLPObjSettings::LP_MODE_UNDEFINED:
				include_once 'Services/Object/classes/class.ilObjectLP.php';
				$olp = ilObjectLP::getInstance($a_obj_id);					
				$mode = $olp->getCurrentMode();
				if($mode != ilLPObjSettings::LP_MODE_UNDEFINED)
				{
					return self::$class_by_obj_id[$a_obj_id] = self::_getClassById($a_obj_id, $mode);
				}
				// fallthrough

			default:
				echo "ilLPStatusFactory: unknown type ".$a_mode;
				exit;
		}
	}

	function _getClassByIdAndType($a_obj_id,$a_type)
	{
		// id is ignored in the moment
		switch($a_type)
		{
			case 'event':
				include_once self::PATH.'class.ilLPStatusEvent.php';
				return 'ilLPStatusEvent';

			default:
				echo "ilLPStatusFactory: unknown type: ".$a_type;
				exit;
		}
	}

	function _getInstance($a_obj_id, $a_mode = NULL)
	{		
		if($a_mode === NULL)
		{
			include_once 'Services/Object/classes/class.ilObjectLP.php';
			$olp = ilObjectLP::getInstance($a_obj_id);					
			$a_mode = $olp->getCurrentMode();
		}
		
		switch($a_mode)
		{
			case ilLPObjSettings::LP_MODE_VISITS:
				include_once self::PATH.'class.ilLPStatusVisits.php';
				return new ilLPStatusVisits($a_obj_id);

			case ilLPObjSettings::LP_MODE_COLLECTION:
				include_once self::PATH.'class.ilLPStatusCollection.php';
				return new ilLPStatusCollection($a_obj_id);

			case ilLPObjSettings::LP_MODE_TLT:
				include_once self::PATH.'class.ilLPStatusTypicalLearningTime.php';
				return new ilLPStatusTypicalLearningTime($a_obj_id);

			case ilLPObjSettings::LP_MODE_SCORM:
				include_once self::PATH.'class.ilLPStatusSCORM.php';
				return new ilLPStatusSCORM($a_obj_id);

			case ilLPObjSettings::LP_MODE_TEST_FINISHED:
				include_once self::PATH.'class.ilLPStatusTestFinished.php';
				return new ilLPStatusTestFinished($a_obj_id);

			case ilLPObjSettings::LP_MODE_TEST_PASSED:
				include_once self::PATH.'class.ilLPStatusTestPassed.php';
				return new ilLPStatusTestPassed($a_obj_id);

			case ilLPObjSettings::LP_MODE_MANUAL:
				include_once self::PATH.'class.ilLPStatusManual.php';
				return new ilLPStatusManual($a_obj_id);

			case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
				include_once self::PATH.'class.ilLPStatusManualByTutor.php';
				return new ilLPStatusManualByTutor($a_obj_id);

			case ilLPObjSettings::LP_MODE_EXERCISE_RETURNED:
				include_once self::PATH.'class.ilLPStatusExerciseReturned.php';
				return new ilLPStatusExerciseReturned($a_obj_id);

			case ilLPObjSettings::LP_MODE_OBJECTIVES:
				include_once self::PATH.'class.ilLPStatusObjectives.php';
				return new ilLPStatusObjectives($a_obj_id);

			case ilLPObjSettings::LP_MODE_SCORM_PACKAGE:
				include_once self::PATH.'class.ilLPStatusSCORMPackage.php';
				return new ilLPStatusSCORMPackage($a_obj_id);

				case ilLPObjSettings::LP_MODE_EVENT:
				include_once self::PATH.'class.ilLPStatusEvent.php';
				return new ilLPStatusEvent($a_obj_id);
				
			case ilLPObjSettings::LP_MODE_PLUGIN:
				include_once self::PATH.'class.ilLPStatusPlugin.php';
				return new ilLPStatusPlugin($a_obj_id);
				
			case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
				include_once self::PATH.'class.ilLPStatusCollectionTLT.php';
				return new ilLPStatusCollectionTLT($a_obj_id);
				
			case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
				include_once self::PATH.'class.ilLPStatusCollectionManual.php';
				return new ilLPStatusCollectionManual($a_obj_id);
									
			case ilLPObjSettings::LP_MODE_QUESTIONS:
				include_once self::PATH.'class.ilLPStatusQuestions.php';
				return new ilLPStatusQuestions($a_obj_id);
				
			case ilLPObjSettings::LP_MODE_DEACTIVATED:
				include_once 'Services/Tracking/classes/class.ilLPStatus.php';
				return new ilLPStatus($a_obj_id);
				
			case ilLPObjSettings::LP_MODE_UNDEFINED:
				include_once 'Services/Object/classes/class.ilObjectLP.php';
				$olp = ilObjectLP::getInstance($a_obj_id);					
				$mode = $olp->getCurrentMode();
				if($mode != ilLPObjSettings::LP_MODE_UNDEFINED)
				{
					return self::_getInstance($a_obj_id, $mode);
				}
				// fallthrough		

			default:
				echo "ilLPStatusFactory: unknown type ".$a_mode;
				exit;
		}
	}
}

?>