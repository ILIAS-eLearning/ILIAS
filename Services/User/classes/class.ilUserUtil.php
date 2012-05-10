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
	 * modifications by jposselt at databay . de :
	 * if $a_user_id is an array of user ids the method returns an array of
	 * "id" => "NamePresentation" pairs.
	 */
	static function getNamePresentation($a_user_id, 
		$a_user_image = false, $a_profile_link = false, $a_profile_back_link = "",
		$a_force_first_lastname = false)
	{
		global $lng, $ilCtrl, $ilDB;
		
		if (!($return_as_array = is_array($a_user_id)))
			$a_user_id = array($a_user_id);
		
		$sql = 'SELECT
					a.usr_id, 
					firstname,
					lastname,
					title,
					login,
					b.value public_profile,
					c.value public_title
				FROM
					usr_data a 
					LEFT JOIN 
						usr_pref b ON 
							(a.usr_id = b.usr_id AND
							b.keyword = %s)
					LEFT JOIN 
						usr_pref c ON 
							(a.usr_id = c.usr_id AND
							c.keyword = %s)
				WHERE ' . $ilDB->in('a.usr_id', $a_user_id, false, 'integer');
		
		$userrow = $ilDB->queryF($sql, array('text', 'text'), array('public_profile', 'public_title'));
		
		$names = array();
		
		while ($row = $ilDB->fetchObject($userrow))
		{
			if ($a_force_first_lastname ||
				$has_public_profile = in_array($row->public_profile, array("y", "g")))
			{
				$title = "";
				if($row->public_title == "y" && $row->title)
				{
					$title = $row->title . " ";
				}				
				$pres = $row->lastname.", ".$title.$row->firstname." ";
			}
			
			$pres.= "[".$row->login."]";
			
			if ($a_profile_link && $has_public_profile)
			{
				$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user", $row->usr_id);
				if ($a_profile_back_link != "")
				{
					$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
						rawurlencode($a_profile_back_link));
				}
				$pres = '<a href="'.$ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML").'">'.$pres.'</a>';
			}
	
			if ($a_user_image)
			{
				$img = ilObjUser::_getPersonalPicturePath($row->usr_id, "xxsmall");
				$pres = '<img border="0" src="'.$img.'" alt="'.$lng->txt("icon").
					" ".$lng->txt("user_picture").'" /> '.$pres;
			}

			$names[$row->usr_id] = $pres; 
		}

		foreach($a_user_id as $id)
		{
			if (!$names[$id])
				$names[$id] = "unknown";
		}

		return $return_as_array ? $names : $names[$a_user_id[0]];
	}
	
}
?>
