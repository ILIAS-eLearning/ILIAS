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
 * Interface ilRBACAccessHandler
 * Checks access for ILIAS objects
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Sascha Hofmann <saschahofmann@gmx.de>
 * @ingroup ServicesAccessControl
 */
interface ilRBACAccessHandler
{
    /**
     * store access result
     */
    public function storeAccessResult(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        bool $a_access_granted,
        ?int $a_user_id = null,
        ?ilAccessInfo $a_info = null
    ): void;

    /**
     * get stored access result
     * @param string   $a_permission permission
     * @param string   $a_cmd        command string
     * @param int      $a_ref_id     reference id
     * @param int|null $a_user_id    user id (if no id passed, current user id)
     * @return array<{granted: bool, info: ?ilAccessInfo, prevent_db_cache: bool}>
     */
    public function getStoredAccessResult(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        ?int $a_user_id = null
    ): array;

    /**
     * Set prevent caching last result.
     */
    public function setPreventCachingLastResult(bool $a_val): void;

    /**
     * Get prevent caching last result.
     */
    public function getPreventCachingLastResult(): bool;

    public function storeCache(): void;

    public function readCache(int $a_secs = 0): bool;

    public function getResults(): array;

    public function setResults(array $a_results);

    /**
     * add an info item to current info object
     */
    public function addInfoItem(string $a_type, string $a_text, string $a_data = ""): void;

    /**
     * check access for an object
     * (provide $a_type and $a_obj_id if available for better performance)
     */
    public function checkAccess(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        string $a_type = "",
        ?int $a_obj_id = null,
        ?int $a_tree_id = null
    ): bool;

    /**
     * check access for an object
     * (provide $a_type and $a_obj_id if available for better performance)
     */
    public function checkAccessOfUser(
        int $a_user_id,
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        string $a_type = "",
        ?int $a_obj_id = null,
        ?int $a_tree_id = null
    ): bool;

    /**
     * get last info object
     * @see ilAccessInfo::getInfoItems()
     */
    public function getInfo(): array;

    /**
     * get last info object
     */
    public function getResultLast(): array;

    public function getResultAll(int $a_ref_id = 0): array;

    /**
     * look if result for current query is already in cache
     * @return array<{hit: bool, granted: bool, prevent_db_cache: bool}>
     */
    public function doCacheCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id
    ): array;

    /**
     * check if object is in tree and not deleted
     */
    public function doTreeCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id
    ): bool;

    /**
     * rbac check for current object
     * -> type is used for create permission
     */
    public function doRBACCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        string $a_type
    ): bool;

    /**
     * check read permission for all parents
     */
    public function doPathCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        bool $a_all = false
    ): bool;

    /**
     * check for activation and centralized offline status.
     */
    public function doActivationCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        int $a_obj_id,
        string $a_type
    ): bool;

    /**
     * condition check (currently only implemented for read permission)
     */
    public function doConditionCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        int $a_obj_id,
        string $a_type
    ): bool;

    /**
     * object type specific check
     */
    public function doStatusCheck(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        int $a_user_id,
        int $a_obj_id,
        string $a_type
    ): bool;

    public function clear(): void;

    /**
     * @deprected
     */
    public function enable(string $a_str, bool $a_bool): void;
}
