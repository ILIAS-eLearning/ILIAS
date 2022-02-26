<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilObjSessionAccess extends ilObjectAccess
{
    protected ilObjUser $user;
    protected static ?array $registrations = null;
    protected static ?array $registered = null;
    protected static ?ilBookingReservationDBRepository $booking_repo = null;

    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
    }

    public static function _getCommands() : array
    {
        $commands = array(
            array("permission" => "read", "cmd" => "infoScreen", "lang_var" => "info_short", "default" => true),
            array("permission" => "read", "cmd" => "register", "lang_var" => "join_session"),
            array("permission" => "read", "cmd" => "unregister", "lang_var" => "event_unregister"),
            array("permission" => "write", "cmd" => "edit", "lang_var" => "settings"),
            array("permission" => "manage_materials", "cmd" => "materials", "lang_var" => "crs_objective_add_mat"),
            array('permission' => 'manage_members', 'cmd' => 'members', 'lang_var' => 'event_edit_members')
        );
        
        return $commands;
    }

    public function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "") : bool
    {
        global $DIC;

        $ilUser = $this->user;
        
        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }
        
        switch ($a_cmd) {
            case 'register':
                
                if (!self::_lookupRegistration($a_obj_id)) {
                    return false;
                }
                if ($ilUser->isAnonymous()) {
                    return false;
                }
                if (self::_lookupRegistered($a_user_id, $a_obj_id)) {
                    return false;
                }
                if (\ilSessionParticipants::_isSubscriber($a_obj_id, $a_user_id)) {
                    return false;
                }
                if (ilSessionWaitingList::_isOnList($a_user_id, $a_obj_id)) {
                    return false;
                }
                break;
                
            case 'unregister':
                if (self::_lookupRegistration($a_obj_id) && $a_user_id != ANONYMOUS_USER_ID) {
                    return self::_lookupRegistered($a_user_id, $a_obj_id);
                }
                return false;
        }
        return true;
    }

    public static function _checkGoto($a_target) : bool
    {
        global $DIC;

        $ilAccess = $DIC->access();
        
        $t_arr = explode("_", $a_target);

        if ($t_arr[0] != "sess" || ((int) $t_arr[1]) <= 0) {
            return false;
        }

        if ($ilAccess->checkAccess("read", "", (int) $t_arr[1]) ||
            $ilAccess->checkAccess("visible", "", (int) $t_arr[1])) {
            return true;
        }
        return false;
    }

    public static function _lookupRegistration(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (!is_null(self::$registrations)) {
            return (bool) self::$registrations[$a_obj_id];
        }
        
        $query = "SELECT registration,obj_id FROM event ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            self::$registrations[$row->obj_id] = (bool) $row->registration;
        }
        return (bool) self::$registrations[$a_obj_id];
    }

    public static function _lookupRegistered(int $a_usr_id, int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $ilUser = $DIC->user();

        if (isset(self::$registered[$a_usr_id])) {
            return (bool) self::$registered[$a_usr_id][$a_obj_id];
        }
        
        $query = "SELECT event_id, registered FROM event_participants WHERE usr_id = " . $ilDB->quote($ilUser->getId(), 'integer');
        $res = $ilDB->query($query);
        self::$registered[$a_usr_id] = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            self::$registered[$a_usr_id][$row->event_id] = (bool) $row->registered;
        }
        return (bool) self::$registered[$a_usr_id][$a_obj_id];
    }

    public static function _preloadData($a_obj_ids, $a_ref_ids) : void
    {
        $f = new ilBookingReservationDBRepositoryFactory();
        self::$booking_repo = $f->getRepoWithContextObjCache($a_obj_ids);
    }

    public static function getBookingInfoRepo() : ?ilBookingReservationDBRepository
    {
        if (self::$booking_repo instanceof ilBookingReservationDBRepository) {
            return self::$booking_repo;
        }
        return null;
    }
}
