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
	
	
	//
	// Personal starting point
	//
	
	/**
	 * Get all valid starting points
	 * 
	 * @return array
	 */
	public static function getPossibleStartingPoints()
	{
		global $ilSetting, $rbacsystem, $lng;
		
		// for all conditions: see ilMainMenuGUI
		
		$all = array();
		
		$all[self::START_PD_OVERVIEW] = 'overview';
		
		if($ilSetting->get('disable_my_offers') == 0 &&
			$ilSetting->get('disable_my_memberships') == 0)
		{
			$all[self::START_PD_SUBSCRIPTION] = 'my_courses_groups';
		}

		if (!$ilSetting->get("disable_bookmarks"))
		{
			$all[self::START_PD_BOOKMARKS] = 'bookmarks';
		}

		if (!$ilSetting->get("disable_notes"))
		{
			$all[self::START_PD_NOTES] = 'notes_and_comments';
		}

		if ($ilSetting->get("block_activated_news"))
		{
			$all[self::START_PD_NEWS] = 'news';
		}

		if(!$ilSetting->get("disable_personal_workspace"))
		{
			$all[self::START_PD_WORKSPACE] = 'personal_workspace';		
		}

		if ($ilSetting->get('user_portfolios'))
		{
			$all[self::START_PD_PORTFOLIO] = 'portfolio';					
		}
		
		$skmg_set = new ilSetting("skmg");
		if ($skmg_set->get("enable_skmg"))
		{
			$all[self::START_PD_SKILLS] = 'skills';					
		}

		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if (ilObjUserTracking::_enabledLearningProgress() && 
			ilObjUserTracking::_hasLearningProgressDesktop())
		{
			$all[self::START_PD_LP] = 'learning_progress';					
		}

		include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
		$settings = ilCalendarSettings::_getInstance();
		if($settings->isEnabled())
		{
			$all[self::START_PD_CALENDAR] = 'calendar';		
		}

		if($rbacsystem->checkAccess('mail_visible', ilMailGlobalServices::getMailObjectRefId()))
		{	
			$all[self::START_PD_MAIL] = 'mail';	
		}
				
		if(!$ilSetting->get('disable_contacts') &&
			($ilSetting->get('disable_contacts_require_mail') ||
			$rbacsystem->checkAccess('mail_visible', ilMailGlobalServices::getMailObjectRefId())))
		{
			$all[self::START_PD_CONTACTS] = 'mail_addressbook';					
		}
		
		$all[self::START_PD_PROFILE] = 'personal_profile';		
		$all[self::START_PD_SETTINGS] = 'personal_settings';		
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
	 * @return boolean 
	 */
	public static function setStartingPoint($a_value)
	{
		global $ilSetting;
		
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
		global $ilSetting;
				
		$valid = array_keys(self::getPossibleStartingPoints());
		$current = $ilSetting->get("usr_starting_point");		
		if(!$current || !in_array($current, $valid))
		{
			self::setStartingPoint(self::START_PD_OVERVIEW);
			$current = self::START_PD_OVERVIEW;
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
		$current = self::getStartingPoint();
		if($current != self::START_REPOSITORY)
		{
			$map = array(
				self::START_PD_OVERVIEW => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSelectedItems',
				self::START_PD_SUBSCRIPTION => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToMemberships',
				self::START_PD_BOOKMARKS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToBookmarks',
				self::START_PD_NOTES => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNotes',
				self::START_PD_NEWS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToNews',
				self::START_PD_WORKSPACE => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace',
				self::START_PD_PORTFOLIO => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToPortfolio',
				self::START_PD_SKILLS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSkills',
				self::START_PD_LP => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToLP',
				self::START_PD_CALENDAR => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToCalendar',
				self::START_PD_MAIL => 'ilias.php?baseClass=ilMailGUI',
				self::START_PD_CONTACTS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts',
				self::START_PD_PROFILE => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile',
				self::START_PD_SETTINGS => 'ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings'			
			);				
			return $map[$current];		
		}
		else
		{
			include_once('./Services/Link/classes/class.ilLink.php');
			return ilLink::_getStaticLink(1,'root',true);
		}
	}
}
?>
