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
 * Base class for course and group participant
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ServicesMembership
 */
abstract class ilParticipant
{
    protected const MEMBERSHIP_ADMIN = 1;
    protected const MEMBERSHIP_TUTOR = 2;
    protected const MEMBERSHIP_MEMBER = 3;

    private int $obj_id = 0;
    private int $usr_id = 0;
    protected string $type = '';
    private int $ref_id = 0;

    private string $component = '';

    private array $roles = [];
    private array $role_data = [];
    private bool $participants = false;
    private bool $admins = false;
    private bool $tutors = false;
    private bool $members = false;

    private ?int $numMembers = null;
    private array $member_roles = [];
    private array $participants_status = array();

    protected ilRecommendedContentManager $recommended_content_manager;
    protected ilDBInterface $db;
    protected ilRbacReview $rbacReview;
    protected ilRbacAdmin $rbacAdmin;
    protected ilObjectDataCache $objectDataCache;
    protected ilAppEventHandler $eventHandler;

    protected function __construct(string $a_component_name, int $a_obj_id, int $a_usr_id)
    {
        global $DIC;

        $this->obj_id = $a_obj_id;
        $this->usr_id = $a_usr_id;
        $this->type = ilObject::_lookupType($a_obj_id);
        $ref_ids = ilObject::_getAllReferences($this->obj_id);
        $this->ref_id = current($ref_ids);
        $this->component = $a_component_name;

        $this->recommended_content_manager = new ilRecommendedContentManager();
        $this->db = $DIC->database();
        $this->rbacReview = $DIC->rbac()->review();
        $this->rbacAdmin = $DIC->rbac()->admin();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->eventHandler = $DIC->event();

        $this->readParticipant();
        $this->readParticipantStatus();
    }

    public static function updateMemberRoles(int $a_obj_id, int $a_usr_id, int $a_role_id, int $a_status): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $a_membership_role_type = self::getMembershipRoleType($a_role_id);
        switch ($a_membership_role_type) {
            case self::MEMBERSHIP_ADMIN:
                $update_fields = array('admin' => array('integer', $a_status ? 1 : 0));
                $update_string = ('admin = ' . $ilDB->quote($a_status ? 1 : 0, 'integer'));
                break;

            case self::MEMBERSHIP_TUTOR:
                $update_fields = array('tutor' => array('integer', $a_status ? 1 : 0));
                $update_string = ('tutor = ' . $ilDB->quote($a_status ? 1 : 0, 'integer'));
                break;

            case self::MEMBERSHIP_MEMBER:
            default:
                $current_status = self::lookupStatusByMembershipRoleType($a_obj_id, $a_usr_id, $a_membership_role_type);

                if ($a_status) {
                    $new_status = $current_status + 1;
                }
                if (!$a_status) {
                    $new_status = $current_status - 1;
                    if ($new_status < 0) {
                        $new_status = 0;
                    }
                }

                $update_fields = array('member' => array('integer', $new_status));
                $update_string = ('member = ' . $ilDB->quote($new_status, 'integer'));
                break;
        }

        $query = 'SELECT count(*) num FROM obj_members  ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);

        $found = false;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->num) {
                $found = true;
            }
        }
        if (!$found) {
            $ilDB->replace(
                'obj_members',
                array(
                    'obj_id' => array('integer', $a_obj_id),
                    'usr_id' => array('integer', $a_usr_id)
                ),
                $update_fields
            );
        } else {
            $query = 'UPDATE obj_members SET ' .
                $update_string . ' ' .
                'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
                'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer');

            $ilDB->manipulate($query);
        }

        $query = 'DELETE from obj_members ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $ilDB->quote($a_usr_id, 'integer') . ' ' .
            'AND admin = ' . $ilDB->quote(0, 'integer') . ' ' .
            'AND tutor = ' . $ilDB->quote(0, 'integer') . ' ' .
            'AND member = ' . $ilDB->quote(0, 'integer');
        $ilDB->manipulate($query);
    }

    public static function getMembershipRoleType(int $a_role_id): int
    {
        $title = ilObject::_lookupTitle($a_role_id);
        switch (substr($title, 0, 8)) {
            case 'il_crs_a':
            case 'il_grp_a':
                return self::MEMBERSHIP_ADMIN;

            case 'il_crs_t':
                return self::MEMBERSHIP_TUTOR;

            case 'il_crs_m':
            default:
                return self::MEMBERSHIP_MEMBER;
        }
    }

    public static function lookupStatusByMembershipRoleType(
        int $a_obj_id,
        int $a_usr_id,
        int $a_membership_role_type
    ): int {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM obj_members ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $ilDB->quote($a_usr_id, ilDBConstants::T_INTEGER) . ' ';
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($a_membership_role_type) {
                case self::MEMBERSHIP_ADMIN:
                    return (int) $row->admin;

                case self::MEMBERSHIP_TUTOR:
                    return (int) $row->tutor;

                case self::MEMBERSHIP_MEMBER:
                    return (int) $row->member;
            }
        }
        return 0;
    }

    /**
     * Get component name
     * Used for event handling
     */
    protected function getComponent(): string
    {
        return $this->component;
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function isBlocked(): bool
    {
        return (bool) ($this->participants_status[$this->getUserId()]['blocked'] ?? false);
    }

    /**
     * Check if user is contact for current object
     */
    public function isContact(): bool
    {
        return (bool) ($this->participants_status[$this->getUserId()]['contact'] ?? false);
    }

    public function isAssigned(): bool
    {
        return $this->participants;
    }

    public function isMember(): bool
    {
        return $this->members;
    }

    public function isAdmin(): bool
    {
        return $this->admins;
    }

    public function isTutor(): bool
    {
        return $this->tutors;
    }

    public function isParticipant(): bool
    {
        return $this->participants;
    }

    public function getNumberOfMembers(): int
    {
        if ($this->numMembers === null) {
            $this->numMembers = $this->rbacReview->getNumberOfAssignedUsers($this->member_roles);
        }
        return $this->numMembers;
    }

    protected function readParticipant(): void
    {
        $this->roles = $this->rbacReview->getRolesOfRoleFolder($this->ref_id, false);
        $this->member_roles = [];
        foreach ($this->roles as $role_id) {
            $title = $this->objectDataCache->lookupTitle($role_id);
            switch (substr($title, 0, 8)) {
                case 'il_crs_m':
                    $this->member_roles[] = $role_id;
                    $this->role_data[ilParticipants::IL_CRS_MEMBER] = $role_id;
                    if ($this->rbacReview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->members = true;
                    }
                    break;

                case 'il_crs_a':
                    $this->role_data[ilParticipants::IL_CRS_ADMIN] = $role_id;
                    if ($this->rbacReview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->admins = true;
                    }
                    break;

                case 'il_crs_t':
                    $this->role_data[ilParticipants::IL_CRS_TUTOR] = $role_id;
                    if ($this->rbacReview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->tutors = true;
                    }
                    break;

                case 'il_grp_a':
                    $this->role_data[ilParticipants::IL_GRP_ADMIN] = $role_id;
                    if ($this->rbacReview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->admins = true;
                    }
                    break;

                case 'il_grp_m':
                    $this->member_roles[] = $role_id;
                    $this->role_data[ilParticipants::IL_GRP_MEMBER] = $role_id;
                    if ($this->rbacReview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->members = true;
                    }
                    break;

                default:

                    $this->member_roles[] = $role_id;
                    if ($this->rbacReview->isAssigned($this->getUserId(), $role_id)) {
                        $this->participants = true;
                        $this->members = true;
                    }
                    break;
            }
        }
    }

    protected function readParticipantStatus(): void
    {
        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            'AND usr_id = ' . $this->db->quote($this->getUserId(), 'integer');

        $res = $this->db->query($query);
        $this->participants_status = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->participants_status[$this->getUserId()]['blocked'] = (bool) $row->blocked;
            $this->participants_status[$this->getUserId()]['notification'] = (bool) $row->notification;
            $this->participants_status[$this->getUserId()]['passed'] = (bool) $row->passed;
            $this->participants_status[$this->getUserId()]['contact'] = (bool) $row->contact;
        }
    }

    public function add(int $a_usr_id, int $a_role): bool
    {
        if ($this->rbacReview->isAssignedToAtLeastOneGivenRole($a_usr_id, $this->roles)) {
            return false;
        }

        switch ($a_role) {
            case ilParticipants::IL_GRP_ADMIN:
            case ilParticipants::IL_CRS_ADMIN:
                $this->admins = true;
                break;

            case ilParticipants::IL_CRS_TUTOR:
                $this->tutors = true;
                break;

            case ilParticipants::IL_GRP_MEMBER:
            case ilParticipants::IL_CRS_MEMBER:
                $this->members = true;
                break;
        }

        $this->rbacAdmin->assignUser($this->role_data[$a_role], $a_usr_id);
        $this->addRecommendation($a_usr_id);

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

    public function delete(int $a_usr_id): void
    {
        $this->recommended_content_manager->removeObjectRecommendation($a_usr_id, $this->ref_id);
        foreach ($this->roles as $role_id) {
            $this->rbacAdmin->deassignUser($role_id, $a_usr_id);
        }

        $query = "DELETE FROM obj_members " .
            "WHERE usr_id = " . $this->db->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->obj_id, 'integer');
        $res = $this->db->manipulate($query);

        $this->eventHandler->raise(
            $this->getComponent(),
            "deleteParticipant",
            array(
                'obj_id' => $this->obj_id,
                'usr_id' => $a_usr_id
            )
        );
    }

    public function deleteSubscriber(int $a_usr_id): void
    {
        $query = "DELETE FROM il_subscribers " .
            "WHERE usr_id = " . $this->db->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->obj_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function addRecommendation($a_usr_id): void
    {
        // deactivated for now, see discussion at
        // https://docu.ilias.de/goto_docu_wiki_wpage_5620_1357.html
        //$this->recommended_content_manager->addObjectRecommendation($a_usr_id, $this->ref_id);
    }

    public function updateContact(int $a_usr_id, bool $a_contact): void
    {
        $this->db->manipulate(
            'UPDATE obj_members SET ' .
            'contact = ' . $this->db->quote($a_contact, 'integer') . ' ' .
            'WHERE obj_id = ' . $this->db->quote($this->obj_id, 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($a_usr_id, 'integer')
        );
        $this->participants_status[$a_usr_id]['contact'] = $a_contact;
    }

    public function updateNotification(int $a_usr_id, bool $a_notification): void
    {
        $this->participants_status[$a_usr_id]['notification'] = $a_notification;

        $query = "SELECT * FROM obj_members " .
            "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
            "AND usr_id = " . $this->db->quote($a_usr_id, 'integer');
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $query = "UPDATE obj_members SET " .
                "notification = " . $this->db->quote((int) $a_notification, 'integer') . " " .
                "WHERE obj_id = " . $this->db->quote($this->obj_id, 'integer') . " " .
                "AND usr_id = " . $this->db->quote($a_usr_id, 'integer');
        } else {
            $query = "INSERT INTO obj_members (notification,obj_id,usr_id,passed,blocked) " .
                "VALUES ( " .
                $this->db->quote((int) $a_notification, 'integer') . ", " .
                $this->db->quote($this->obj_id, 'integer') . ", " .
                $this->db->quote($a_usr_id, 'integer') . ", " .
                $this->db->quote(0, 'integer') . ", " .
                $this->db->quote(0, 'integer') .
                ")";
        }
        $this->db->manipulate($query);
    }

    public function checkLastAdmin(array $a_usr_ids): bool
    {
        $admin_role_id =
            $this->type === 'crs' ?
                $this->role_data[ilParticipants::IL_CRS_ADMIN] :
                $this->role_data[ilParticipants::IL_GRP_ADMIN];

        $query = "
		SELECT			COUNT(rolesusers.usr_id) cnt
		
		FROM			object_data rdata
		
		LEFT JOIN		rbac_ua  rolesusers		
		ON				rolesusers.rol_id = rdata.obj_id
		
		WHERE			rdata.obj_id = %s
		";

        $query .= ' AND ' . $this->db->in('rolesusers.usr_id', $a_usr_ids, true, 'integer');
        $res = $this->db->queryF($query, array('integer'), array($admin_role_id));

        $data = $this->db->fetchAssoc($res);
        return $data['cnt'] > 0;
    }
}
