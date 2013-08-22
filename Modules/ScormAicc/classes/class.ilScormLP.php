<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * SCORM to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesScormAicc
 */
class ilScormLP extends ilObjectLP
{
	public function getDefaultMode()
	{		
		return ilLPObjSettings::LP_MODE_DEACTIVATED;
	}
	
	public function getValidModes()
	{					
		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";		
		$subtype = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
		if($subtype != "scorm2004")
		{
			if($this->checkSCORMPreconditions())
			{
				return array(ilLPObjSettings::LP_MODE_SCORM);
			}
			
			include_once "Services/Tracking/classes/collection/class.ilLPCollectionOfSCOs.php";	
			$collection = new ilLPCollectionOfSCOs($this->obj_id, ilLPObjSettings::LP_MODE_SCORM);				
			if(sizeof($collection->getPossibleItems()))			
			{
				return array(ilLPObjSettings::LP_MODE_DEACTIVATED, 
					ilLPObjSettings::LP_MODE_SCORM);
			}
			return array(ilLPObjSettings::LP_MODE_DEACTIVATED);
		}
		else
		{
			if($this->checkSCORMPreconditions())
			{
				return array(ilLPObjSettings::LP_MODE_SCORM,	
					ilLPObjSettings::LP_MODE_SCORM_PACKAGE);
			}
			
			include_once "Services/Tracking/classes/collection/class.ilLPCollectionOfSCOs.php";	
			$collection = new ilLPCollectionOfSCOs($this->obj_id, ilLPObjSettings::LP_MODE_SCORM);				
			if(sizeof($collection->getPossibleItems()))			
			{
				return array(ilLPObjSettings::LP_MODE_DEACTIVATED,
					ilLPObjSettings::LP_MODE_SCORM_PACKAGE,
					ilLPObjSettings::LP_MODE_SCORM);
			}
			
			return array(ilLPObjSettings::LP_MODE_DEACTIVATED,
				ilLPObjSettings::LP_MODE_SCORM_PACKAGE);
		}
	}		
	
	public function getCurrentMode()
	{
		if($this->checkSCORMPreconditions())
		{
			return ilLPObjSettings::LP_MODE_SCORM;
		}
		return parent::getCurrentMode();
	}
	
	protected function checkSCORMPreconditions()
	{
		include_once('./Services/AccessControl/classes/class.ilConditionHandler.php');
		if(count(ilConditionHandler::_getConditionsOfTrigger('sahs', $this->obj_id)))
		{
			return true;
		}
		return false;
	}
}

?>