<?php

/**
 * Interface ilOrgUnitPositionAndRBACAccessHandler
 *
 * Provides access checks due to a users OrgUnit-Positions in Combination with RBAC
 *
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
     *
     * @return bool
     */
    public function checkRbacOrPositionPermissionAccess($rbac_perm, $pos_perm, $ref_id);


    /**
     * @param string $rbac_perm
     * @param string $pos_perm           See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     * @param int    $ref_id             Reference-ID of the desired Object in the tree
     * @param int[]  $user_ids
     *
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser($rbac_perm, $pos_perm, $ref_id, array $user_ids);


    /**
     * @param string $rbac_perm
     *
     * @param int $ref_id
     *
     * @return bool
     */
    public function hasUserRBACorAnyPositionAccess($rbac_perm, $ref_id);
}
