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
	const START_PD_OVERVIEW = 1;
	const START_PD_SUBSCRIPTION = 2;
	const START_PD_BOOKMARKS = 3;
	const START_PD_NOTES = 4;
	const START_PD_NEWS = 5;
	const START_PD_WORKSPACE = 6;
	const START_PD_PORTFOLIO = 7;
	const START_PD_SKILLS = 8;
	const START_PD_LP = 9;
	const START_PD_CALENDAR = 10;
	const START_PD_MAIL = 11;
	const START_PD_CONTACTS = 12;
	const START_PD_PROFILE= 13;
	const START_PD_SETTINGS = 14;
	const START_REPOSITORY= 15;
	const START_REPOSITORY_OBJ= 16;
	
	/**
	 * Default behaviour is:
	 * - lastname, firstname if public profile enabled
	 * - [loginname] (always)
	 * modifications by jposselt at databay . de :
	 * if $a_user_id is an array of user ids the method returns an array of
	 * "id" => "NamePresentation" pairs.
	 * 
	 * ...
	 * @param boolean sortable should be used in table presentions. output is "Doe, John" title is ommited
	 */
	static function getNamePresentation($a_user_id, 
		$a_user_image = false, $a_profile_link = false, $a_profile_back_link = "",
		$a_force_first_lastname = false, $a_omit_login = false, $a_sortable = true, $a_return_data_array = false)
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

		$data = array();
		while ($row = $ilDB->fetchObject($userrow))
		{
			$d = array("id" => $row->usr_id, "title" => "", "lastname" => "", "firstname" => "", "img" => "", "link" => "",
				"public_profile" => "");
			$has_public_profile = in_array($row->public_profile, array("y", "g"));
			if ($a_force_first_lastname || $has_public_profile)
			{
				$title = "";
				if($row->public_title == "y" && $row->title)
				{
					$title = $row->title . " ";
				}
				$d["title"] = $title;
				if($a_sortable)
				{
					$pres = $row->lastname;
					if(strlen($row->firstname))
					{
						$pres .= (', '.$row->firstname.' ');
					}
				}
				else
				{
					$pres = $title;
					if(strlen($row->firstname))
					{
						$pres .= $row->firstname.' ';
					}
					$pres .= ($row->lastname.' ');
				}
				$d["firstname"] = $row->firstname;
				$d["lastname"] = $row->lastname;
			}
			$d["login"] = $row->login;
			$d["public_profile"] = $has_public_profile;

			
			if(!$a_omit_login)
			{
				$pres.= "[".$row->login."]";
			}

			if ($a_profile_link && $has_public_profile)
			{
				$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $row->usr_id);
				if ($a_profile_back_link != "")
				{
					$ilCtrl->setParameterByClass("ilpublicuserprofilegui", "back_url",
						rawurlencode($a_profile_back_link));
				}
				$link = $ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML");
				$pres = '<a href="'.$link.'">'.$pres.'</a>';
				$d["link"] = $link;
			}
	
			if ($a_user_image)
			{
				$img = ilObjUser::_getPersonalPicturePath($row->usr_id, "xxsmall");
				$pres = '<img border="0" src="'.$img.'" alt="'.$lng->txt("icon").
					" ".$lng->txt("user_picture").'" /> '.$pres;
				$d["img"] = $img;
			}

			$names[$row->usr_id] = $pres;
			$data[$row->usr_id] = $d;
		}

		foreach($a_user_id as $id)
		{
			if (!$names[$id])
				$names[$id] = "unknown";
		}

		if ($a_return_data_array)
		{
			if ($return_as_array)
			{
				return $data;
			}
			else
			{
				return current($data);
			}
		}
		return $return_as_array ? $names : $names[$a_user_id[0]];
	}

	/**
	 * Has public profile
	 *
	 * @param
	 * @return
	 */
	static function hasPublicProfile($a_user_id)
	{
		global $ilDB;

		$set = $ilDB->query("SELECT value FROM usr_pref ".
			" WHERE usr_id = ".$ilDB->quote($a_user_id, "integer").
			" and keyword = ".$ilDB->quote("public_profile", "text")
			);
		$rec = $ilDB->fetchAssoc($set);

		return in_array($rec["value"], array("y", "g"));
	}


	/**
	 * Get link to personal profile 
	 * Return empty string in case of not public profile
	 * @param type $a_usr_id
	 * @return string
	 */
	public static function getProfileLink($a_usr_id)
	{
		$public_profile = ilObjUser::_lookupPref($a_usr_id,'public_profile');
		if($public_profile != 'y' and $public_profile != 'g')
		{
			return '';
		}
		
		$GLOBALS['ilCtrl']->setParameterByClass('ilpublicuserprofilegui','user',$a_usr_id);
		return $GLOBALS['ilCtrl']->getLinkTargetByClass('ilpublicuserprofilegui','getHTML');
	}
	
	
	//
	// Personal starting point
	//
	
	/**
	 * Get all valid starting points
	 * 
	 * @return array
	 */
	public static function getPossibleStartingPoints($a_force_all = false)
	{
		global $ilSetting, $lng;
		
		// for all conditions: see ilMainMenuGUI
		
		$all = array();
		
		$all[self::START_PD_OVERVIEW] = 'overview';
		
		if($a_force_all || ($ilSetting->get('disable_my_offers') == 0 &&
			$ilSetting->get('disable_my_memberships') == 0))
		{
			$all[self::START_PD_SUBSCRIPTION] = 'my_courses_groups';
		}
	
		if($a_force_all || !$ilSetting->get("disable_personal_workspace"))
		{
			$all[self::START_PD_WORKSPACE] = 'personal_workspace';		
		}

		include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
		$settings = ilCalendarSettings::_getInstance();
		if($a_force_all || $settings->isEnabled())
		{
			$all[self::START_PD_CALENDAR] = 'calendar';		
		}

		$all[self::START_REPOSITORY] = 'repository';		
		
		foreach($all as $idx => $lang)
		{
			$all[$idx] = $lng->txt($lang);
		}
		
		return $all;
	}
	
	/**
	 * Set starting point setting
	 * 
	 * @param int $a_value
	 * @param int $a_ref_id
	 * @return boolean 
	 */
	public static function setStartingPoint($a_value, $a_ref_id = null)
	{
		global $ilSetting, $tree;
		
		if($a_value == self::START_REPOSITORY_OBJ)
		{
			$a_ref_id = (int)$a_ref_id;
			if(ilObject::_lookupObjId($a_ref_id) && 
				!$tree->isDeleted($a_ref_id))
			{
				$ilSetting->set("usr_starting_point", $a_value);
				$ilSetting->set("usr_starting_point_ref_id", $a_ref_id);
				return true;
			}
		}
		$valid = array_keys(self::getPossibleStartingPoints());
		if(in_array($a_value, $valid))
		{							
			$ilSetting->set("usr_starting_point", $a_value);
			return true;
		}	
		return false;
	}	
	
	/**
	 * Get current starting point setting
	 * 
	 * @return int 
	 */
	public static function getStartingPoint()
	{
		global $ilSetting, $ilUser;
				
		$valid = array_keys(self::getPossibleStartingPoints());		
		$current = $ilSetting->get("usr_starting_point");	
		if($current == self::START_REPOSITORY_OBJ)
		{
			return $current;
		}
		else if(!$current || !in_array($current, $valid))
		{
			$current = self::START_PD_OVERVIEW;
					
			// #10715 - if 1 is disabled overview will display the current default
			if($ilSetting->get('disable_my_offers') == 0 &&
				$ilSetting->get('disable_my_memberships') == 0 &&
				$ilSetting->get('personal_items_default_view') == 1)
			{
				$current = self::START_PD_SUBSCRIPTION;
			}						
			
			self::setStartingPoint($current);
		}
		if($ilUser->getId() == ANONYMOUS_USER_ID ||
			!$ilUser->getId()) // #18531
		{
			$current = self::START_REPOSITORY;
		}
		return $current;
	}	
	
	/**
	 * Get current starting point setting as URL
	 * 
	 * @return string
	 */
	public static function getStartingPointAsUrl()
	{	
		global $tree, $ilUser;
		
		$ref_id = 1;
		if(self::hasPersonalStartingPoint())
		{
			$current = self::getPersonalStartingPoint();	
			if($current == self::START_REPOSITORY_OBJ)
			{
				$ref_id = self::getPersonalStartingObject();
			}
		}
		else
		{
			$current = self::getStartingPoint();
			if($current == self::START_REPOSITORY_OBJ)
			{
				$ref_id = self::getStartingObject();
			}
		}
		switch($current)
		{			
			case self::START_REPOSITORY:
			case self::START_REPOSITORY_OBJ:
				if($ref_id &&
					ilObject::_lookupObjId($ref_id) && 
					!$tree->isDeleted($ref_id))
				{
					include_once('./Services/Link/classes/class.ilLink.php');
					return ilLink::_getStaticLink($ref_id,'',true);
				}
				// invalid starting object, overview is fallback
				$current = self::START_PD_OVERVIEW;
				// fallthrough
			
			default:
				$map = array(
					self::START_PD_OVERVIEW => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems',
					self::START_PD_SUBSCRIPTION => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships',
					// self::START_PD_BOOKMARKS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBookmarks',
					// self::START_PD_NOTES => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNotes',
					// self::START_PD_NEWS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNews',
					self::START_PD_WORKSPACE => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace',
					// self::START_PD_PORTFOLIO => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToPortfolio',
					// self::START_PD_SKILLS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSkills',
					/// self::START_PD_LP => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToLP',
					self::START_PD_CALENDAR => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToCalendar',
					// self::START_PD_MAIL => 'ilias.php?baseClass=ilMailGUI',
					// self::START_PD_CONTACTS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts',
					// self::START_PD_PROFILE => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile',
					// self::START_PD_SETTINGS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings'			
				);				
				return $map[$current];		
		}		
	}
	
	/**
	 * Get ref id of starting object
	 * 
	 * @return int
	 */
	public static function getStartingObject()
	{
		global $ilSetting;
		
		return $ilSetting->get("usr_starting_point_ref_id");
	}
	
	/**
	 * Toggle personal starting point setting
	 * 
	 * @param bool $a_value
	 */
	public static function togglePersonalStartingPoint($a_value)
	{
		global $ilSetting;
		
		$ilSetting->set("usr_starting_point_personal", (bool)$a_value);		
	}	
	
	/**
	 * Can starting point be personalized?
	 * 
	 * @return bool 
	 */
	public static function hasPersonalStartingPoint()
	{
		global $ilSetting;
		
		return $ilSetting->get("usr_starting_point_personal");
	}
	
	/**
	 * Did user set any personal starting point (yet)?
	 * 
	 * @return bool
	 */
	public static function hasPersonalStartPointPref()
	{
		global $ilUser;
		
		return (bool)$ilUser->getPref("usr_starting_point");	
	}	
		
	/**
	 * Get current personal starting point 
	 * 
	 * @return int 
	 */
	public static function getPersonalStartingPoint()
	{
		global $ilUser;
								
		$valid = array_keys(self::getPossibleStartingPoints());
		$current = $ilUser->getPref("usr_starting_point");	
		if($current == self::START_REPOSITORY_OBJ)
		{
			return $current;
		}		
		else if(!$current || !in_array($current, $valid))
		{
			return self::getStartingPoint();
		}
		return $current;
	}
	
	/**
	 * Set personal starting point setting
	 * 
	 * @param int $a_value
	 * @param int $a_ref_id
	 * @return boolean 
	 */
	public static function setPersonalStartingPoint($a_value, $a_ref_id = null)
	{
		global $ilUser, $tree;
		
		if(!$a_value)
		{
			$ilUser->setPref("usr_starting_point", null);
			$ilUser->setPref("usr_starting_point_ref_id", null);
			return;
		}
		
		if($a_value == self::START_REPOSITORY_OBJ)
		{
			$a_ref_id = (int)$a_ref_id;
			if(ilObject::_lookupObjId($a_ref_id) && 
				!$tree->isDeleted($a_ref_id))
			{
				$ilUser->setPref("usr_starting_point", $a_value);
				$ilUser->setPref("usr_starting_point_ref_id", $a_ref_id);
				return true;
			}
		}
		$valid = array_keys(self::getPossibleStartingPoints());
		if(in_array($a_value, $valid))
		{							
			$ilUser->setPref("usr_starting_point", $a_value);
			return true;
		}	
		return false;
	}	
	
	/**
	 * Get ref id of personal starting object
	 * 
	 * @return int
	 */
	public static function getPersonalStartingObject()
	{
		global $ilUser;
		
		$ref_id = $ilUser->getPref("usr_starting_point_ref_id");
		if(!$ref_id)
		{
			$ref_id = self::getStartingObject();
		}
		return $ref_id;
	}
}
?>
