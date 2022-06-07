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
 ********************************************************************
 */

/**
 * Class ilOrgUnitPositionAccess
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionAccess implements ilOrgUnitPositionAccessHandler, ilOrgUnitPositionAndRBACAccessHandler
{
    protected static array $ref_id_obj_type_map = array();
    private \ilOrgUnitUserAssignmentQueries $ua;
    private \ilOrgUnitGlobalSettings $set;
    private ilAccess $access;
    private ilObjUser $user;

    public function __construct(ilAccess $access)
    {
        global $DIC;
        $this->set = ilOrgUnitGlobalSettings::getInstance();
        $this->ua = ilOrgUnitUserAssignmentQueries::getInstance();
        $this->access = $access;
        $this->user = $DIC->user();
    }


    /** @return int[] Filtered List of ILIAS-User-IDs */
    public function filterUserIdsForCurrentUsersPositionsAndPermission(
        array $user_ids,
        string $permission
    ) : array {
        $current_user_id = $this->getCurrentUsersId();
        return $this->filterUserIdsForUsersPositionsAndPermission($user_ids, $current_user_id, $permission);
    }



    /** @return int[] Filtered List of ILIAS-User-IDs */
    public function filterUserIdsForUsersPositionsAndPermission(
        array $user_ids,
        int $for_user_id,
        string $permission
    ) : array {
        $assignment_of_user = $this->ua->getAssignmentsOfUserId($for_user_id);
        $other_users_in_same_org_units = [];
        foreach ($assignment_of_user as $assignment) {
            $other_users_in_same_org_units += $this->ua->getUserIdsOfOrgUnit($assignment->getOrguId());
        }

        return array_intersect($user_ids, $other_users_in_same_org_units);
    }

    /** @param int[] $on_user_ids */
    public function isCurrentUserBasedOnPositionsAllowedTo(string $permission, array $on_user_ids) : bool
    {
        $current_user_id = $this->getCurrentUsersId();

        return $this->isUserBasedOnPositionsAllowedTo($current_user_id, $permission, $on_user_ids);
    }


    /** @param int[] $on_user_ids */
    public function isUserBasedOnPositionsAllowedTo(
        int $which_user_id,
        string $permission,
        array $on_user_ids
    ) : bool {
        $filtered_user_ids = $this->filterUserIdsForUsersPositionsAndPermission($on_user_ids, $which_user_id,
            $permission);

        return ($on_user_ids === array_intersect($on_user_ids, $filtered_user_ids)
            && $filtered_user_ids === array_intersect($filtered_user_ids, $on_user_ids));
    }


    /** @param int[] $user_ids */
    public function filterUserIdsByPositionOfCurrentUser(string $pos_perm, int $ref_id, array $user_ids) : array
    {
        // If context is not activated, return same array of $user_ids
        if (!$this->set->getObjectPositionSettingsByType($this->getTypeForRefId($ref_id))->isActive()) {
            return $user_ids;
        }

        $current_user_id = $this->getCurrentUsersId();

        return $this->filterUserIdsByPositionOfUser($current_user_id, $pos_perm, $ref_id, $user_ids);
    }


    /** @param int[] $user_ids */
    public function filterUserIdsByPositionOfUser(
        int $user_id,
        string $pos_perm,
        int $ref_id,
        array $user_ids
    ) : array {
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
                                $allowed_user_ids = array_merge($allowed_user_ids, $allowed);
                                break;
                            case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
                                $allowed = $this->ua->getUserIdsOfOrgUnitsOfUsersPosition($position->getId(), $user_id,
                                    true);
                                $allowed_user_ids = array_merge($allowed_user_ids, $allowed);
                                break;
                        }
                        break;
                    default:
                        switch ($authority->getScope()) {
                            case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
                                $allowed = $this->ua->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(),
                                    $authority->getOver());
                                $allowed_user_ids = array_merge($allowed_user_ids, $allowed);
                                break;
                            case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
                                $allowed = $this->ua->getUserIdsOfUsersOrgUnitsInPosition($user_id, $position->getId(),
                                    $authority->getOver(), true);
                                $allowed_user_ids = array_merge($allowed_user_ids, $allowed);
                                break;
                        }
                        break;
                }
            }
        }

        return array_intersect($user_ids, $allowed_user_ids);
    }


    public function checkPositionAccess(string $pos_perm, int $ref_id) : bool
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


    public function hasCurrentUserAnyPositionAccess(int $ref_id) : bool
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


    public function checkRbacOrPositionPermissionAccess(string $rbac_perm, string $pos_perm, int $ref_id) : bool
    {
        // If RBAC allows, just return true
        if ($this->access->checkAccess($rbac_perm, '', $ref_id)) {
            return true;
        }

        // If context is not activated, return same array of $user_ids
        if (!$this->isPositionActiveForRefId($ref_id)) {
            return false;
        }

        return $this->checkPositionAccess($pos_perm, $ref_id);
    }


    public function filterUserIdsByRbacOrPositionOfCurrentUser(
        string $rbac_perm,
        string $pos_perm,
        int $ref_id,
        array $user_ids
    ) : array {
        global $DIC;

        // If RBAC allows, just return true
        if ($this->access->checkAccess($rbac_perm, '', $ref_id)) {
            return $user_ids;
        }
        // If context is not activated, return same array of $user_ids
        if (!$this->isPositionActiveForRefId($ref_id)) {
            return $user_ids;
        }

        return $this->filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, $user_ids);
    }


    public function hasUserRBACorAnyPositionAccess(string $rbac_perm, int $ref_id) : bool
    {
        if ($this->access->checkAccess($rbac_perm, '', $ref_id)) {
            return true;
        }

        return $this->hasCurrentUserAnyPositionAccess($ref_id);
    }


    //
    // Helpers
    //

    private function getCurrentUsersId() : int
    {
        return $this->user->getId();
    }


    private function getTypeForRefId(int $ref_id) : string
    {
        if (!isset(self::$ref_id_obj_type_map[$ref_id])) {
            self::$ref_id_obj_type_map[$ref_id] = ilObject2::_lookupType($ref_id, true);
        }

        return self::$ref_id_obj_type_map[$ref_id];
    }

    private function getObjIdForRefId(int $ref_id) : int
    {
        return ilObject2::_lookupObjectId($ref_id);
    }
    
    private function isPositionActiveForRefId(int $ref_id) : bool
    {
        $obj_id = $this->getObjIdForRefId($ref_id); // TODO this will change to ref_id!!

        return $this->set->isPositionAccessActiveForObject($obj_id);
    }
}
