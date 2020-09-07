<?php

/**
 * Class ilOrgUnitPositionAccess
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionAccess implements ilOrgUnitPositionAccessHandler, ilOrgUnitPositionAndRBACAccessHandler
{

    /**
     * @var \ilOrgUnitUserAssignmentQueries
     */
    protected $ua;
    /**
     * @var \ilOrgUnitGlobalSettings
     */
    protected $set;
    /**
     * @var array
     */
    protected static $ref_id_obj_type_map = array();


    /**
     * ilOrgUnitPositionAccess constructor.
     */
    public function __construct()
    {
        $this->set = ilOrgUnitGlobalSettings::getInstance();
        $this->ua = ilOrgUnitUserAssignmentQueries::getInstance();
    }


    /**
     * @inheritdoc
     */
    public function filterUserIdsForCurrentUsersPositionsAndPermission(array $user_ids, $permission)
    {
        $current_user_id = $this->getCurrentUsersId();

        return $this->filterUserIdsForUsersPositionsAndPermission($user_ids, $current_user_id, $permission);
    }


    /**
     * @inheritdoc
     */
    public function filterUserIdsForUsersPositionsAndPermission(array $user_ids, $for_user_id, $permission)
    {
        // FSX TODO no permission is checked or existing
        $assignment_of_user = $this->ua->getAssignmentsOfUserId($for_user_id);
        $other_users_in_same_org_units = [];
        foreach ($assignment_of_user as $assignment) {
            $other_users_in_same_org_units = $other_users_in_same_org_units + $this->ua->getUserIdsOfOrgUnit($assignment->getOrguId());
        }

        return array_intersect($user_ids, $other_users_in_same_org_units);
    }


    /**
     * @inheritdoc
     */
    public function isCurrentUserBasedOnPositionsAllowedTo($permission, array $on_user_ids)
    {
        $current_user_id = $this->getCurrentUsersId();

        return $this->isUserBasedOnPositionsAllowedTo($current_user_id, $permission, $on_user_ids);
    }


    /**
     * @inheritdoc
     */
    public function isUserBasedOnPositionsAllowedTo($which_user_id, $permission, array $on_user_ids)
    {
        $filtered_user_ids = $this->filterUserIdsForUsersPositionsAndPermission($on_user_ids, $which_user_id, $permission);

        return ($on_user_ids === array_intersect($on_user_ids, $filtered_user_ids)
            && $filtered_user_ids === array_intersect($filtered_user_ids, $on_user_ids));
    }


    /**
     * @inheritdoc
     */
    public function filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, array $user_ids)
    {
        // If context is not activated, return same array of $user_ids
        if (!$this->set->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))->isActive()) {
            return $user_ids;
        }

        $current_user_id = $this->getCurrentUsersId();

        return $this->filterUserIdsByPositionOfUser($current_user_id, $pos_perm, $ref_id, $user_ids);
    }


    /**
     * @inheritdoc
     */
    public function filterUserIdsByPositionOfUser($user_id, $pos_perm, $ref_id, array $user_ids)
    {
        // If context is not activated, return same array of $user_ids
        if (!$this->set->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))->isActive()) {
            return $user_ids;
        }

        // $all_available_users = $this->ua->getUserIdsOfOrgUnit()
        $operation = ilOrgUnitOperationQueries::findByOperationString($pos_perm, $this->getTypeForRefId($ref_id));
        if (!$operation) {
            return $user_ids;
        }

        $allowed_user_ids = [];
        foreach ($this->ua->getPositionsOfUserId($user_id) as $position) {
            $permissions = ilOrgUnitPermissionQueries::getSetForRefId($ref_id, $position->getId());
            if (!$permissions->isOperationIdSelected($operation->getOperationId())) {
                continue;
            }

            foreach ($position->getAuthorities() as $authority) {
                switch ($authority->getOver()) {
                    case ilOrgUnitAuthority::OVER_EVERYONE:
                        switch ($authority->getScope()) {
                            case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
                                $allowed = $this->ua->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id);
                                $allowed_user_ids = $allowed_user_ids + $allowed;
                                break;
                            case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
                                $allowed = $this->ua->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id, true);
                                $allowed_user_ids = $allowed_user_ids + $allowed;
                                break;
                        }
                        break;
                    default:
                        switch ($authority->getScope()) {
                            case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
                                $allowed = $this->ua->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver());
                                $allowed_user_ids = $allowed_user_ids + $allowed;
                                break;
                            case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
                                $allowed = $this->ua->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(), $authority->getOver(), true);
                                $allowed_user_ids = $allowed_user_ids + $allowed;
                                break;
                        }
                        break;
                }
            }
        }

        return array_intersect($user_ids, $allowed_user_ids);
    }


    /**
     * @inheritdoc
     */
    public function checkPositionAccess($pos_perm, $ref_id)
    {
        // If context is not activated, return same array of $user_ids
        if (!$this->isPositionActiveForRefId($ref_id)) {
            return false;
        }

        $operation = ilOrgUnitOperationQueries::findByOperationString($pos_perm, $this->getTypeForRefId($ref_id));
        if (!$operation) {
            return false;
        }
        $current_user_id = $this->getCurrentUsersId();

        foreach ($this->ua->getPositionsOfUserId($current_user_id) as $position) {
            $permissions = ilOrgUnitPermissionQueries::getSetForRefId($ref_id, $position->getId());
            if ($permissions->isOperationIdSelected($operation->getOperationId())) {
                return true;
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     */
    public function hasCurrentUserAnyPositionAccess($ref_id)
    {
        // If context is not activated, return same array of $user_ids
        if (!$this->isPositionActiveForRefId($ref_id)) {
            return false;
        }

        $current_user_id = $this->getCurrentUsersId();

        foreach ($this->ua->getPositionsOfUserId($current_user_id) as $position) {
            $permissions = ilOrgUnitPermissionQueries::getSetForRefId($ref_id, $position->getId());
            if (count($permissions->getOperations()) > 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     */
    public function checkRbacOrPositionPermissionAccess($rbac_perm, $pos_perm, $ref_id)
    {
        global $DIC;
        // If RBAC allows, just return true
        if ($DIC->access()->checkAccess($rbac_perm, '', $ref_id)) {
            return true;
        }

        // If context is not activated, return same array of $user_ids
        if (!$this->isPositionActiveForRefId($ref_id)) {
            return false;
        }

        return $this->checkPositionAccess($pos_perm, $ref_id);
    }


    /**
     * @inheritdoc
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser($rbac_perm, $pos_perm, $ref_id, array $user_ids)
    {
        global $DIC;
        // If RBAC allows, just return true
        if ($DIC->access()->checkAccess($rbac_perm, '', $ref_id)) {
            return $user_ids;
        }
        // If context is not activated, return same array of $user_ids
        if (!$this->isPositionActiveForRefId($ref_id)) {
            return $user_ids;
        }

        return $this->filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, $user_ids);
    }


    /**
     * @inheritdoc
     */
    public function hasUserRBACorAnyPositionAccess($rbac_perm, $ref_id)
    {
        global $DIC;
        if ($DIC->access()->checkAccess($rbac_perm, '', $ref_id)) {
            return true;
        }

        return $this->hasCurrentUserAnyPositionAccess($ref_id);
    }


    //
    // Helpers
    //

    /**
     * @return \ILIAS\DI\Container
     */
    private function dic()
    {
        return $GLOBALS['DIC'];
    }


    /**
     * @return int
     */
    private function getCurrentUsersId()
    {
        return $this->dic()->user()->getId();
    }


    /**
     * @param $ref_id
     *
     * @return mixed
     */
    private function getTypeForRefId($ref_id)
    {
        if (!isset(self::$ref_id_obj_type_map[$ref_id])) {
            self::$ref_id_obj_type_map[$ref_id] = ilObject2::_lookupType($ref_id, true);
        }

        return self::$ref_id_obj_type_map[$ref_id];
    }


    /**
     * @param $ref_id
     *
     * @return int
     */
    private function getObjIdForRefId($ref_id)
    {
        return ilObject2::_lookupObjectId($ref_id);
    }


    /**
     * @param $ref_id
     *
     * @return bool
     */
    private function isPositionActiveForRefId($ref_id)
    {
        $obj_id = $this->getObjIdForRefId($ref_id); // TODO this will change to ref_id!!

        return $this->set->isPositionAccessActiveForObject($obj_id);
    }
}
