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
 * Provides Role actions.
 */
class ilLearningSequenceRoles
{
    public const ROLE_LS_ADMIN = "il_lso_admin";
    public const ROLE_LS_MEMBER = "il_lso_member";

    public const TYPE_PORTFOLIO = "prtf";

    protected int $ref_id;
    protected int $obj_id;
    protected ilLearningSequenceParticipants $participants;
    protected ilCtrl $ctrl;
    protected ilRbacAdmin $rbacadmin;
    protected ilRbacReview $rbacreview;
    protected ilDBInterface $database;
    protected ilObjUser $user;
    protected ilLanguage $lng;

    protected array $local_roles;

    public function __construct(
        int $ls_ref_id,
        int $ls_obj_id,
        ilLearningSequenceParticipants $participants,
        ilCtrl $ctrl,
        ilRbacAdmin $rbacadmin,
        ilRbacReview $rbacreview,
        ilDBInterface $database,
        ilObjUser $user,
        ilLanguage $lng
    ) {
        $this->ref_id = $ls_ref_id;
        $this->obj_id = $ls_obj_id;
        $this->participants = $participants;
        $this->ctrl = $ctrl;
        $this->rbacadmin = $rbacadmin;
        $this->rbacreview = $rbacreview;
        $this->database = $database;
        $this->user = $user;
        $this->lng = $lng;

        $this->local_roles = array();
    }

    public function initDefaultRoles(): void
    {
        ilObjRole::createDefaultRole(
            self::ROLE_LS_ADMIN . '_' . $this->ref_id,
            "LSO admin learning sequence obj_no." . $this->obj_id,
            self::ROLE_LS_ADMIN,
            $this->ref_id
        );

        ilObjRole::createDefaultRole(
            self::ROLE_LS_MEMBER . '_' . $this->ref_id,
            "LSO member of learning sequence obj_no." . $this->obj_id,
            self::ROLE_LS_MEMBER,
            $this->ref_id
        );
    }

    /**
    * @return array<string, int>
    */
    public function getLocalLearningSequenceRoles(bool $translate = false): array
    {
        if (count($this->local_roles) == 0) {
            $role_ids = $this->rbacreview->getRolesOfRoleFolder(
                $this->ref_id
            );

            foreach ($role_ids as $role_id) {
                if ($this->rbacreview->isAssignable($role_id, $this->ref_id) == true) {
                    $role = $this->getRoleObject($role_id);

                    if ($translate) {
                        $role_name = ilObjRole::_getTranslation($role->getTitle());
                    } else {
                        $role_name = $role->getTitle();
                    }

                    $this->local_roles[$role_name] = $role->getId();
                }
            }
        }

        return $this->local_roles;
    }

    public function getDefaultMemberRole(): int
    {
        $local_ls_roles = $this->getLocalLearningSequenceRoles();
        return $local_ls_roles[self::ROLE_LS_MEMBER . "_" . $this->ref_id];
    }

    public function getDefaultAdminRole(): int
    {
        $local_ls_roles = $this->getLocalLearningSequenceRoles();
        return $local_ls_roles[self::ROLE_LS_ADMIN . "_" . $this->ref_id];
    }

    public function addLSMember(int $user_id, int $role): bool
    {
        return $this->join($user_id, $role);
    }

    public function join(int $user_id, int $role = null): bool
    {
        if (is_null($role)) {
            $role = $this->getDefaultMemberRole();
        }
        $this->rbacadmin->assignUser($role, $user_id);
        return true;
    }

    public function leave(int $user_id): bool
    {
        $roles = $this->participants::getMemberRoles($this->ref_id);

        foreach ($roles as $role) {
            $this->rbacadmin->deassignUser($role, $user_id);
        }

        return true;
    }

    /**
     * @return array<int>
     */
    public function getLearningSequenceAdminIds(): array
    {
        $users = array();
        foreach ($this->rbacreview->assignedUsers($this->getDefaultAdminRole()) as $admin_id) {
            $users[] = (int) $admin_id;
        }

        return $users;
    }

    /**
     * @return array<string, int>|[]
     */
    public function getDefaultLearningSequenceRoles(string $lso_id): array
    {
        if (strlen($lso_id) == 0) {
            $lso_id = $this->ref_id;
        }

        $roles = $this->rbacreview->getRolesOfRoleFolder($lso_id);

        $default_roles = array();
        foreach ($roles as $role) {
            $object = $this->getRoleObject($role);

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

    protected function getRoleObject(int $obj_id): ?\ilObject
    {
        return ilObjectFactory::getInstanceByObjId($obj_id);
    }

    /**
     * @param array<int|string> $user_ids
     * @param string[] $columns
     * @return array<int|string, array>
     */
    public function readMemberData(array $user_ids, array $selected_columns = null): array
    {
        $portfolio_enabled = $this->isPortfolio($selected_columns);
        $tracking_enabled = $this->isTrackingEnabled();
        $privacy = ilPrivacySettings::getInstance();

        if ($tracking_enabled) {
            $olp = ilObjectLP::getInstance($this->obj_id);
            $tracking_enabled = $olp->isActive();

            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->obj_id);
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->obj_id);
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->obj_id);
        }

        if ($privacy->enabledLearningSequenceAccessTimes()) {
            $progress = ilLearningProgress::_lookupProgressByObjId($this->obj_id);
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
            if ($this->participants->isNotificationEnabled($usr_id)) {
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
                if (isset($progress[$usr_id]['ts']) && $progress[$usr_id]['ts']) {
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

    protected function isTrackingEnabled(): bool
    {
        return
            ilObjUserTracking::_enabledLearningProgress() &&
            ilObjUserTracking::_enabledUserRelatedData()
        ;
    }

    protected function isPortfolio(array $columns = null): bool
    {
        if (is_null($columns)) {
            return false;
        }
        return in_array(self::TYPE_PORTFOLIO, $columns);
    }

    public function isMember(int $usr_id): bool
    {
        return $this->participants->isMember($usr_id);
    }

    public function isCompletedByUser(int $usr_id): bool
    {
        ilLPStatusWrapper::_updateStatus($this->obj_id, $usr_id);
        $tracking_active = ilObjUserTracking::_enabledLearningProgress();
        $user_completion = ilLPStatus::_hasUserCompleted($this->obj_id, $usr_id);
        return ($tracking_active && $user_completion);
    }
}
