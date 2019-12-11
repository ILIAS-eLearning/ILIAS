<?php
require_once 'Modules/IndividualAssessment/interfaces/AccessControl/interface.IndividualAssessmentAccessHandler.php';
require_once 'Services/AccessControl/classes/class.ilObjRole.php';
/**
 * @inheritdoc
 * Deal with ilias rbac-system
 */
class ilIndividualAssessmentAccessHandler implements IndividualAssessmentAccessHandler
{
    /**
     * @var ilObjIndividualAssessment
     */
    protected $iass;

    /**
     * @var ilAccessHandler
     */
    protected $handler;

    /**
     * @var ilRbacAdmin
     */
    protected $admin;

    /**
     * ilRbacReview
     */
    protected $review;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var string[]
     */
    protected $mass_global_permissions_cache;

    const DEFAULT_ROLE = 'il_iass_member';

    public function __construct(ilObjIndividualAssessment $iass, ilAccessHandler $handler, ilRbacAdmin $admin, ilRbacReview $review, ilObjUser $usr)
    {
        $this->iass = $iass;
        $this->handler = $handler;
        $this->admin = $admin;
        $this->review = $review;
        $this->usr = $usr;
        $this->mass_global_permissions_cache = array();
    }

    /**
     * @inheritdoc
     */
    public function checkAccessToObj($operation)
    {
        if ($operation == "read_learning_progress") {
            return $this->handler->checkRbacOrPositionPermissionAccess("read_learning_progress", "read_learning_progress", $this->iass->getRefId());
        }
        if ($operation == "edit_learning_progress") {
            return $this->handler->checkRbacOrPositionPermissionAccess("edit_learning_progress", "write_learning_progress", $this->iass->getRefId());
        }

        return $this->handler->checkAccessOfUser($this->usr->getId(), $operation, '', $this->iass->getRefId(), 'iass');
    }

    /**
     * @inheritdoc
     */
    public function initDefaultRolesForObject(ilObjIndividualAssessment $iass)
    {
        $role = ilObjRole::createDefaultRole(
            $this->getRoleTitleByObj($iass),
            "Admin of iass obj_no." . $iass->getId(),
            self::DEFAULT_ROLE,
            $iass->getRefId()
        );
    }

    /**
     * @inheritdoc
     */
    public function assignUserToMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass)
    {
        return $this->admin->assignUser($this->getMemberRoleIdForObj($iass), $usr->getId());
    }

    /**
     * @inheritdoc
     */
    public function deassignUserFromMemberRole(ilObjUser $usr, ilObjIndividualAssessment $iass)
    {
        return $this->admin->deassignUser($this->getMemberRoleIdForObj($iass), $usr->getId());
    }

    protected function getRoleTitleByObj(ilObjIndividualAssessment $iass)
    {
        return self::DEFAULT_ROLE . '_' . $iass->getRefId();
    }

    protected function getMemberRoleIdForObj(ilObjIndividualAssessment $iass)
    {
        return current($this->review->getLocalRoles($iass->getRefId()));
    }

    /**
     * User view iass object
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayViewObject($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('read');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('read');
    }

    /**
     * User edit iass
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayEditObject($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('write');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('write');
    }

    /**
     * User edit permissions
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayEditPermissions($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('edit_permission');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('edit_permission');
    }

    /**
     * User may edit members
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayEditMembers($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('edit_members');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('edit_members');
    }

    /**
     * User may view gradings
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayViewUser($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('read_learning_progress');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('read_learning_progress');
    }

    /**
     * User may grade
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayGradeUser($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('edit_learning_progress');
        }

        return $this->isSystemAdmin() || $this->checkAccessToObj('edit_learning_progress');
    }

    /**
     * User may grade
     *
     * @param  int	$a_user_id
     *
     * @return bool
     */
    public function mayGradeUserById($a_user_id)
    {
        return $this->isSystemAdmin()
            || ($this->mayGradeUser() && count($this->handler->filterUserIdsByRbacOrPositionOfCurrentUser("edit_learning_progress", "set_lp", $this->iass->getRefId(), [$a_user_id])) > 0);
    }

    /**
     * Filter out users that may be graded or viewed.
     *
     * @param	int[] $a_user_ids
     * @return	int[] $a_user_ids
     */
    public function filterViewableOrGradeableUsers(array $a_user_ids) : array
    {
        $usr_id = $this->usr->getId();
        $obj_id = $this->iass->getId();
        $ref_id = $this->iass->getRefId();
        if (
            $this->handler->checkAccessOfUser($usr_id, "edit_members", '', $ref_id, 'iass')
            || $this->handler->checkAccessOfUser($usr_id, "read_learning_progress", '', $ref_id, 'iass')
            || $this->handler->checkAccessOfUser($usr_id, "write_learning_progress", '', $ref_id, 'iass')
        ) {
            return $a_user_ids;
        }

        $orgu_settings = ilOrgUnitGlobalSettings::getInstance();
        if (!$orgu_settings->isPositionAccessActiveForObject($obj_id)) {
            return [];
        }

        $viewable_users = $this->handler->filterUserIdsByPositionOfCurrentUser("read_learning_progress", $ref_id, $a_user_ids);
        $gradeable_users = $this->handler->filterUserIdsByPositionOfCurrentUser("write_learning_progress", $ref_id, $a_user_ids);

        return array_unique(array_merge($viewable_users, $gradeable_users));
    }

    /**
     * User may Amend grading
     *
     * @param bool	$use_cache
     *
     * @return bool
     */
    public function mayAmendGradeUser($use_cache = true)
    {
        if ($use_cache) {
            return $this->cacheCheckAccessToObj('amend_grading');
        }

        return $this->checkAccessToObj('amend_grading');
    }

    /**
     * Get permission state from cache
     *
     * @param string	$operation
     *
     * @return bool
     */
    protected function cacheCheckAccessToObj($operation)
    {
        $iass_id = $this->iass->getId();
        $user_id = $this->usr->getId();

        if (!isset($this->mass_global_permissions_cache[$iass_id][$user_id][$operation])) {
            $this->mass_global_permissions_cache[$iass_id][$user_id][$operation]
                = $this->checkAccessToObj($operation);
        }

        return $this->mass_global_permissions_cache[$iass_id][$user_id][$operation];
    }

    /**
     * Check whether user is system admin.
     *
     * @return bool
     */
    public function isSystemAdmin()
    {
        return $this->review->isAssigned($this->usr->getId(), SYSTEM_ROLE_ID);
    }
}
