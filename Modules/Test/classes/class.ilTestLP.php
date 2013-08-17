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
		return LP_MODE_TEST_PASSED;
	}
	
	public function getValidModes()
	{				
		return array(
			LP_MODE_DEACTIVATED,
			LP_MODE_TEST_FINISHED, 
			LP_MODE_TEST_PASSED
		);
	}		
}

?>