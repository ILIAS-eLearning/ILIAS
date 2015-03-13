<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Survey to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesSurvey
 */
class ilSurveyLP extends ilObjectLP
{
	public function getDefaultMode()
	{		
		return ilLPObjSettings::LP_MODE_DEACTIVATED; // :TODO:
	}
	
	public function getValidModes()
	{				
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_SURVEY_FINISHED
		);
	}	
	
	public function isAnonymized()
	{
		include_once './Modules/Survey/classes/class.ilObjSurveyAccess.php';
		return (bool)ilObjSurveyAccess::_lookupAnonymize($this->obj_id);
	}

	protected static function isLPMember(array &$a_res, $a_usr_id, array $a_obj_ids)
	{		
		global $ilDB;
		
		// if active id
		$set = $ilDB->query("SELECT tt.obj_fi".
			" FROM tst_active ta".
			" JOIN tst_tests tt ON (ta.test_fi = tt.test_id)".
			" WHERE ".$ilDB->in("tt.obj_fi", $a_obj_ids, "", "integer").
			" AND ta.user_fi = ".$ilDB->quote($a_usr_id, "integer"));		
		while($row = $ilDB->fetchAssoc($set))
		{
			$a_res[$row["obj_fi"]] = true;
		}
		
		return true;
	}
}

?>