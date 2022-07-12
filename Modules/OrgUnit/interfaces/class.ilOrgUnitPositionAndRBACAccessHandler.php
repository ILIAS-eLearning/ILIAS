<?php

/**
 * Interface ilOrgUnitPositionAndRBACAccessHandler
 * Provides access checks due to a users OrgUnit-Positions in Combination with RBAC
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilOrgUnitPositionAndRBACAccessHandler
{

    /**
     * @param string $rbac_perm
     * @param string $pos_perm           See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     * @param int    $ref_id             Reference-ID of the desired Object in the tree
     * @return bool
     */
    public function checkRbacOrPositionPermissionAccess(string $rbac_perm, string $pos_perm, int $ref_id) : bool;

    /**
     * @param string $rbac_perm
     * @param string $pos_perm           See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     * @param int    $ref_id             Reference-ID of the desired Object in the tree
     * @param int[]  $user_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(string $rbac_perm, string $pos_perm, int $ref_id, array $user_ids) : array;

    public function hasUserRBACorAnyPositionAccess(string $rbac_perm, int $ref_id) : bool;
}
