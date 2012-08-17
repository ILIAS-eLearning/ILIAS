<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/WebServices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSGlossarySettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup Modules/Glossary
*/
class ilECSGlossarySettings extends ilECSObjectSettings
{		
	protected function getECSObjectType() 
	{
		return '/campusconnect/glossaries';
	}
	
	protected function buildJson(ilECSSetting $a_server) 
	{			
		$json = $this->getJsonCore('application/ecs-glossary');
		
		$json->availability = $this->content_obj->getOnline() ? 'online' : 'offline';
		
		return $json;
	}
}

?>