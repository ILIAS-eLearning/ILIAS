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
		include_once "Services/User/classes/class.ilUserProfile.php";
		$user = new ilObjUser($a_user_id);
		
		// see ilPersonalProfileGUI::savePersonalData()		
		// if form field name differs from setter
		$map = array(
			"firstname" => "FirstName",
			"lastname" => "LastName",
			"title" => "UTitle",
			"sel_country" => "SelectedCountry",
			"phone_office" => "PhoneOffice",
			"phone_home" => "PhoneHome",
			"phone_mobile" => "PhoneMobile",
			"referral_comment" => "Comment",
			"interests_general" => "GeneralInterests",
			"interests_help_offered" => "OfferingHelp",
			"interests_help_looking" => "LookingForHelp"
		);
		
		foreach($a_config["profile"] as $field)
		{
			$field = substr($field, 4);
			$m = ucfirst($field);			
			if(isset($map[$field]))
			{
				$m = $map[$field];
			}	
			if(!$user->{"get".$m}())
			{				
				return false;
			}
		}

		return true;		
	}
}