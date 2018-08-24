<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSCourseSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup ModulesCourse
*/
class ilECSCourseSettings extends ilECSObjectSettings
{		
	protected function getECSObjectType() 
	{
		return '/campusconnect/courselinks';
	}
	
	protected function buildJson(ilECSSetting $a_server) 
	{	
		global $ilLog;
		
		$json = $this->getJsonCore('application/ecs-course');
		
		// meta language
		include_once('./Services/MetaData/classes/class.ilMDLanguage.php');
		$lang = ilMDLanguage::_lookupFirstLanguage($this->content_obj->getId(),$this->content_obj->getId(),$this->content_obj->getType());
		if(strlen($lang))
	 	{
	 		$json->lang = $lang.'_'.strtoupper($lang);
	 	}
		
		$json->status = $this->content_obj->isActivated() ? 'online' : 'offline';
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSUtils.php');
		$definition = ilECSUtils::getEContentDefinition($this->getECSObjectType());
		$this->addMetadataToJson($json, $a_server, $definition);
		
		$json->courseID = 'il_'.IL_INST_ID.'_'.$this->getContentObject()->getType().'_'.$this->getContentObject()->getId();
		
		return $json;
	}
}

?>