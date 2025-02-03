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
    public function checkRbacOrPositionPermissionAccess(string $rbac_perm, string $pos_perm, int $ref_id): bool;

    /**
     * @param string $rbac_perm
     * @param string $pos_perm           See the list of
     *                                   available permissions in interface
     *                                   ilOrgUnitPositionAccessHandler
     * @param int    $ref_id             Reference-ID of the desired Object in the tree
     * @param int[]  $user_ids
     * @return int[]
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(string $rbac_perm, string $pos_perm, int $ref_id, array $user_ids): array;

    public function hasUserRBACorAnyPositionAccess(string $rbac_perm, int $ref_id): bool;
}
