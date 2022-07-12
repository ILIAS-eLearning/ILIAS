<?php declare(strict_types=0);

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
 
/**
 * Class ilObjCourseAccess
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilObjCourseAccess extends ilObjectAccess implements ilConditionHandling
{
    protected static bool $using_code = false;
    protected static ?ilBookingReservationDBRepository $booking_repo = null;

    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacSystem;

    public function __construct()
    {
        global $DIC;

        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->rbacSystem = $DIC->rbac()->system();
    }

    /**
     * Get operators
     * @return string[]
     */
    public static function getConditionOperators() : array
    {
        return array(
            ilConditionHandler::OPERATOR_PASSED
        );
    }

    /**
     * @inheritDoc
     */
    public static function checkCondition(
        int $a_trigger_obj_id,
        string $a_operator,
        string $a_value,
        int $a_usr_id
    ) : bool {
        switch ($a_operator) {
            case ilConditionHandler::OPERATOR_PASSED:
                return ilCourseParticipants::_hasPassed($a_trigger_obj_id, $a_usr_id);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function _checkAccess(string $cmd, string $permission, int $ref_id, int $obj_id, ?int $user_id = null) : bool
    {
        if (is_null($user_id)) {
            $user_id = $this->user->getId();
        }
        if ($this->user->getId() === $user_id) {
            $participants = ilCourseParticipant::_getInstanceByObjId($obj_id, $user_id);
        } else {
            $participants = ilCourseParticipants::_getInstanceByObjId($obj_id);
        }

        switch ($cmd) {
            case "view":
                if ($participants->isBlocked($user_id) && $participants->isAssigned($user_id)) {
                    $this->access->addInfoItem(
                        ilAccessInfo::IL_NO_OBJECT_ACCESS,
                        $this->lng->txt("crs_status_blocked")
                    );
                    return false;
                }
                break;

            case 'leave':

                // Regular member
                if ($permission == 'leave') {
                    $limit = null;
                    if (!ilObjCourse::mayLeave($obj_id, $user_id, $limit)) {
                        $this->access->addInfoItem(
                            ilAccessInfo::IL_STATUS_INFO,
                            sprintf(
                                $this->lng->txt("crs_cancellation_end_rbac_info"),
                                ilDatePresentation::formatDate($limit)
                            )
                        );
                        return false;
                    }

                    if (!$participants->isAssigned($user_id)) {
                        return false;
                    }
                }
                // Waiting list
                if ($permission == 'join') {
                    if (!ilCourseWaitingList::_isOnList($user_id, $obj_id)) {
                        return false;
                    }
                    return true;
                }
                break;

            case 'join':

                if (ilCourseWaitingList::_isOnList($user_id, $obj_id)) {
                    return false;
                }
                break;
        }

        switch ($permission) {
            case 'visible':
                $visible = null;
                $active = self::_isActivated($obj_id, $visible);
                $tutor = $this->rbacSystem->checkAccessOfUser($user_id, 'write', $ref_id);
                if (!$active) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt("offline"));
                }
                if (!$tutor && !$active && !$visible) {
                    return false;
                }
                break;

            case 'read':
                $tutor = $this->rbacSystem->checkAccessOfUser($user_id, 'write', $ref_id);
                if ($tutor) {
                    return true;
                }
                $active = self::_isActivated($obj_id);
                if (!$active) {
                    $this->access->addInfoItem(ilAccessInfo::IL_NO_OBJECT_ACCESS, $this->lng->txt("offline"));
                    return false;
                }
                if ($participants->isBlocked($user_id) && $participants->isAssigned($user_id)) {
                    $this->access->addInfoItem(
                        ilAccessInfo::IL_NO_OBJECT_ACCESS,
                        $this->lng->txt("crs_status_blocked")
                    );
                    return false;
                }
                break;

            case 'join':
                if (!self::_registrationEnabled($obj_id)) {
                    return false;
                }

                if ($participants->isAssigned($user_id)) {
                    return false;
                }
                break;

            case 'leave':
                return ilObjCourse::mayLeave($obj_id, $user_id);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function _getCommands() : array
    {
        $commands = array();
        $commands[] = array("permission" => "crs_linked", "cmd" => "", "lang_var" => "view", "default" => true);

        $commands[] = array("permission" => "join", "cmd" => "join", "lang_var" => "join");

        // on waiting list
        $commands[] = array('permission' => "join", "cmd" => "leave", "lang_var" => "leave_waiting_list");

        // regualar users
        $commands[] = array('permission' => "leave", "cmd" => "leave", "lang_var" => "crs_unsubscribe");

        if (ilDAVActivationChecker::_isActive()) {
            $webdav_obj = new ilObjWebDAV();
            $commands[] = $webdav_obj->retrieveWebDAVCommandArrayForActionMenu();
        }

        $commands[] = array("permission" => "write",
                            "cmd" => "enableAdministrationPanel",
                            "lang_var" => "edit_content"
        );
        $commands[] = array("permission" => "write", "cmd" => "edit", "lang_var" => "settings");
        return $commands;
    }

    /**
     * @inheritDoc
     */
    public static function _checkGoto(string $target) : bool
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];

        $t_arr = explode("_", $target);

        // registration codes
        if (isset($t_arr[2]) && substr($t_arr[2], 0, 5) == 'rcode' && $ilUser->getId() != ANONYMOUS_USER_ID) {
            self::$using_code = true;
            return true;
        }

        if ($t_arr[0] != "crs" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        // checking for read results in endless loop, if read is given
        // but visible is not given (-> see bug 5323)
        if ($ilAccess->checkAccess("read", "", $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", $t_arr[1])) {
            //if ($ilAccess->checkAccess("visible", "", $t_arr[1]))
            return true;
        }
        return false;
    }

    public static function _lookupViewMode(int $a_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT view_mode FROM crs_settings WHERE obj_id = " . $ilDB->quote($a_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->view_mode;
        }
        return ilContainer::VIEW_DEFAULT;
    }

    public static function _isActivated(int $a_obj_id, ?bool &$a_visible_flag = null, bool $a_mind_member_view = true) : bool
    {
        // #7669
        if ($a_mind_member_view) {
            if (ilMemberViewSettings::getInstance()->isActive()) {
                $a_visible_flag = true;
                return true;
            }
        }
        $ref_id = ilObject::_getAllReferences($a_obj_id);
        $ref_id = array_pop($ref_id);
        $a_visible_flag = true;

        $item = ilObjectActivation::getItem($ref_id);
        switch ($item['timing_type']) {
            case ilObjectActivation::TIMINGS_ACTIVATION:
                if (time() < $item['timing_start'] || time() > $item['timing_end']) {
                    $a_visible_flag = $item['visible'];
                    return false;
                }
            // fallthrough

            // no break
            default:
                return true;
        }
    }

    public static function _registrationEnabled(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $type = null;

        $query = "SELECT * FROM crs_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";

        $reg_start = $reg_end = 0;
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $type = $row->sub_limitation_type;
            $reg_start = $row->sub_start;
            $reg_end = $row->sub_end;
        }

        switch ($type) {
            case ilCourseConstants::IL_CRS_SUBSCRIPTION_UNLIMITED:
                return true;

            case ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED:
                return false;

            case ilCourseConstants::IL_CRS_SUBSCRIPTION_LIMITED:
                if (time() > $reg_start && time() < $reg_end) {
                    return true;
                }
            // no break
            default:
                return false;
        }
    }

    public static function lookupRegistrationInfo(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        $lng = $DIC->language();

        $query = 'SELECT sub_limitation_type, sub_start, sub_end, sub_mem_limit, sub_max_members FROM crs_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        $info = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $info['reg_info_start'] = new ilDateTime($row->sub_start, IL_CAL_UNIX);
            $info['reg_info_end'] = new ilDateTime($row->sub_end, IL_CAL_UNIX);
            $info['reg_info_type'] = (int) $row->sub_limitation_type;
            $info['reg_info_max_members'] = (int) $row->sub_max_members;
            $info['reg_info_mem_limit'] = (int) $row->sub_mem_limit;
        }

        $registration_possible = true;

        // Limited registration
        if ($info['reg_info_type'] == ilCourseConstants::SUBSCRIPTION_LIMITED) {
            $dt = new ilDateTime(time(), IL_CAL_UNIX);
            if (ilDateTime::_before($dt, $info['reg_info_start'])) {
                $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg_start');
                $info['reg_info_list_prop']['value'] = ilDatePresentation::formatDate($info['reg_info_start']);
            } elseif (ilDateTime::_before($dt, $info['reg_info_end'])) {
                $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg_end');
                $info['reg_info_list_prop']['value'] = ilDatePresentation::formatDate($info['reg_info_end']);
            } else {
                $registration_possible = false;
                $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg_period');
                $info['reg_info_list_prop']['value'] = $lng->txt('crs_list_reg_noreg');
            }
        } elseif ($info['reg_info_type'] == ilCourseConstants::SUBSCRIPTION_UNLIMITED) {
            $registration_possible = true;
        } else {
            $registration_possible = false;
            $info['reg_info_list_prop']['property'] = $lng->txt('crs_list_reg');
            $info['reg_info_list_prop']['value'] = $lng->txt('crs_list_reg_noreg');
        }

        if ($info['reg_info_mem_limit'] && $info['reg_info_max_members'] && $registration_possible) {
            // Check for free places
            $part = ilCourseParticipant::_getInstanceByObjId($a_obj_id, $ilUser->getId());

            $info['reg_info_list_size'] = ilCourseWaitingList::lookupListSize($a_obj_id);
            if ($info['reg_info_list_size']) {
                $info['reg_info_free_places'] = 0;
            } else {
                $info['reg_info_free_places'] = max(0, $info['reg_info_max_members'] - $part->getNumberOfMembers());
            }

            if ($info['reg_info_free_places']) {
                $info['reg_info_list_prop_limit']['property'] = $lng->txt('crs_list_reg_limit_places');
                $info['reg_info_list_prop_limit']['value'] = $info['reg_info_free_places'];
            } else {
                $info['reg_info_list_prop_limit']['property'] = '';
                $info['reg_info_list_prop_limit']['value'] = $lng->txt('crs_list_reg_limit_full');
            }
        }
        return $info;
    }

    public static function _isOffline(int $obj_id) : bool
    {
        $dummy = null;
        return !self::_isActivated($obj_id, $dummy, false);
    }

    /**
     * Preload data
     */
    public static function _preloadData(array $obj_ids, array $ref_ids) : void
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];

        $lng->loadLanguageModule("crs");

        ilCourseWaitingList::_preloadOnListInfo([$ilUser->getId()], $obj_ids);

        $repository = new ilUserCertificateRepository();
        $coursePreload = new ilCertificateObjectsForUserPreloader($repository);
        $coursePreload->preLoad($ilUser->getId(), $obj_ids);

        $f = new ilBookingReservationDBRepositoryFactory();
        self::$booking_repo = $f->getRepoWithContextObjCache($obj_ids);
    }

    public static function getBookingInfoRepo() : ?ilBookingReservationDBRepository
    {
        return self::$booking_repo;
    }

    public static function _usingRegistrationCode() : bool
    {
        return self::$using_code;
    }

    public static function lookupPeriodInfo(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();

        $start = $end = null;
        $query = 'SELECT period_start, period_end, period_time_indication FROM crs_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, ilDBConstants::T_INTEGER);

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
            if (!$row->period_time_indication) {
                $start = ($row->period_start
                    ? new \ilDate($row->period_start, IL_CAL_DATETIME)
                    : null);
                $end = ($row->period_end
                    ? new \ilDate($row->period_end, IL_CAL_DATETIME)
                    : null);
            } else {
                $start = ($row->period_start
                    ? new \ilDateTime($row->period_start, IL_CAL_DATETIME, \ilTimeZone::UTC)
                    : null);
                $end = ($row->period_end
                    ? new \ilDateTime($row->period_end, IL_CAL_DATETIME, \ilTimeZone::UTC)
                    : null);
            }
        }
        if ($start && $end) {
            $lng->loadLanguageModule('crs');

            return
                [
                    'crs_start' => $start,
                    'crs_end' => $end,
                    'property' => $lng->txt('crs_period'),
                    'value' => ilDatePresentation::formatPeriod($start, $end)
                ];
        }
        return [];
    }
}
