<?php

/**
 * Interface  ilOrgUnitPositionAccessHandler
 *
 * Provides access checks due to a users OrgUnit-Positions
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilOrgUnitPositionAccessHandler
{

    /**
     * @param int[]  $user_ids List of ILIAS-User-IDs which shall be filtered
     *
     * @param string $permission
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @throws \ilOrgUnitAccessException when a unknown permission is used. See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     *
     *
     * @return int[] Filtered List of ILIAS-User-IDs
     */
    public function filterUserIdsForCurrentUsersPositionsAndPermission(array $user_ids, $permission);


    /**
     * @param int[]  $user_ids List of ILIAS-User-IDs which shall be filtered
     * @param int    $for_user_id
     * @param string $permission
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @throws \ilOrgUnitAccessException when a unknown permission is used. See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     *
     * @return int[] Filtered List of ILIAS-User-IDs
     */
    public function filterUserIdsForUsersPositionsAndPermission(array $user_ids, $for_user_id, $permission);


    /**
     * @param string $permission
     * @param int[]  $on_user_ids List of ILIAS-User-IDs
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @return bool
     */
    public function isCurrentUserBasedOnPositionsAllowedTo($permission, array $on_user_ids);


    /**
     * @param int    $which_user_id Permission check for this ILIAS-User-ID
     * @param string $permission
     * @param int[]  $on_user_ids   List of ILIAS-User-IDs
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @return bool
     */
    public function isUserBasedOnPositionsAllowedTo($which_user_id, $permission, array $on_user_ids);


    /**
     * @param string $pos_perm
     * @param int    $ref_id Reference-ID of the desired Object in the tree
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @return bool
     */
    public function checkPositionAccess($pos_perm, $ref_id);


    /**
     * @param int $ref_id
     *
     * @return bool
     */
    public function hasCurrentUserAnyPositionAccess($ref_id);

    /**
     * @param string $pos_perm
     * @param int    $ref_id
     * @param int[]  $user_ids
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @return int[]
     */
    public function filterUserIdsByPositionOfCurrentUser($pos_perm, $ref_id, array $user_ids);


    /**
     * @param int    $user_id
     * @param string $pos_perm
     * @param int    $ref_id
     * @param int[]  $user_ids
     *
     * @see getAvailablePositionRelatedPermissions for available permissions
     *
     * @return int[]
     */
    public function filterUserIdsByPositionOfUser($user_id, $pos_perm, $ref_id, array $user_ids);
}
