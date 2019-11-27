<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilECSGlossarySettings
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
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