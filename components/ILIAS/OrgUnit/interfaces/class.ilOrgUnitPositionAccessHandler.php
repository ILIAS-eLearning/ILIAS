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
 *********************************************************************/

/**
 * Interface  ilOrgUnitPositionAccessHandler
 * Provides access checks due to a users OrgUnit-Positions
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ilOrgUnitPositionAccessHandler
{
    /**
     * @param int[]  $user_ids           List of ILIAS-User-IDs which shall be filtered
     * @param string $permission
     * @return int[] Filtered List of ILIAS-User-IDs
     * @throws \ilOrgUnitAccessException when a unknown permission is used. See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function filterUserIdsForCurrentUsersPositionsAndPermission(array $user_ids, string $permission): array;

    /**
     * @param int[]  $user_ids           List of ILIAS-User-IDs which shall be filtered
     * @param int    $for_user_id
     * @param string $permission
     * @return int[] Filtered List of ILIAS-User-IDs
     * @throws \ilOrgUnitAccessException when a unknown permission is used. See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function filterUserIdsForUsersPositionsAndPermission(
        array $user_ids,
        int $for_user_id,
        string $permission
    ): array;

    /**
     * @param string $permission
     * @param int[]  $on_user_ids List of ILIAS-User-IDs
     * @return bool
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function isCurrentUserBasedOnPositionsAllowedTo(string $permission, array $on_user_ids): bool;

    /**
     * @param int    $which_user_id Permission check for this ILIAS-User-ID
     * @param string $permission
     * @param int[]  $on_user_ids   List of ILIAS-User-IDs
     * @return bool
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function isUserBasedOnPositionsAllowedTo(int $which_user_id, string $permission, array $on_user_ids): bool;

    /**
     * @param string $pos_perm
     * @param int    $ref_id Reference-ID of the desired Object in the tree
     * @return bool
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function checkPositionAccess(string $pos_perm, int $ref_id): bool;

    /**
     * @param int $ref_id
     * @return bool
     */
    public function hasCurrentUserAnyPositionAccess(int $ref_id): bool;

    /**
     * @param string $pos_perm
     * @param int    $ref_id
     * @param int[]  $user_ids
     * @return int[]
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function filterUserIdsByPositionOfCurrentUser(string $pos_perm, int $ref_id, array $user_ids): array;

    /**
     * @param int    $user_id
     * @param string $pos_perm
     * @param int    $ref_id
     * @param int[]  $user_ids
     * @return int[]
     * @see getAvailablePositionRelatedPermissions for available permissions
     */
    public function filterUserIdsByPositionOfUser(int $user_id, string $pos_perm, int $ref_id, array $user_ids): array;
}
