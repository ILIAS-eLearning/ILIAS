<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\MyStaff\ilMyStaffAccess;

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
    const START_PD_NOTES = 4;
    const START_PD_NEWS = 5;
    const START_PD_WORKSPACE = 6;
    const START_PD_PORTFOLIO = 7;
    const START_PD_SKILLS = 8;
    const START_PD_LP = 9;
    const START_PD_CALENDAR = 10;
    const START_PD_MAIL = 11;
    const START_PD_CONTACTS = 12;
    const START_PD_PROFILE = 13;
    const START_PD_SETTINGS = 14;
    const START_REPOSITORY = 15;
    const START_REPOSITORY_OBJ = 16;
    const START_PD_MYSTAFF = 17;
    
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
    public static function getNamePresentation(
        $a_user_id,
        $a_user_image = false,
        $a_profile_link = false,
        $a_profile_back_link = "",
        $a_force_first_lastname = false,
        $a_omit_login = false,
        $a_sortable = true,
        $a_return_data_array = false,
        $a_ctrl_path = "ilpublicuserprofilegui"
    ) {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilDB = $DIC['ilDB'];

        if (!is_array($a_ctrl_path)) {
            $a_ctrl_path = array($a_ctrl_path);
        }

        if (!($return_as_array = is_array($a_user_id))) {
            $a_user_id = array($a_user_id);
        }
        
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
        while ($row = $ilDB->fetchObject($userrow)) {
            $pres = '';
            $d = array("id" => (int) $row->usr_id, "title" => "", "lastname" => "", "firstname" => "", "img" => "", "link" => "",
                "public_profile" => "");
            $has_public_profile = in_array($row->public_profile, array("y", "g"));
            if ($a_force_first_lastname || $has_public_profile) {
                $title = "";
                if ($row->public_title == "y" && $row->title) {
                    $title = $row->title . " ";
                }
                $d["title"] = $title;
                if ($a_sortable) {
                    $pres = $row->lastname;
                    if (strlen($row->firstname)) {
                        $pres .= (', ' . $row->firstname . ' ');
                    }
                } else {
                    $pres = $title;
                    if (strlen($row->firstname)) {
                        $pres .= $row->firstname . ' ';
                    }
                    $pres .= ($row->lastname . ' ');
                }
                $d["firstname"] = $row->firstname;
                $d["lastname"] = $row->lastname;
            }
            $d["login"] = $row->login;
            $d["public_profile"] = $has_public_profile;

            
            if (!$a_omit_login) {
                $pres .= "[" . $row->login . "]";
            }

            if ($a_profile_link && $has_public_profile) {
                $ilCtrl->setParameterByClass(end($a_ctrl_path), "user_id", $row->usr_id);
                if ($a_profile_back_link != "") {
                    $ilCtrl->setParameterByClass(
                        end($a_ctrl_path),
                        "back_url",
                        rawurlencode($a_profile_back_link)
                    );
                }
                $link = $ilCtrl->getLinkTargetByClass($a_ctrl_path, "getHTML");
                $pres = '<a href="' . $link . '">' . $pres . '</a>';
                $d["link"] = $link;
            }
    
            if ($a_user_image) {
                $img = ilObjUser::_getPersonalPicturePath($row->usr_id, "xxsmall");
                $pres = '<img class="ilUserXXSmall" src="' . $img . '" alt="' . $lng->txt("icon") .
                    " " . $lng->txt("user_picture") . '" /> ' . $pres;
                $d["img"] = $img;
            }

            $names[$row->usr_id] = $pres;
            $data[$row->usr_id] = $d;
        }

        foreach ($a_user_id as $id) {
            if (!$names[$id]) {
                $names[$id] = $lng->txt('usr_name_undisclosed');
            }
        }

        if ($a_return_data_array) {
            if ($return_as_array) {
                return $data;
            } else {
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
    public static function hasPublicProfile($a_user_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT value FROM usr_pref " .
            " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
            " and keyword = " . $ilDB->quote("public_profile", "text")
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
        $public_profile = ilObjUser::_lookupPref($a_usr_id, 'public_profile');
        if ($public_profile != 'y' and $public_profile != 'g') {
            return '';
        }
        
        $GLOBALS['DIC']['ilCtrl']->setParameterByClass('ilpublicuserprofilegui', 'user', $a_usr_id);
        return $GLOBALS['DIC']['ilCtrl']->getLinkTargetByClass('ilpublicuserprofilegui', 'getHTML');
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
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $lng = $DIC['lng'];
        
        // for all conditions: see ilMainMenuGUI
        
        $all = array();
        
        $all[self::START_PD_OVERVIEW] = 'mm_dashboard';
        
        if ($a_force_all || ($ilSetting->get('disable_my_offers') == 0 &&
            $ilSetting->get('disable_my_memberships') == 0)) {
            $all[self::START_PD_SUBSCRIPTION] = 'my_courses_groups';
        }

        if (ilMyStaffAccess::getInstance()->hasCurrentUserAccessToMyStaff()) {
            $all[self::START_PD_MYSTAFF] = 'my_staff';
        }
    
        if ($a_force_all || !$ilSetting->get("disable_personal_workspace")) {
            $all[self::START_PD_WORKSPACE] = 'mm_personal_and_shared_r';
        }

        include_once('./Services/Calendar/classes/class.ilCalendarSettings.php');
        $settings = ilCalendarSettings::_getInstance();
        if ($a_force_all || $settings->isEnabled()) {
            $all[self::START_PD_CALENDAR] = 'calendar';
        }

        $all[self::START_REPOSITORY] = 'obj_root';
        
        foreach ($all as $idx => $lang) {
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
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $tree = $DIC['tree'];
        
        if ($a_value == self::START_REPOSITORY_OBJ) {
            $a_ref_id = (int) $a_ref_id;
            if (ilObject::_lookupObjId($a_ref_id) &&
                !$tree->isDeleted($a_ref_id)) {
                $ilSetting->set("usr_starting_point", $a_value);
                $ilSetting->set("usr_starting_point_ref_id", $a_ref_id);
                return true;
            }
        }
        $valid = array_keys(self::getPossibleStartingPoints());
        if (in_array($a_value, $valid)) {
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
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];
                
        $valid = array_keys(self::getPossibleStartingPoints());
        $current = $ilSetting->get("usr_starting_point");
        if ($current == self::START_REPOSITORY_OBJ) {
            return $current;
        } elseif (!$current || !in_array($current, $valid)) {
            $current = self::START_PD_OVERVIEW;
                    
            // #10715 - if 1 is disabled overview will display the current default
            if ($ilSetting->get('disable_my_offers') == 0 &&
                $ilSetting->get('disable_my_memberships') == 0 &&
                $ilSetting->get('personal_items_default_view') == 1) {
                $current = self::START_PD_SUBSCRIPTION;
            }
            
            self::setStartingPoint($current);
        }
        if ($ilUser->getId() == ANONYMOUS_USER_ID ||
            !$ilUser->getId()) { // #18531
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
        global $DIC;

        $tree = $DIC['tree'];
        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];
        
        $ref_id = 1;
        $by_default = true;

        //configuration by user preference
        #21782
        if (self::hasPersonalStartingPoint() && $ilUser->getPref('usr_starting_point') != null) {
            $current = self::getPersonalStartingPoint();
            if ($current == self::START_REPOSITORY_OBJ) {
                $ref_id = self::getPersonalStartingObject();
            }
        } else {
            include_once './Services/AccessControl/classes/class.ilStartingPoint.php';

            if (ilStartingPoint::ROLE_BASED) {
                //getting all roles with starting points and store them in array
                $roles = ilStartingPoint::getRolesWithStartingPoint();

                $roles_ids = array_keys($roles);

                $gr = array();
                foreach ($rbacreview->getGlobalRoles() as $role_id) {
                    if ($rbacreview->isAssigned($ilUser->getId(), $role_id)) {
                        if (in_array($role_id, $roles_ids)) {
                            $gr[$roles[$role_id]['position']] = array(
                                "point" => $roles[$role_id]['starting_point'],
                                "object" => $roles[$role_id]['starting_object']
                            );
                        }
                    }
                }
                if (!empty($gr)) {
                    krsort($gr);	// ak: if we use array_pop (last element) we need to reverse sort, since we want the one with the smallest number
                    $role_point = array_pop($gr);
                    $current = $role_point['point'];
                    $ref_id = $role_point['object'];
                    $by_default = false;
                }
            }
            if ($by_default) {
                $current = self::getStartingPoint();

                if ($current == self::START_REPOSITORY_OBJ) {
                    $ref_id = self::getStartingObject();
                }
            }
        }

        switch ($current) {
            case self::START_REPOSITORY:
                $ref_id = $tree->readRootId();

                // no break
            case self::START_REPOSITORY_OBJ:
                if ($ref_id &&
                    ilObject::_lookupObjId($ref_id) &&
                    !$tree->isDeleted($ref_id)) {
                    include_once('./Services/Link/classes/class.ilLink.php');
                    return ilLink::_getStaticLink($ref_id, '', true);
                }
                // invalid starting object, overview is fallback
                $current = self::START_PD_OVERVIEW;
                // fallthrough

                // no break
            default:
                $map = array(
                    self::START_PD_OVERVIEW => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToSelectedItems',
                    self::START_PD_SUBSCRIPTION => 'ilias.php?baseClass=ilMembershipOverviewGUI',
                    self::START_PD_WORKSPACE => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace',
                    self::START_PD_CALENDAR => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToCalendar',
                    self::START_PD_MYSTAFF => 'ilias.php?baseClass=' . ilDashboardGUI::class . '&cmd=' . ilDashboardGUI::CMD_JUMP_TO_MY_STAFF
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
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return $ilSetting->get("usr_starting_point_ref_id");
    }
    
    /**
     * Toggle personal starting point setting
     *
     * @param bool $a_value
     */
    public static function togglePersonalStartingPoint($a_value)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $ilSetting->set("usr_starting_point_personal", (bool) $a_value);
    }
    
    /**
     * Can starting point be personalized?
     *
     * @return bool
     */
    public static function hasPersonalStartingPoint()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return $ilSetting->get("usr_starting_point_personal");
    }
    
    /**
     * Did user set any personal starting point (yet)?
     *
     * @return bool
     */
    public static function hasPersonalStartPointPref()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        return (bool) $ilUser->getPref("usr_starting_point");
    }
        
    /**
     * Get current personal starting point
     *
     * @return int
     */
    public static function getPersonalStartingPoint()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
                                
        $valid = array_keys(self::getPossibleStartingPoints());
        $current = $ilUser->getPref("usr_starting_point");
        if ($current == self::START_REPOSITORY_OBJ) {
            return $current;
        } elseif (!$current || !in_array($current, $valid)) {
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
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        
        if (!$a_value) {
            $ilUser->setPref("usr_starting_point", null);
            $ilUser->setPref("usr_starting_point_ref_id", null);
            return;
        }
        
        if ($a_value == self::START_REPOSITORY_OBJ) {
            $a_ref_id = (int) $a_ref_id;
            if (ilObject::_lookupObjId($a_ref_id) &&
                !$tree->isDeleted($a_ref_id)) {
                $ilUser->setPref("usr_starting_point", $a_value);
                $ilUser->setPref("usr_starting_point_ref_id", $a_ref_id);
                return true;
            }
        }
        $valid = array_keys(self::getPossibleStartingPoints());
        if (in_array($a_value, $valid)) {
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
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $ref_id = $ilUser->getPref("usr_starting_point_ref_id");
        if (!$ref_id) {
            $ref_id = self::getStartingObject();
        }
        return $ref_id;
    }
}
