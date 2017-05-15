<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Exercise to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesExercise
 */
class ilExerciseLP extends ilObjectLP
{
	public function getDefaultMode()
	{		
		return ilLPObjSettings::LP_MODE_EXERCISE_RETURNED;
	}
	
	public function getValidModes()
	{
	   // START PATCH RUBRIC CPKN 2015
	    return array(            
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_EXERCISE_RETURNED,
            ilLPObjSettings::LP_MODE_RUBRIC,
		);
       // END PATCH RUBRIC CPKN 2015 
	}		
}

?>
