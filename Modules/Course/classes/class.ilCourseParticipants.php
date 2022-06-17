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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilCourseParticipants extends ilParticipants
{
    protected const COMPONENT_NAME = 'Modules/Course';

    protected static array $instances = [];

    /**
     * @todo get rid of these pseude constants
     */
    public function __construct(int $a_obj_id)
    {
        // ref based constructor
        $refs = ilObject::_getAllReferences($a_obj_id);
        parent::__construct(self::COMPONENT_NAME, array_pop($refs));
    }

    public static function _getInstanceByObjId(int $a_obj_id) : ilCourseParticipants
    {
        if (isset(self::$instances[$a_obj_id]) && self::$instances[$a_obj_id]) {
            return self::$instances[$a_obj_id];
        }
        return self::$instances[$a_obj_id] = new ilCourseParticipants($a_obj_id);
    }

    public function add(int $a_usr_id, int $a_role) : bool
    {
        if (parent::add($a_usr_id, $a_role)) {
            $this->addRecommendation($a_usr_id);
            return true;
        }
        return false;
    }

    public static function getMemberRoles(int $a_ref_id) : array
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $lrol = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);
        $roles = array();
        foreach ($lrol as $role) {
            $title = ilObject::_lookupTitle($role);
            switch (substr($title, 0, 8)) {
                case 'il_crs_a':
                case 'il_crs_t':
                case 'il_crs_m':
                    continue 2;

                default:
                    $roles[$role] = $role;
            }
        }
        return $roles;
    }

    public function addSubscriber(int $a_usr_id) : void
    {
        parent::addSubscriber($a_usr_id);
        $this->eventHandler->raise(
            "Modules/Course",
            'addSubscriber',
            array(
                'obj_id' => $this->getObjId(),
                'usr_id' => $a_usr_id
            )
        );
    }

    public function updatePassed(
        int $a_usr_id,
        bool $a_passed,
        bool $a_manual = false,
        bool $a_no_origin = false
    ) : void {
        $this->participants_status[$a_usr_id]['passed'] = $a_passed;
        self::_updatePassed($this->obj_id, $a_usr_id, $a_passed, $a_manual, $a_no_origin);
    }

    public static function _updatePassed(
        int $a_obj_id,
        int $a_usr_id,
        bool $a_passed,
        bool $a_manual = false,
        bool $a_no_origin = false
    ) : void {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $ilAppEventHandler = $DIC->event();

        // #11600
        $origin = -1;
        if ($a_manual) {
            $origin = $ilUser->getId();
        }

        $query = "SELECT passed FROM obj_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);
        $update_query = '';
        if ($res->numRows()) {
            // #9284 - only needs updating when status has changed
            $old = $ilDB->fetchAssoc($res);
            if ((int) $old["passed"] !== (int) $a_passed) {
                $update_query = "UPDATE obj_members SET " .
                    "passed = " . $ilDB->quote($a_passed, 'integer') . ", " .
                    "origin = " . $ilDB->quote($origin, 'integer') . ", " .
                    "origin_ts = " . $ilDB->quote(time(), 'integer') . " " .
                    "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
                    "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer');
            }
        } else {
            // when member is added we should not set any date
            // see ilObjCourse::checkLPStatusSync()
            if ($a_no_origin && !$a_passed) {
                $origin = 0;
                $origin_ts = 0;
            } else {
                $origin_ts = time();
            }

            $update_query = "INSERT INTO obj_members (passed,obj_id,usr_id,notification,blocked,origin,origin_ts) " .
                "VALUES ( " .
                $ilDB->quote((int) $a_passed, 'integer') . ", " .
                $ilDB->quote($a_obj_id, 'integer') . ", " .
                $ilDB->quote($a_usr_id, 'integer') . ", " .
                $ilDB->quote(0, 'integer') . ", " .
                $ilDB->quote(0, 'integer') . ", " .
                $ilDB->quote($origin, 'integer') . ", " .
                $ilDB->quote($origin_ts, 'integer') . ")";
        }
        if (strlen($update_query)) {
            $ilDB->manipulate($update_query);
            if ($a_passed) {
                $ilAppEventHandler->raise('Modules/Course', 'participantHasPassedCourse', array(
                    'obj_id' => $a_obj_id,
                    'usr_id' => $a_usr_id,
                ));
            }
        }
    }

    public function getPassedInfo(int $a_usr_id) : ?array
    {
        $sql = "SELECT origin, origin_ts" .
            " FROM obj_members" .
            " WHERE obj_id = " . $this->ilDB->quote($this->obj_id, "integer") .
            " AND usr_id = " . $this->ilDB->quote($a_usr_id, "integer");
        $set = $this->ilDB->query($sql);
        $row = $this->ilDB->fetchAssoc($set);
        if ($row && $row["origin"]) {
            return [
                "user_id" => (int) $row["origin"],
                "timestamp" => new ilDateTime((int) $row["origin_ts"], IL_CAL_UNIX)
            ];
        }
        return null;
    }

    public function sendNotification(int $a_type, int $a_usr_id, bool $a_force_sending_mail = false) : void
    {
        $mail = new ilCourseMembershipMailNotification();
        $mail->forceSendingMail($a_force_sending_mail);

        switch ($a_type) {
            case ilCourseMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_DISMISS_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_DISMISS_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_BLOCKED_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_BLOCKED_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_UNBLOCKED_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_UNBLOCKED_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_STATUS_CHANGED:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_STATUS_CHANGED);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER:
                $mail->setType(ilCourseMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_WAITING_LIST_MEMBER:
                $wl = new ilCourseWaitingList($this->obj_id);
                $pos = $wl->getPosition($a_usr_id);

                $mail->setType(ilCourseMembershipMailNotification::TYPE_WAITING_LIST_MEMBER);
                $mail->setRefId($this->ref_id);
                $mail->setRecipients(array($a_usr_id));
                $mail->setAdditionalInformation(array('position' => $pos));
                $mail->send();
                break;

            case ilCourseMembershipMailNotification::TYPE_NOTIFICATION_ADMINS_REGISTRATION_REQUEST:
                $this->sendSubscriptionRequestToAdmins($a_usr_id);
                break;

            case ilCourseMembershipMailNotification::TYPE_NOTIFICATION_ADMINS:
                $this->sendNotificationToAdmins($a_usr_id);
                break;
        }
    }

    public function sendUnsubscribeNotificationToAdmins(int $a_usr_id) : void
    {
        $mail = new ilCourseMembershipMailNotification();
        $mail->setType(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE);
        $mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
        $mail->setRefId($this->ref_id);
        $mail->setRecipients($this->getNotificationRecipients());
        $mail->send();
    }

    public function sendSubscriptionRequestToAdmins(int $a_usr_id) : void
    {
        $mail = new ilCourseMembershipMailNotification();
        $mail->setType(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST);
        $mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
        $mail->setRefId($this->ref_id);
        $mail->setRecipients($this->getNotificationRecipients());
        $mail->send();
    }

    public function sendNotificationToAdmins(int $a_usr_id) : void
    {
        $mail = new ilCourseMembershipMailNotification();
        $mail->setType(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION);
        $mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
        $mail->setRefId($this->ref_id);
        $mail->setRecipients($this->getNotificationRecipients());
        $mail->send();
    }

    public static function getDateTimeOfPassed(int $a_obj_id, int $a_usr_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();
        $sql = "SELECT origin_ts FROM obj_members" .
            " WHERE usr_id = " . $ilDB->quote($a_usr_id, "integer") .
            " AND obj_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND passed = " . $ilDB->quote(1, "integer");
        $res = $ilDB->query($sql);
        $res = $ilDB->fetchAssoc($res);
        if ($res["origin_ts"]) {
            return date("Y-m-d H:i:s", $res["origin_ts"]);
        }
        return '';
    }

    public static function getPassedUsersForObjects(array $a_obj_ids, array $a_usr_ids) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $sql = "SELECT usr_id,obj_id FROM obj_members" .
            " WHERE " . $ilDB->in("usr_id", $a_usr_ids, false, "integer") .
            " AND " . $ilDB->in("obj_id", $a_obj_ids, false, "integer") .
            " AND passed = " . $ilDB->quote(1, "integer");
        $set = $ilDB->query($sql);
        $res = [];
        while ($row = $ilDB->fetchAssoc($set)) {
            $row['usr_id'] = (int) $row['usr_id'];
            $row['obj_id'] = (int) $row['obj_id'];
            $res[] = $row;
        }
        return $res;
    }
}
