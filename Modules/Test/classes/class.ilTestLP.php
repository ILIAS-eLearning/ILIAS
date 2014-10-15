<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Test to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesTest
 */
class ilTestLP extends ilObjectLP
{
	public function getDefaultMode()
	{		
		return ilLPObjSettings::LP_MODE_TEST_PASSED;
	}
	
	public function getValidModes()
	{				
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_TEST_FINISHED, 
			ilLPObjSettings::LP_MODE_TEST_PASSED
		);
	}	
	
	public function isAnonymized()
	{
		include_once './Modules/Test/classes/class.ilObjTest.php';
		return (bool)ilObjTest::_lookupAnonymity($this->obj_id);
	}

	protected function resetCustomLPDataForUserIds(array $a_user_ids, $a_recursive = true)
	{
		/* @var ilObjTest $testOBJ */
		require_once 'Services/Object/classes/class.ilObjectFactory.php';
		$testOBJ = ilObjectFactory::getInstanceByObjId($this->obj_id);
		$testOBJ->removeTestResults($a_user_ids);
		
		// :TODO: there has to be a better way
		$test_ref_id = (int)$_REQUEST["ref_id"];		
		if($test_ref_id)
		{
			require_once "Modules/Course/classes/Objectives/class.ilLOSettings.php";
			$course_obj_id = ilLOSettings::isObjectiveTest($test_ref_id);
			if($course_obj_id)
			{
				// is test initial and/or qualified?
				$lo_settings = ilLOSettings::getInstanceByObjId($course_obj_id);				
				$is_i = ($lo_settings->getInitialTest() == $test_ref_id);
				$is_q = ($lo_settings->getQualifiedTest() == $test_ref_id);		
				
				// remove objective results data
				require_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
				ilLOUserResults::deleteResultsFromLP($course_obj_id, $a_user_ids, $is_i, $is_q);
				
				// refresh LP - see ilLPStatusWrapper::_updateStatus()
				require_once "Services/Tracking/classes/class.ilLPStatusFactory.php";
				$lp_status = ilLPStatusFactory::_getInstance($course_obj_id);	
				if (strtolower(get_class($lp_status)) != "illpstatus")
				{
					foreach($a_user_ids as $user_id)
					{
						$lp_status->_updateStatus($course_obj_id, $user_id);
					}
				}
			}
		}
	}
}

?>