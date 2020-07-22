<?php

declare(strict_types=1);

/**
 * Provides Role actions.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 */
class ilLearningSequenceRoles
{
    const ROLE_LS_ADMIN = "il_lso_admin";
    const ROLE_LS_MEMBER = "il_lso_member";

    const TYPE_PORTFOLIO = "prtf";

    /**
     * @var ilObjLearningSequence
     */
    protected $object;

    /**
     * @var ilLearningSequenceParticipants
     */
    protected $participants;

    /**
     * @var ilRbacAdmin
     */
    protected $rbacadmin;

    /**
     * @var ilRbacReview
     */
    protected $rbacreview;

    /**
     * @var ilDB
     */
    protected $database;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var array
     */
    protected $local_roles;

    public function __construct(
        ilObjLearningSequence $object,
        ilLearningSequenceParticipants $participants,
        ilCtrl $ctrl,
        ilRbacAdmin $rbacadmin,
        ilRbacReview $rbacreview,
        ilDBInterface $database,
        ilObjUser $user
    ) {
        $this->object = $object;
        $this->participants = $participants;
        $this->ctrl = $ctrl;
        $this->rbacadmin = $rbacadmin;
        $this->rbacreview = $rbacreview;
        $this->database = $database;
        $this->user = $user;

        $this->local_roles = array();
    }

    public function initDefaultRoles()
    {
        ilObjRole::createDefaultRole(
            self::ROLE_LS_ADMIN . '_' . $this->object->getRefId(),
            "LSO admin learning sequence obj_no." . $this->object->getId(),
            self::ROLE_LS_ADMIN,
            $this->object->getRefId()
        );

        ilObjRole::createDefaultRole(
            self::ROLE_LS_MEMBER . '_' . $this->object->getRefId(),
            "LSO member of learning sequence obj_no." . $this->object->getId(),
            self::ROLE_LS_MEMBER,
            $this->object->getRefId()
        );
    }

    /**
    * @return array [title|id] of roles...
    */
    public function getLocalLearningSequenceRoles(bool $translate = false) : array
    {
        if (count($this->local_roles) == 0) {
            $role_ids = $this->rbacreview->getRolesOfRoleFolder(
                $this->object->getRefId()
            );

            foreach ($role_ids as $role_id) {
                if ($this->rbacreview->isAssignable(
                    $role_id,
                    $this->object->getRefId()
                ) == true
                ) {
                    $role = $this->getRoleObject((int) $role_id);

                    if ($translate) {
                        $role_name = ilObjRole::_getTranslation($role->getTitle());
                    } else {
                        $role_name = $role->getTitle();
                    }

                    $this->local_roles[$role_name] = (int) $role->getId();
                }
            }
        }

        return $this->local_roles;
    }

    public function getDefaultMemberRole() : int
    {
        $local_ls_roles = $this->getLocalLearningSequenceRoles();
        return $local_ls_roles[self::ROLE_LS_MEMBER . "_" . $this->object->getRefId()];
    }

    public function getDefaultAdminRole() : int
    {
        $local_ls_roles = $this->getLocalLearningSequenceRoles();
        return $local_ls_roles[self::ROLE_LS_ADMIN . "_" . $this->object->getRefId()];
    }

    public function addLSMember(int $user_id, int $role) : bool
    {
        return $this->join($user_id, $role);
    }

    public function join(int $user_id, $roles) : bool
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                $this->rbacadmin->assignUser($role, $user_id, false);
            }
        } else {
            $this->rbacadmin->assignUser($roles, $user_id, false);
        }

        return true;
    }

    public function leaveLearningSequence() : int
    {
        $member_ids = $this->getLearningSequenceMemberIds();

        if (count($member_ids) <= 1 || !in_array($this->user->getId(), $member_ids)) {
            return 2;
        } else {
            if (!$this->isAdmin($this->user->getId())) {
                $this->leave($this->user->getId());
                $member = new ilObjUser($this->user->getId());
                $member->dropDesktopItem($this->getRefId(), "lso");
                return 0;
            } elseif (count($this->getLearningSequenceAdminIds()) == 1) {
                return 1;
            }
        }
    }

    public function getLearningSequenceMemberIds() : array
    {
        $users = array();
        $roles = $this->getLocalLearningSequenceRoles();

        foreach ($roles as $role) {
            foreach ($this->rbacreview->assignedUsers($role) as $member_id) {
                array_push($users, $member_id);
            }
        }

        $users = array_unique($users);

        return $users;
    }

    public function leave(int $user_id) : bool
    {
        $roles = $this->participants::getMemberRoles($this->object->getRefId());

        if (!is_array($roles)) {
            return $this->rbacadmin->deassignUser($roles, $user_id);
        }

        foreach ($roles as $role) {
            $this->rbacadmin->deassignUser($role, $user_id);
        }

        return true;
    }

    public function getLearningSequenceMemberData(array $user_ids, int $active = 1)
    {
        $users = array();
        $additional_where = "";

        if (is_numeric($active) && $active > -1) {
            $additional_where = "AND active = '$active'" . PHP_EOL;
        }

        $query =
             "SELECT login, firstname, lastname, title, usr_id, last_login" . PHP_EOL
            . "FROM usr_data " . PHP_EOL
            . "WHERE usr_id IN (" . implode(',', ilUtil::quoteArray($user_ids)) . ") " . PHP_EOL
            . $additional_where . PHP_EOL
            . "ORDER BY lastname, firstname" . PHP_EOL
        ;

        $result = $this->database->query($query);

        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $users[] = [
                "id" => $row->usr_id,
                "login" => $row->login,
                "firstname" => $row->firstname,
                "lastname" => $row->lastname,
                "last_login" => $row->last_login
            ];
        }

        return $users;
    }

    public function getLearningSequenceAdminIds()
    {
        $users = array();
        $roles = $this->getDefaultLearningSequenceRoles((string) $this->object->getRefId());

        foreach ($this->rbacreview->assignedUsers($this->getDefaultAdminRole()) as $admin_id) {
            array_push($users, $admin_id);
        }

        return $users;
    }

    public function getDefaultLearningSequenceRoles(string $lso_id) : array
    {
        if (strlen($lso_id) == 0) {
            $lso_id = $this->object->getRefId();
        }

        $roles = $this->rbacreview->getRolesOfRoleFolder($lso_id);

        $default_roles = array();
        foreach ($roles as $role) {
            $object = $this->getRoleObject((int) $role);

            $member = self::ROLE_LS_MEMBER . "_" . $lso_id;
            $admin = self::ROLE_LS_ADMIN . "_" . $lso_id;

            if (strcmp($object->getTitle(), $member) == 0) {
                $default_roles["lso_member_role"] = $object->getId();
            }

            if (strcmp($object->getTitle(), $admin) == 0) {
                $default_roles["lso_admin_role"] = $object->getId();
            }
        }

        return $default_roles;
    }

    protected function getRoleObject(int $obj_id)
    {
        return ilObjectFactory::getInstanceByObjId($obj_id);
    }

    public function readMemberData(array $user_ids, array $selected_columns = null) : array
    {
        $portfolio_enabled = $this->isPortfolio($selected_columns);
        $tracking_enabled = $this->isTrackingEnabled();
        $privacy = ilPrivacySettings::_getInstance();

        if ($tracking_enabled) {
            $olp = ilObjectLP::getInstance($this->object->getId());
            $tracking_enabled = $olp->isActive();

            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
        }

        if ($privacy->enabledLearningSequenceAccessTimes()) {
            $progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
        }

        if ($portfolio_enabled) {
            $portfolios = ilObjPortfolio::getAvailablePortfolioLinksForUserIds(
                $user_ids,
                $this->ctrl->getLinkTargetByClass("ilLearningSequenceMembershipGUI", "members")
            );
        }

        $members = array();
        $profile_data = ilObjUser::_readUsersProfileData($user_ids);
        foreach ($user_ids as $usr_id) {
            $data = array();
            $name = ilObjUser::_lookupName($usr_id);

            $data['firstname'] = $name['firstname'];
            $data['lastname'] = $name['lastname'];
            $data['login'] = ilObjUser::_lookupLogin($usr_id);
            $data['usr_id'] = $usr_id;

            $data['notification'] = 0;
            if ($this->object->getLSParticipants()->isNotificationEnabled($usr_id)) {
                $data['notification'] = 1;
            }

            foreach ($profile_data[$usr_id] as $field => $value) {
                $data[$field] = $value;
            }

            if ($tracking_enabled) {
                if (in_array($usr_id, $completed)) {
                    $data['progress'] = ilLPStatus::LP_STATUS_COMPLETED;
                } elseif (in_array($usr_id, $in_progress)) {
                    $data['progress'] = ilLPStatus::LP_STATUS_IN_PROGRESS;
                } elseif (in_array($usr_id, $failed)) {
                    $data['progress'] = ilLPStatus::LP_STATUS_FAILED;
                } else {
                    $data['progress'] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
                }
            }

            if ($privacy->enabledLearningSequenceAccessTimes()) {
                if (isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts']) {
                    $data['access_time'] = ilDatePresentation::formatDate(
                        $date = new ilDateTime($progress[$usr_id]['ts'], IL_CAL_UNIX)
                    );
                    $data['access_time_unix'] = $date->get(IL_CAL_UNIX);
                } else {
                    $data['access_time'] = $this->lng->txt('no_date');
                    $data['access_time_unix'] = 0;
                }
            }

            if ($portfolio_enabled) {
                $data['prtf'] = $portfolios[$usr_id];
            }

            $members[$usr_id] = $data;
        }

        return $members;
    }

    protected function isTrackingEnabled() : bool
    {
        return
            ilObjUserTracking::_enabledLearningProgress() &&
            ilObjUserTracking::_enabledUserRelatedData()
        ;
    }

    protected function isPortfolio(array $columns = null) : bool
    {
        if (is_null($columns)) {
            return false;
        }
        return in_array(self::TYPE_PORTFOLIO, $columns);
    }
}
