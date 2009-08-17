<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilUserUtil
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilUserUtil
{
	/**
	 * Default behaviour is:
	 * - lastname, firstname if public profile enabled
	 * - [loginname] (always)
	 */
	static function getNamePresentation($a_user_id, 
		$a_user_image = false, $a_profile_link = false, $a_profile_back_link = "",
		$a_force_first_lastname = false)
	{
		global $lng, $ilCtrl;
		
		if (ilObject::_lookupType($a_user_id) != "usr")
		{
			return $lng->txt("unknown");
		}
		
		$user = ilObjUser::_lookupName($a_user_id);
		$login = ilObjUser::_lookupLogin($a_user_id);

		if ($a_force_first_lastname ||
			in_array(ilObjUser::_lookupPref($a_user_id, "public_profile"), array("y", "g")))
		{
			$pres = $user["lastname"].", ".$user["title"]." ".$user["firstname"]." ";
		}
		
		$pres.= "[".$login."]";
		
		if ($a_profile_link && 
			in_array(ilObjUser::_lookupPref($a_user_id, "public_profile"), array("y", "g")))
		{
			$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $a_user_id);
			if ($a_profile_back_link != "")
			{
				$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
					rawurlencode($a_profile_back_link));
			}
			$pres = '<a href="'.$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML").'">'.$pres.'</a>';
		}

		if ($a_user_image)
		{
			$img = ilObjUser::_getPersonalPicturePath($a_user_id, "xxsmall");
			$pres = '<img border="0" src="'.$img.'" alt="'.$lng->txt("icon").
				" ".$lng->txt("user_picture").'" /> '.$pres;
		}

		return $pres;
	}
	
}
?>
