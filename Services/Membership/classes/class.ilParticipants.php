<?php

declare(strict_types=1);

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
 * Base class for course and group participants
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesMembership
 */
/**
 * @todo move these constants to ilParticipants
 */
abstract class ilParticipants
{
    public const IL_CRS_ADMIN = 1;
    public const IL_CRS_TUTOR = 3;
    public const IL_CRS_MEMBER = 2;
    public const IL_GRP_ADMIN = 4;
    public const IL_GRP_MEMBER = 5;
    public const IL_SESS_MEMBER = 6;
    public const IL_LSO_ADMIN = 7;
    public const IL_LSO_MEMBER = 8;
    public const IL_ROLE_POSITION_ADMIN = 1;
    public const IL_ROLE_POSITION_TUTOR = 2;
    public const IL_ROLE_POSITION_MEMBER = 3;

    protected string $component = '';
    protected int $ref_id = 0;
    protected int $obj_id = 0;
    protected string $type = '';
    protected array $roles = [];
    protected array $role_data = [];
    protected array $roles_sorted = [];
    protected array $role_assignments = [];
    protected array $participants = [];
    protected array $participants_status = [];
    protected array $members = [];
    protected array $tutors = [];
    protected array $admins = [];
    protected array $subscribers = [];
    protected ilAppEventHandler $eventHandler;
    protected ilRbacReview $rbacReview;
    protected ilRbacAdmin $rbacAdmin;
    protected ilObjectDataCache $objectDataCache;
    protected ilDBInterface $ilDB;
    protected ilLanguage $lng;
    protected ilLogger $logger;
    protected ilErrorHandling $error;
    protected ilRecommendedContentManager $recommended_content_manager;

    /**
     * @param string component definition e.g Modules/Course used for event handler
     * @param int ref_id of container
     */
    public function __construct(string $a_component_name, int $a_ref_id)
    {
        global $DIC;

        $this->ilDB = $DIC->database();
        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->mmbr();
        $this->eventHandler = $DIC->event();
        $this->rbacReview = $DIC->rbac()->review();
        $this->rbacAdmin = $DIC->rbac()->admin();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->error = $DIC['ilErr'];
        $this->component = $a_component_name;
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($a_ref_id);
        $this->type = ilObject::_lookupType($this->obj_id);
        $this->recommended_content_manager = new ilRecommendedContentManager();

        $this->readParticipants();
        $this->readParticipantsStatus();
    }

    public static function getInstance(int $a_ref_id): ilParticipants
    {
        global $DIC;

        $logger = $DIC->logger()->mmbr();

        $obj_id = ilObject::_lookupObjId($a_ref_id);
        $type = ilObject::_lookupType($obj_id);

        switch ($type) {
            case 'crs':
            case 'grp':
            case 'lso':
                return self::getInstanceByObjId($obj_id);
            case 'sess':
                return ilSessionParticipants::getInstance($a_ref_id);
            default:
                $logger()->mem()->logStack();
                $logger()->mem()->warning('Invalid ref_id -> obj_id given: ' . $a_ref_id . ' -> ' . $obj_id);
                throw new InvalidArgumentException('Invalid obj_id given.');
        }
    }

    /**
     * Get instance by obj type
     * @deprecated since version 5.4 use getInstance() (ref_id based)
     * @todo       remove this method in favour of selff::getInstance
     */
    public static function getInstanceByObjId(int $a_obj_id): ilParticipants
    {
        global $DIC;

        $logger = $DIC->logger()->mmbr();

        $type = ilObject::_lookupType($a_obj_id);
        switch ($type) {
            case 'crs':
                return ilCourseParticipants::_getInstanceByObjId($a_obj_id);

            case 'grp':
                return ilGroupParticipants::_getInstanceByObjId($a_obj_id);

            case 'sess':
                return ilSessionParticipants::_getInstanceByObjId($a_obj_id);
            case 'lso':
                return ilLearningSequenceParticipants::_getInstanceByObjId($a_obj_id);
            default:
                $logger()->mmbr()->logStack(ilLogLevel::WARNING);
                $logger()->mmbr()->warning(': Invalid obj_id given: ' . $a_obj_id);
                throw new InvalidArgumentException('Invalid obj id given');
        }
    }

    /**
     * Get component name
     * Used for raising events
     */
    protected function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Check if (current) user has access to the participant list
     */
    public static function hasParticipantListAccess(int $a_obj_id, int $a_usr_id = null): bool
    {
        global $DIC;

        $access = $DIC->access();

        if (!$a_usr_id) {
            $a_usr_id = $DIC->user()->getId();
        }

        // if write access granted => return true
        $refs = ilObject::_getAllReferences($a_obj_id);
        $ref_id = end($refs);

        if ($access->checkAccess('manage_members', '', $ref_id)) {
            return true;
        }
        $part = self::getInstance($ref_id);
        if ($part->isAssigned($a_usr_id)) {
            if ($part->getType() === 'crs') {
                if (!ilObjCourse::lookupShowMembersEnabled($a_obj_id)) {
                    return false;
                }
            }
            if ($part->getType() === 'grp') {
                if (!ilObjGroup::lookupShowMembersEnabled($a_obj_id)) {
                    return false;
                }
            }
            return true;
        }
        // User is not assigned to course/group => no read access
        return false;
    }

    /**
     * Get user membership assignments by type
     */
    public static function getUserMembershipAssignmentsByType(
        array $a_user_ids,
        array $a_type,
        bool $a_only_member_roles
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $j2 = $a2 = '';
        if ($a_only_member_roles) {
            $j2 = "JOIN object_data obd2 ON (ua.rol_id = obd2.obj_id) ";
            $a2 = 'AND obd2.title = ' . $ilDB->concat(
                array(
                        array($ilDB->quote('il_', 'text')),
                        array('obd.type'),
                        array($ilDB->quote('_member_', 'text')),
                        array('obr.ref_id'),
                    ),
                false
            );
        }

        $query = "SELECT DISTINCT obd.obj_id,obr.ref_id,ua.usr_id FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON ua.rol_id = fa.rol_id " .
            "JOIN object_reference obr ON fa.parent = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            $j2 .
            "WHERE " . $ilDB->in("obd.type", $a_type, false, "text") .
            "AND fa.assign = 'y' " .
            'AND ' . $ilDB->in('ua.usr_id', $a_user_ids, false, 'integer') . ' ' .
            $a2;

        $obj_ids = [];
        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchObject($res)) {
            $obj_ids[(int) $row->obj_id][] = (int) $row->usr_id;
        }
        return $obj_ids;
    }

    /**
     * get membership by type
     * Get course or group membership
     * @param int      $a_usr_id usr_id
     * @param string[] $a_type   array of object types
     * @return int[]
     */
    public static function _getMembershipByType(
        int $a_usr_id,
        array $a_type,
        bool $a_only_member_role = false
    ): array {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        if (!is_array($a_type)) {
            $a_type = array($a_type);
        }

        $j2 = '';
        $a2 = '';
        // this will also dismiss local roles!
        if ($a_only_member_role) {
            $j2 = "JOIN object_data obd2 ON (ua.rol_id = obd2.obj_id) ";
            $a2 = 'AND obd2.title = ' . $ilDB->concat(
                array(
                        array($ilDB->quote('il_', 'text')),
                        array('obd.type'),
                        array($ilDB->quote('_member_', 'text')),
                        array('obr.ref_id'),
                    ),
                false
            );
        }

        // #14290 - no role folder anymore
        $query = "SELECT DISTINCT obd.obj_id,obr.ref_id FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON ua.rol_id = fa.rol_id " .
            "JOIN object_reference obr ON fa.parent = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            $j2 .
            "WHERE " . $ilDB->in("obd.type", $a_type, false, "text") .
            "AND fa.assign = 'y' " .
            "AND ua.usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            $a2;
        $res = $ilDB->query($query);
        $ref_ids = [];
        while ($row = $ilDB->fetchObject($res)) {
            $ref_ids[] = (int) $row->obj_id;
        }
        return $ref_ids;
    }

    /**
     * Static function to check if a user is a participant of the container object
     */
    public static function _isParticipant(int $a_ref_id, int $a_usr_id): bool
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $local_roles = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);
        return $rbacreview->isAssignedToAtLeastOneGivenRole($a_usr_id, $local_roles);
    }

    /**
     * Lookup the number of participants (crs admins, tutors, members, grp admins, members)
     */
    public static function lookupNumberOfParticipants(int $a_ref_id): int
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $lroles = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);
        return $rbacreview->getNumberOfAssignedUsers($lroles);
    }

    /**
     * Lookup number of members
     */
    public static function lookupNumberOfMembers(int $a_ref_id): int
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();
        $ilObjDataCache = $DIC['ilObjDataCache'];
        $has_policies = $rbacreview->getLocalPolicies($a_ref_id);
        if (!$has_policies) {
            return 0;
        }
        $lroles = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);
        $memberRoles = array();
        foreach ($lroles as $role_id) {
            $title = $ilObjDataCache->lookupTitle($role_id);
            switch (substr($title, 0, 8)) {
                case 'il_crs_a':
                case 'il_crs_t':
                case 'il_grp_a':
                    break;

                default:
                    $memberRoles[] = $role_id;
                    break;
            }
        }
        return $rbacreview->getNumberOfAssignedUsers($memberRoles);
    }

    /**
     * Check if user is blocked
     */
    public static function _isBlocked(int $a_obj_id, int $a_usr_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND blocked = " . $ilDB->quote(1, 'integer');
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    /**
     * Check if user has passed course
     */
    public static function _hasPassed(int $a_obj_id, int $a_usr_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND passed = '1'";
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    /**
     * Delete all entries
     * Normally called in case of object deletion
     */
    public static function _deleteAllEntries(int $a_obj_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM obj_members " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $query = "DELETE FROM il_subscribers " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $query = 'DELETE FROM crs_waiting_list ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $ilDB->manipulate($query);
    }

    /**
     * Delete user data
     */
    public static function _deleteUser(int $a_usr_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM obj_members WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->manipulate($query);

        $query = "DELETE FROM il_subscribers WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->manipulate($query);

        ilCourseWaitingList::_deleteUser($a_usr_id);
    }

    public static function getDefaultMemberRole(int $a_ref_id): int
    {
        global $DIC;

        $rbacreview = $DIC->rbac()->review();

        $obj_id = ilObject::_lookupObjId($a_ref_id);
        $type = ilObject::_lookupType($obj_id);

        if (!in_array($type, array('crs', 'grp'))) {
            return 0;
        }

        $roles = $rbacreview->getRolesOfRoleFolder($a_ref_id, false);
        foreach ($roles as $role) {
            $title = ilObject::_lookupTitle($role);
            if (strpos($title, ('il_' . $type . '_member')) === 0) {
                return $role;
            }
        }
        return 0;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get admin, tutor which have notification enabled
     * @return int[] of user ids
     */
    public function getNotificationRecipients(): array
    {
        $query = "SELECT * FROM obj_members " .
            "WHERE notification = 1 " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, ilDBConstants::T_INTEGER) . " ";
        $res = $this->ilDB->query($query);
        $recp = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($this->isAdmin((int) $row->usr_id) || $this->isTutor((int) $row->usr_id)) {
                $recp[] = (int) $row->usr_id;
            }
        }
        return $recp;
    }

    /**
     * Get number of members (not participants)
     */
    public function getCountMembers(): int
    {
        return count($this->members);
    }

    /**
     * Get number of participants
     */
    public function getCountParticipants(): int
    {
        return count($this->participants);
    }

    /**
     * Get all participants ids
     * @return int[]
     */
    public function getParticipants(): array
    {
        return $this->participants;
    }

    /**
     * Get all members ids (admins and tutors are not members)
     * Use get participants to fetch all
     * @return int[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    /**
     * Get all admins ids
     * @return int[]
     */
    public function getAdmins(): array
    {
        return $this->admins;
    }

    public function getCountAdmins(): int
    {
        return count($this->getAdmins());
    }

    /**
     * Get all tutors ids
     * @return int[]
     */
    public function getTutors(): array
    {
        return $this->tutors;
    }

    /**
     * check if user is admin
     */
    public function isAdmin(int $a_usr_id): bool
    {
        return in_array($a_usr_id, $this->admins);
    }

    /**
     * is user tutor
     */
    public function isTutor(int $a_usr_id): bool
    {
        return in_array($a_usr_id, $this->tutors);
    }

    /**
     * is user member
     */
    public function isMember(int $a_usr_id): bool
    {
        return in_array($a_usr_id, $this->members);
    }

    /**
     * check if user is assigned
     */
    public function isAssigned(int $a_usr_id): bool
    {
        return in_array($a_usr_id, $this->participants);
    }

    /**
     * Check if user is last admin
     */
    public function isLastAdmin(int $a_usr_id): bool
    {
        return in_array($a_usr_id, $this->getAdmins()) && count($this->getAdmins()) === 1;
    }

    /**
     * Get object roles
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Get assigned roles
     */
    public function getAssignedRoles(int $a_usr_id): array
    {
        $assigned = [];
        foreach ($this->roles as $role) {
            if ($this->rbacReview->isAssigned($a_usr_id, $role)) {
                $assigned[] = $role;
            }
        }
        return $assigned;
    }

    /**
     * Update role assignments
     * @access public
     * @param int usr_id
     * @param int[] array of new roles
     */
    public function updateRoleAssignments($a_usr_id, $a_roles): void
    {
        foreach ($this->getRoles() as $role_id) {
            if ($this->rbacReview->isAssigned($a_usr_id, $role_id)) {
                if (!in_array($role_id, $a_roles)) {
                    $this->rbacAdmin->deassignUser($role_id, $a_usr_id);
                }
            } elseif (in_array($role_id, $a_roles)) {
                $this->rbacAdmin->assignUser($role_id, $a_usr_id);
            }
        }
        $this->rbacReview->clearCaches();
        $this->readParticipants();
        $this->readParticipantsStatus();
    }

    /**
     * Check if users for deletion are last admins
     * @access public
     * @param int[] array of user ids for deletion
     * @todo   fix this and add unit test
     */
    public function checkLastAdmin(array $a_usr_ids): bool
    {
        foreach ($this->getAdmins() as $admin_id) {
            if (!in_array($admin_id, $a_usr_ids)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked(int $a_usr_id): bool
    {
        if (isset($this->participants_status[$a_usr_id])) {
            return (bool) $this->participants_status[$a_usr_id]['blocked'];
        }
        return false;
    }

    /**
     * Check if user has passed object
     */
    public function hasPassed(int $a_usr_id): bool
    {
        if (isset($this->participants_status[$a_usr_id])) {
            return (bool) $this->participants_status[$a_usr_id]['passed'];
        }
        return false;
    }

    /**
     * Drop user from all roles
     */
    public function delete(int $a_usr_id): void
    {
        $this->recommended_content_manager->removeObjectRecommendation($a_usr_id, $this->ref_id);
        foreach ($this->roles as $role_id) {
            $this->rbacAdmin->deassignUser($role_id, $a_usr_id);
        }

        $query = "DELETE FROM obj_members " .
            "WHERE usr_id = " . $this->ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, 'integer');
        $res = $this->ilDB->manipulate($query);

        $this->readParticipants();
        $this->readParticipantsStatus();

        $this->eventHandler->raise(
            $this->getComponent(),
            "deleteParticipant",
            [
                'obj_id' => $this->obj_id,
                'usr_id' => $a_usr_id
            ]
        );
    }

    /**
     * Update blocked status
     */
    public function updateBlocked(int $a_usr_id, bool $a_blocked): void
    {
        $this->participants_status[$a_usr_id]['blocked'] = (int) $a_blocked;
        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " " .
            "AND usr_id = " . $this->ilDB->quote($a_usr_id, 'integer');
        $res = $this->ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE obj_members SET " .
                "blocked = " . $this->ilDB->quote((int) $a_blocked, 'integer') . " " .
                "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " " .
                "AND usr_id = " . $this->ilDB->quote($a_usr_id, 'integer');
        } else {
            $query = "INSERT INTO obj_members (blocked,obj_id,usr_id,notification,passed) " .
                "VALUES ( " .
                $this->ilDB->quote((int) $a_blocked, 'integer') . ", " .
                $this->ilDB->quote($this->obj_id, 'integer') . ", " .
                $this->ilDB->quote($a_usr_id, 'integer') . ", " .
                $this->ilDB->quote(0, 'integer') . ", " .
                $this->ilDB->quote(0, 'integer') .
                ")";
        }
        $res = $this->ilDB->manipulate($query);
    }

    public function updateContact(int $a_usr_id, bool $a_contact): void
    {
        $this->ilDB->manipulate(
            'UPDATE obj_members SET ' .
            'contact = ' . $this->ilDB->quote($a_contact, 'integer') . ' ' .
            'WHERE obj_id = ' . $this->ilDB->quote($this->obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $this->ilDB->quote($a_usr_id, 'integer')
        );
        $this->participants_status[$a_usr_id]['contact'] = $a_contact;
    }

    /**
     * get user ids which are confirgured as contact
     * @return int[]
     */
    public function getContacts(): array
    {
        $contacts = array();
        foreach ($this->participants_status as $usr_id => $status) {
            if ($status['contact']) {
                $contacts[] = (int) $usr_id;
            }
        }
        return $contacts;
    }

    /**
     * Update notification status
     */
    public function updateNotification(int $a_usr_id, bool $a_notification): void
    {
        $this->participants_status[$a_usr_id]['notification'] = $a_notification;

        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " " .
            "AND usr_id = " . $this->ilDB->quote($a_usr_id, 'integer');
        $res = $this->ilDB->query($query);
        if ($res->numRows()) {
            $query = "UPDATE obj_members SET " .
                "notification = " . $this->ilDB->quote((int) $a_notification, 'integer') . " " .
                "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " " .
                "AND usr_id = " . $this->ilDB->quote($a_usr_id, 'integer');
        } else {
            $query = "INSERT INTO obj_members (notification,obj_id,usr_id,passed,blocked) " .
                "VALUES ( " .
                $this->ilDB->quote((int) $a_notification, 'integer') . ", " .
                $this->ilDB->quote($this->obj_id, 'integer') . ", " .
                $this->ilDB->quote($a_usr_id, 'integer') . ", " .
                $this->ilDB->quote(0, 'integer') . ", " .
                $this->ilDB->quote(0, 'integer') .
                ")";
        }
        $res = $this->ilDB->manipulate($query);
    }

    public function add(int $a_usr_id, int $a_role): bool
    {
        if ($this->isAssigned($a_usr_id)) {
            return false;
        }

        switch ($a_role) {
            case self::IL_LSO_ADMIN:
            case self::IL_GRP_ADMIN:
            case self::IL_CRS_ADMIN:
                $this->admins[] = $a_usr_id;
                break;

            case self::IL_CRS_TUTOR:
                $this->tutors[] = $a_usr_id;
                break;

            case self::IL_SESS_MEMBER:
            case self::IL_LSO_MEMBER:
            case self::IL_GRP_MEMBER:
            case self::IL_CRS_MEMBER:
                $this->members[] = $a_usr_id;
                break;

        }

        $this->participants[] = $a_usr_id;
        $this->rbacAdmin->assignUser($this->role_data[$a_role], $a_usr_id);

        // Delete subscription request
        $this->deleteSubscriber($a_usr_id);

        ilWaitingList::deleteUserEntry($a_usr_id, $this->obj_id);

        $this->eventHandler->raise(
            $this->getComponent(),
            "addParticipant",
            array(
                'obj_id' => $this->obj_id,
                'usr_id' => $a_usr_id,
                'role_id' => $a_role
            )
        );
        return true;
    }

    /**
     * @param int[]
     */
    public function deleteParticipants(array $a_user_ids): bool
    {
        foreach ($a_user_ids as $user_id) {
            $this->delete($user_id);
        }
        return true;
    }

    /**
     * Add desktop item
     * @access public
     */
    public function addRecommendation(int $a_usr_id): void
    {
        // deactivated for now, see discussion at
        // https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
        // $this->recommended_content_manager->addObjectRecommendation($a_usr_id, $this->ref_id);
    }

    public function isNotificationEnabled(int $a_usr_id): bool
    {
        if (isset($this->participants_status[$a_usr_id])) {
            return (bool) $this->participants_status[$a_usr_id]['notification'];
        }
        return false;
    }

    public function isContact(int $a_usr_id): bool
    {
        if (isset($this->participants_status[$a_usr_id])) {
            return (bool) $this->participants_status[$a_usr_id]['contact'];
        }
        return false;
    }

    public function getAutoGeneratedRoleId(int $a_role_type): int
    {
        if (array_key_exists($a_role_type, $this->role_data)) {
            return $this->role_data[$a_role_type];
        }
        return 0;
    }

    protected function readParticipants(): void
    {
        $this->roles = $this->rbacReview->getRolesOfRoleFolder($this->ref_id, false);
        $this->participants = [];
        $this->members = $this->admins = $this->tutors = [];

        $additional_roles = [];
        $auto_generated_roles = [];
        foreach ($this->roles as $role_id) {
            $title = $this->objectDataCache->lookupTitle($role_id);
            switch (substr($title, 0, 8)) {
                case 'il_crs_m':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_MEMBER;
                    $this->role_data[self::IL_CRS_MEMBER] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->members = array_unique(array_merge($assigned, $this->members));
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                case 'il_crs_a':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_ADMIN;
                    $this->role_data[self::IL_CRS_ADMIN] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->admins = $this->rbacReview->assignedUsers($role_id);
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                case 'il_crs_t':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_TUTOR;
                    $this->role_data[self::IL_CRS_TUTOR] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->tutors = $this->rbacReview->assignedUsers($role_id);
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                case 'il_grp_a':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_ADMIN;
                    $this->role_data[self::IL_GRP_ADMIN] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->admins = $this->rbacReview->assignedUsers($role_id);
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                case 'il_grp_m':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_MEMBER;
                    $this->role_data[self::IL_GRP_MEMBER] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->members = $this->rbacReview->assignedUsers($role_id);
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                case 'il_sess_':
                    $this->role_data[self::IL_SESS_MEMBER] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->members = $this->rbacReview->assignedUsers($role_id);
                    break;

                case 'il_lso_m':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_MEMBER;
                    $this->role_data[self::IL_LSO_MEMBER] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->members = $this->rbacReview->assignedUsers($role_id);
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                case 'il_lso_a':
                    $auto_generated_roles[$role_id] = self::IL_ROLE_POSITION_ADMIN;
                    $this->role_data[self::IL_LSO_ADMIN] = $role_id;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->admins = $this->rbacReview->assignedUsers($role_id);
                    $this->role_assignments[$role_id] = $assigned;
                    break;

                default:
                    $additional_roles[$role_id] = $title;
                    $this->participants = array_unique(array_merge(
                        $assigned = $this->rbacReview->assignedUsers($role_id),
                        $this->participants
                    ));
                    $this->members = array_unique(array_merge($assigned, $this->members));
                    $this->role_assignments[$role_id] = $assigned;
                    break;
            }
        }
        asort($auto_generated_roles);
        asort($additional_roles);
        $this->roles_sorted = $auto_generated_roles + $additional_roles;
    }

    /**
     * Read status of participants (blocked, notification, passed)
     */
    protected function readParticipantsStatus(): void
    {
        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " ";
        $res = $this->ilDB->query($query);
        $this->participants_status = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->participants_status[(int) $row->usr_id]['blocked'] = (bool) $row->blocked;
            $this->participants_status[(int) $row->usr_id]['notification'] = (bool) $row->notification;
            $this->participants_status[(int) $row->usr_id]['passed'] = (bool) $row->passed;
            $this->participants_status[(int) $row->usr_id]['contact'] = (bool) $row->contact;
        }
    }

    /**
     * Check membership for
     */
    public function isGroupingMember(int $a_usr_id, string $a_field = ''): bool
    {
        if ($a_field === '') {
            return false;
        }
        // Used for membership limitations -> check membership by given field
        $tmp_user = ilObjectFactory::getInstanceByObjId($a_usr_id);
        if (!$tmp_user instanceof ilObjUser) {
            $this->logger->logStack(ilLogLevel::ERROR);
            throw new DomainException('Invalid user id given: ' . $a_usr_id);
        }
        switch ($a_field) {
            case 'login':
                $and = "AND login = " . $this->ilDB->quote($tmp_user->getLogin(), 'text') . " ";
                break;
            case 'email':
                $and = "AND email = " . $this->ilDB->quote($tmp_user->getEmail(), 'text') . " ";
                break;
            case 'matriculation':
                $and = "AND matriculation = " . $this->ilDB->quote($tmp_user->getMatriculation(), 'text') . " ";
                break;

            default:
                $and = "AND usr_id = " . $this->ilDB->quote($a_usr_id, 'integer') . " ";
                break;
        }

        if (!$this->getParticipants()) {
            return false;
        }

        $query = "SELECT * FROM usr_data ud " .
            "WHERE " . $this->ilDB->in('usr_id', $this->getParticipants(), false, 'integer') . " " .
            $and;

        $res = $this->ilDB->query($query);
        return (bool) $res->numRows();
    }

    /**
     * @return int[]
     */
    public static function lookupSubscribers(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $subscribers = array();
        $query = "SELECT usr_id FROM il_subscribers " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "ORDER BY sub_time ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $subscribers[] = (int) $row->usr_id;
        }
        return $subscribers;
    }

    /**
     * get all subscribers
     * int[]
     */
    public function getSubscribers(): array
    {
        $this->readSubscribers();
        return $this->subscribers;
    }

    public function getCountSubscribers(): int
    {
        return count($this->getSubscribers());
    }

    public function getSubscriberData(int $a_usr_id): array
    {
        return $this->readSubscriberData($a_usr_id);
    }

    public function assignSubscribers(array $a_usr_ids): bool
    {
        if (!is_array($a_usr_ids) || !count($a_usr_ids)) {
            return false;
        }
        foreach ($a_usr_ids as $id) {
            if (!$this->assignSubscriber($id)) {
                return false;
            }
        }
        return true;
    }

    public function assignSubscriber(int $a_usr_id): bool
    {
        $this->error->setMessage("");
        if (!$this->isSubscriber($a_usr_id)) {
            $this->error->appendMessage($this->lng->txt("crs_user_notsubscribed"));

            return false;
        }
        if ($this->isAssigned($a_usr_id)) {
            $tmp_obj = ilObjectFactory::getInstanceByObjId($a_usr_id);
            $this->error->appendMessage($tmp_obj->getLogin() . ": " . $this->lng->txt("crs_user_already_assigned"));

            return false;
        }

        if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($a_usr_id, false)) {
            $this->error->appendMessage($this->lng->txt("crs_user_not_exists"));
            return false;
        }

        if ($this instanceof ilCourseParticipants) {
            $this->add($tmp_obj->getId(), self::IL_CRS_MEMBER);
        }
        if ($this instanceof ilGroupParticipants) {
            $this->add($tmp_obj->getId(), self::IL_GRP_MEMBER);
        }
        if ($this instanceof ilLearningSequenceParticipants) {
            $this->add($tmp_obj->getId(), self::IL_LSO_MEMBER);
        }
        if ($this instanceof ilSessionParticipants) {
            $this->register($tmp_obj->getId());
        }
        $this->deleteSubscriber($a_usr_id);
        return true;
    }

    /**
     * @todo check and fix notification
     */
    public function autoFillSubscribers(): int
    {
        $this->readSubscribers();
        $counter = 0;
        foreach ($this->subscribers as $subscriber) {
            if (!$this->assignSubscriber($subscriber)) {
                continue;
            }
            ++$counter;
        }
        return $counter;
    }

    public function addSubscriber(int $a_usr_id): void
    {
        $query = "INSERT INTO il_subscribers (usr_id,obj_id,subject,sub_time) " .
            " VALUES (" .
            $this->ilDB->quote($a_usr_id, 'integer') . "," .
            $this->ilDB->quote($this->obj_id, 'integer') . ", " .
            $this->ilDB->quote('', 'text') . ", " .
            $this->ilDB->quote(time(), 'integer') .
            ")";
        $res = $this->ilDB->manipulate($query);
    }

    public function updateSubscriptionTime(int $a_usr_id, int $a_subtime): void
    {
        $query = "UPDATE il_subscribers " .
            "SET sub_time = " . $this->ilDB->quote($a_subtime, 'integer') . " " .
            "WHERE usr_id = " . $this->ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " ";
        $res = $this->ilDB->manipulate($query);
    }

    public function updateSubject(int $a_usr_id, string $a_subject): void
    {
        $query = "UPDATE il_subscribers " .
            "SET subject = " . $this->ilDB->quote($a_subject, 'text') . " " .
            "WHERE usr_id = " . $this->ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " ";
        $res = $this->ilDB->manipulate($query);
    }

    public function deleteSubscriber(int $a_usr_id): void
    {
        $query = "DELETE FROM il_subscribers " .
            "WHERE usr_id = " . $this->ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " ";
        $res = $this->ilDB->manipulate($query);
    }

    public function deleteSubscribers(array $a_usr_ids): bool
    {
        if (!count($a_usr_ids)) {
            $this->error->setMessage('');
            $this->error->appendMessage($this->lng->txt("no_usr_ids_given"));
            return false;
        }
        $query = "DELETE FROM il_subscribers " .
            "WHERE " . $this->ilDB->in('usr_id', $a_usr_ids, false, 'integer') . " " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, 'integer');
        $res = $this->ilDB->query($query);
        return true;
    }

    public function isSubscriber(int $a_usr_id): bool
    {
        $query = "SELECT * FROM il_subscribers " .
            "WHERE usr_id = " . $this->ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->ilDB->quote($this->obj_id, 'integer');

        $res = $this->ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    public static function _isSubscriber(int $a_obj_id, int $a_usr_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM il_subscribers " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    /**
     * @todo fix performance; check if method is in use
     */
    protected function readSubscribers(): void
    {
        $this->subscribers = [];
        $query = "SELECT usr_id FROM il_subscribers " .
            "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " " .
            "ORDER BY sub_time ";

        $res = $this->ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!ilObjectFactory::getInstanceByObjId((int) $row->usr_id, false)) {
                $this->deleteSubscriber((int) $row->usr_id);
            }
            $this->subscribers[] = (int) $row->usr_id;
        }
    }

    /**
     * @return array<{time: int, usr_id: int, subject: string}>
     */
    protected function readSubscriberData(int $a_usr_id): array
    {
        $query = "SELECT * FROM il_subscribers " .
            "WHERE obj_id = " . $this->ilDB->quote($this->obj_id, 'integer') . " " .
            "AND usr_id = " . $this->ilDB->quote($a_usr_id, 'integer');

        $res = $this->ilDB->query($query);
        $data = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data["time"] = (int) $row->sub_time;
            $data["usr_id"] = (int) $row->usr_id;
            $data['subject'] = (string) $row->subject;
        }
        return $data;
    }

    /**
     * @param int $a_usr_id
     * @return array<int, array<{time: int, usr_id: int, subject: string}>>
     */
    public static function lookupSubscribersData(int $a_obj_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM il_subscribers ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);

        $data = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data[$row->usr_id]['time'] = (int) $row->sub_time;
            $data[$row->usr_id]['usr_id'] = (int) $row->usr_id;
            $data[$row->usr_id]['subject'] = (string) $row->subject;
        }
        return $data;
    }

    /**
     * Get all support contacts for a user
     * @param int    $a_usr_id usr_id
     * @param string $a_type   crs or grp
     * @return array array of contacts (keys are usr_id and obj_id)
     * @todo  join the two queries or alternatively reuse _getMembershipByType
     * @todo  fix returning fetchAssoc result
     */
    public static function _getAllSupportContactsOfUser(int $a_usr_id, string $a_type): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // for the first part

        // this will also dismiss local roles!
        $j2 = "JOIN object_data obd2 ON (ua.rol_id = obd2.obj_id) ";
        $a2 = "AND obd2.title LIKE 'il_" . $a_type . "_mem%' ";

        // #14290 - no role folder anymore
        $query = "SELECT DISTINCT obd.obj_id,obr.ref_id FROM rbac_ua ua " .
            "JOIN rbac_fa fa ON ua.rol_id = fa.rol_id " .
            "JOIN object_reference obr ON fa.parent = obr.ref_id " .
            "JOIN object_data obd ON obr.obj_id = obd.obj_id " .
            $j2 .
            "WHERE obd.type = " . $ilDB->quote($a_type, 'text') . " " .
            "AND fa.assign = 'y' " .
            "AND ua.usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            $a2;

        $res = $ilDB->query($query);
        $obj_ids = array();
        while ($row = $ilDB->fetchObject($res)) {
            $obj_ids[] = (int) $row->obj_id;
        }

        $set = $ilDB->query("SELECT obj_id, usr_id FROM obj_members " .
            " WHERE " . $ilDB->in("obj_id", $obj_ids, false, "integer") .
            " AND contact = " . $ilDB->quote(1, "integer"));
        $res = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $res[] = $rec;
        }
        return $res;
    }

    /**
     * Set role order position
     */
    public function setRoleOrderPosition(int $a_user_id): string
    {
        $counter = 0;
        $sortable_assignments = '9999999999';
        foreach ($this->roles_sorted as $role_id => $trash) {
            if (in_array($a_user_id, (array) $this->role_assignments[$role_id])) {
                $sortable_assignments = substr_replace($sortable_assignments, '1', $counter, 1);
            }
            ++$counter;
        }
        return $sortable_assignments;
    }
}
