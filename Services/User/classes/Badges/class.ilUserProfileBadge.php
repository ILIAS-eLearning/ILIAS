<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeType.php";

/**
 * Class ilUserProfileBadge
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesUser
 */
class ilUserProfileBadge implements ilBadgeType
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
}