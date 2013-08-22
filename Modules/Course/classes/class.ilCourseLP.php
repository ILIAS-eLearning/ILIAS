<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Course to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesCourse
 */
class ilCourseLP extends ilObjectLP
{
	public function getDefaultMode()
	{
		if($this->checkObjectives())
		{
			return ilLPObjSettings::LP_MODE_OBJECTIVES;
		}
		return ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR;
	}
	
	public function getValidModes()
	{		
		if($this->checkObjectives())
		{
			return array(ilLPObjSettings::LP_MODE_OBJECTIVES);
		}
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR, 
			ilLPObjSettings::LP_MODE_COLLECTION
		);
	}	
	
	public function getCurrentMode()
	{
		if($this->checkObjectives())
		{
			return ilLPObjSettings::LP_MODE_OBJECTIVES;
		}
		return parent::getCurrentMode();
	}
	
	protected function checkObjectives()
	{
		include_once "Modules/Course/classes/class.ilObjCourse.php";
		if(ilObjCourse::_lookupViewMode($this->obj_id) == IL_CRS_VIEW_OBJECTIVE)
		{
			return true;
		}
		return false;
	}
	
	public function getMembers($a_search = true)
	{	
		include_once "Modules/Course/classes/class.ilCourseParticipants.php";
		$member_obj = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
		return $member_obj->getMembers();		
	}			
}

?>