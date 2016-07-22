<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";
require_once "./Services/Badge/interfaces/interface.ilBadgeAuto.php";

/**
 * Class ilUserProfileBadge
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesUser
 */
class ilUserProfileBadge implements ilBadgeType, ilBadgeAuto
{
	public function getId()
	{
		return "profile";
	}
	
	public function getCaption()
	{
		global $lng;
		return $lng->txt("badge_user_profile");
	}
	
	public function isSingleton()
	{
		return false;
	}
	
	public function getValidObjectTypes()
	{
		return array("bdga");
	}
	
	public function getConfigGUIInstance()
	{
		include_once "Services/User/classes/Badges/class.ilUserProfileBadgeGUI.php";
		return new ilUserProfileBadgeGUI();
	}
	
	public function evaluate($a_user_id, array $a_params, array $a_config)
	{
		$user = new ilObjUser($a_user_id);
		
		// use getter mapping from user profile
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$pfields = $up->getStandardFields();
		
		foreach($a_config["profile"] as $field)
		{
			$field = substr($field, 4);
			
			// instant messengers 
			if(substr($field, 0, 3) == "im_")
			{
				$im = substr($field, 3);
				if(!$user->getInstantMessengerId($im))
				{
					return false;
				}
			}
			// udf
			else if(substr($field, 0, 4) == "udf_")
			{
				$udf = $user->getUserDefinedData();			
				if($udf["f_".substr($field, 4)] == "")
				{
					return false;
				}				
			}
			// picture
			else if($field == "upload")
			{
				if(!ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true, true))
				{
					return false;
				}
			}
			// use profile mapping if possible
			else if(isset($pfields[$field]["method"]))
			{
				$m = $pfields[$field]["method"];		
				if(!$user->{$m}())
				{				
					return false;
				}				
			}						
		}
		
		return true;		
	}
}