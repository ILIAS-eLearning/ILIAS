<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once('./Services/WebAccessChecker/interfaces/interface.ilWACCheckingClass.php');

/**
 * Class ilBadgeHandler
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeWAC implements ilWACCheckingClass
{	
	public function canBeDelivered(ilWACPath $ilWACPath) 
	{
		// :TODO:
		return true;
		
		/*
		global $ilUser, $ilAccess;
		
		if(preg_match("/\\/blog_([\\d]*)\\//uism", $ilWACPath->getPath(), $results))
		{
			$obj_id = $results[1];
			$ref_ids = ilObject::_getAllReferences($obj_id);
			foreach($ref_ids as $ref_id)
			{						
				if ($ilAccess->checkAccessOfUser($ilUser->getId(), "read", "view", $ref_id, "blog", $obj_id))
				{
					return true;
				}		
			}
		}
		
		return false;		 
		*/
	}		
}