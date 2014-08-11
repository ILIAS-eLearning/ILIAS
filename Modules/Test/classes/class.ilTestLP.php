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
		require_once 'Services/Object/classes/class.ilObjectFactory.php';
		$testOBJ = ilObjectFactory::getInstanceByObjId($this->obj_id);

		$testOBJ->removeTestResults($a_user_ids);
	}
}

?>