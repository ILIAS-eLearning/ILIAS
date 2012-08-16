<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Webservices/ECS/classes/class.ilECSObjectSettings.php';

/**
* Class ilECSFileSettings
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id: class.ilObjCourseGUI.php 31646 2011-11-14 11:39:37Z jluetzen $
*
* @ingroup Modules/File
*/
class ilECSFileSettings extends ilECSObjectSettings
{		
	protected function getECSObjectType() 
	{
		return '/campusconnect/files';
	}
	
	protected function buildJson(ilECSSetting $a_server) 
	{			
		$json = $this->getJsonCore('application/ecs-file');	
		
		// :TODO:
		$json->version = $this->content_obj->getVersion();
		$json->version_tstamp = time();
		
		return $json;
	}
}

?>