<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\MyStaff\ilMyStaffAccess;
use ILIAS\MyStaff\ilMyStaffCachedAccessDecorator;

/**
 * Class ilUserUtil
 * @author Alexander Killing <killing@leifos.de>
 */
class ilUserUtil
{
    public const START_PD_OVERVIEW = 1;
    public const START_PD_SUBSCRIPTION = 2;
    public const START_PD_NOTES = 4;
    public const START_PD_NEWS = 5;
    public const START_PD_WORKSPACE = 6;
    public const START_PD_PORTFOLIO = 7;
    public const START_PD_SKILLS = 8;
    public const START_PD_LP = 9;
    public const START_PD_CALENDAR = 10;
    public const START_PD_MAIL = 11;
    public const START_PD_CONTACTS = 12;
    public const START_PD_PROFILE = 13;
    public const START_PD_SETTINGS = 14;
    public const START_REPOSITORY = 15;
    public const START_REPOSITORY_OBJ = 16;
    public const START_PD_MYSTAFF = 17;

    /**
     * Default behaviour is:
     * - lastname, firstname if public profile enabled
     * - [loginname] (always)
     * modifications by jposselt at databay . de :
     * if $a_user_id is an array of user ids the method returns an array of
     * "id" => "NamePresentation" pairs.
     * @param int|int[]    $a_user_id
     * @param string|array $a_ctrl_path
     * @return array|false|mixed
     * @throws ilWACException
     */
    public static function getNamePresentation(
        $a_user_id,
        bool $a_user_image = false,
        bool $a_profile_link = false,
        string $a_profile_back_link = "",
        bool $a_force_first_lastname = false,
        bool $a_omit_login = false,
        bool $a_sortable = true,
        bool $a_return_data_array = false,
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
            $d = array(
                "id" => (int) $row->usr_id,
                "title" => "",
                "lastname" => "",
                "firstname" => "",
                "img" => "",
                "link" => ""
            );
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
            if (!isset($names[$id]) || !$names[$id]) {
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

    public static function hasPublicProfile(int $a_user_id): bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $set = $ilDB->query(
            "SELECT value FROM usr_pref " .
            " WHERE usr_id = " . $ilDB->quote($a_user_id, "integer") .
            " and keyword = " . $ilDB->quote("public_profile", "text")
        );
        $rec = $ilDB->fetchAssoc($set);

        return in_array($rec["value"] ?? "", array("y", "g"));
    }


    /**
     * Get link to personal profile
     * Return empty string in case of not public profile
     */
    public static function getProfileLink(int $a_usr_id): string
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
     * @return array<int,string>
     */
    public static function getPossibleStartingPoints(bool $a_force_all = false): array
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

        if ((new ilMyStaffCachedAccessDecorator($DIC, ilMyStaffAccess::getInstance()))->hasCurrentUserAccessToMyStaff()) {
            $all[self::START_PD_MYSTAFF] = 'my_staff';
        }

        if ($a_force_all || !$ilSetting->get("disable_personal_workspace")) {
            $all[self::START_PD_WORKSPACE] = 'mm_personal_and_shared_r';
        }
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
     */
    public static function setStartingPoint(
        int $a_value,
        int $a_ref_id = null,
        array $a_cal_view = []
    ): bool {
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
            if ($a_value == self::START_PD_CALENDAR) {
                foreach ($a_cal_view as $key => $value) {
                    $ilSetting->set($key, $value);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Get current starting point setting
     */
    public static function getStartingPoint(): int
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

    public static function getStartingPointAsUrl(): string
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilUser = $DIC['ilUser'];
        $rbacreview = $DIC['rbacreview'];

        $ref_id = 1;
        $by_default = true;
        $current = 0;

        //configuration by user preference
        #21782
        if (self::hasPersonalStartingPoint() && $ilUser->getPref('usr_starting_point') != null) {
            $current = self::getPersonalStartingPoint();
            if ($current == self::START_REPOSITORY_OBJ) {
                $ref_id = self::getPersonalStartingObject();
            }
        } else {
            if (ilStartingPoint::ROLE_BASED) {
                //getting all roles with starting points and store them in array
                $roles = ilStartingPoint::getRolesWithStartingPoint();

                $roles_ids = array_keys($roles);
                $gr = array();
                foreach ($roles_ids as $role_id) {
                    if ($rbacreview->isAssigned($ilUser->getId(), $role_id)) {
                        $gr[$roles[$role_id]['position']] = array(
                            "point" => $roles[$role_id]['starting_point'],
                            "object" => $roles[$role_id]['starting_object'],
                            "cal_view" => $roles[$role_id]['calendar_view'],
                            "cal_period" => $roles[$role_id]['calendar_period']
                        );
                    }
                }
                if (!empty($gr)) {
                    krsort($gr);	// ak: if we use array_pop (last element) we need to reverse sort, since we want the one with the smallest number
                    $role_point = array_pop($gr);
                    $current = $role_point['point'];
                    $ref_id = $role_point['object'];
                    $cal_view = $role_point['cal_view'];
                    $cal_period = $role_point['cal_period'];
                    $by_default = false;
                }
            }
            if ($by_default) {
                $current = self::getStartingPoint();

                $cal_view = self::getCalendarView();
                $cal_period = self::getCalendarPeriod();
                if ($current == self::START_REPOSITORY_OBJ) {
                    $ref_id = self::getStartingObject();
                }
            }
        }

        $calendar_string = "";
        if (!empty($cal_view) && !empty($cal_period)) {
            $calendar_string = "&cal_view=" . $cal_view . "&cal_agenda_per=" . $cal_period;
        }

        switch ($current) {
            case self::START_REPOSITORY:
                $ref_id = $tree->readRootId();

                // no break
            case self::START_REPOSITORY_OBJ:
                if ($ref_id &&
                    ilObject::_lookupObjId($ref_id) &&
                    !$tree->isDeleted($ref_id)) {
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
                    self::START_PD_CALENDAR => 'ilias.php?baseClass=ilDashboardGUI&cmd=jumpToCalendar' . $calendar_string,
                    self::START_PD_MYSTAFF => 'ilias.php?baseClass=' . ilDashboardGUI::class . '&cmd=' . ilDashboardGUI::CMD_JUMP_TO_MY_STAFF
                );
                return $map[$current];
        }
    }

    /**
     * Get ref id of starting object
     */
    public static function getStartingObject(): int
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return $ilSetting->get("usr_starting_point_ref_id");
    }

    /**
     * Get specific view of calendar starting point
     */
    public static function getCalendarView(): int
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return (int) $ilSetting->get("user_calendar_view");
    }

    /**
     * Get time frame of calendar view
     */
    public static function getCalendarPeriod(): int
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return (int) $ilSetting->get("user_cal_period");
    }

    /**
     * Toggle personal starting point setting
     */
    public static function togglePersonalStartingPoint(bool $a_value): void
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $ilSetting->set("usr_starting_point_personal", (string) $a_value);
    }

    /**
     * Can starting point be personalized?
     */
    public static function hasPersonalStartingPoint(): bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return (bool) $ilSetting->get("usr_starting_point_personal");
    }

    /**
     * Did user set any personal starting point (yet)?
     */
    public static function hasPersonalStartPointPref(): bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        return (bool) $ilUser->getPref("usr_starting_point");
    }

    /**
     * Get current personal starting point
     */
    public static function getPersonalStartingPoint(): int
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
     */
    public static function setPersonalStartingPoint(
        int $a_value,
        int $a_ref_id = null
    ): bool {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];

        if (!$a_value) {
            $ilUser->setPref("usr_starting_point", null);
            $ilUser->setPref("usr_starting_point_ref_id", null);
            return false;
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
     */
    public static function getPersonalStartingObject(): int
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
