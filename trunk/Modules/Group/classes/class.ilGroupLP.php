<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObjectLP.php";

/**
 * Group to lp connector
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: class.ilLPStatusPlugin.php 43734 2013-07-29 15:27:58Z jluetzen $
 * @package ModulesGroup
 */
class ilGroupLP extends ilObjectLP
{
	public function getDefaultMode()
	{		
		return ilLPObjSettings::LP_MODE_DEACTIVATED;
	}
	
	public function getValidModes()
	{				
		return array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR, 
			ilLPObjSettings::LP_MODE_COLLECTION
		);
	}	
	
	public function getMembers($a_search = true)
	{	
		include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
		$member_obj = ilGroupParticipants::_getInstanceByObjId($this->obj_id);
		return $member_obj->getMembers();					
	}			
}

?>