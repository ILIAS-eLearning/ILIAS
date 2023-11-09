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
declare(strict_types=1);

interface OrgUnitPermissionRepository
{
    /**
     * Get local permission for parent and position
     * If no permission exists yet, it is created from the default setting
     *
     * @throws ilException
     * @throws ilPositionPermissionsNotActive
     */
    public function get(int $parent_id, int $position_id): ilOrgUnitPermission;

    /**
     * Find local permission for parent and position
     * Does not create new local permissions, returns null if no local permission exists
     */
    public function find(int $parent_id, int $position_id): ?ilOrgUnitPermission;

    /**
     * Store permission to db
     * Returns permission with updated fields (see update())
     */
    public function store(ilOrgUnitPermission $permission): ilOrgUnitPermission;

    /**
     * Delete local permission for parent and position
     * Returns false if no local permission exists
     *
     * @throws ilException
     * @throws ilPositionPermissionsNotActive
     */
    public function delete(int $parent_id, int $position_id): bool;

    /**
     * Update/refresh the additional fields of the permssion object (e.g. available operations)
     *
     * This is done via the repository cause it also needs data from the operations/context repositories
     * Ideally, this should be private use only but is still needed as public in the current version
     */
    public function update(ilOrgUnitPermission $permission): ilOrgUnitPermission;

    /**
     * Get an existing local permission. If a local permission does not exist,
     * return a protected default setting (if permissions are enabled for the context of the parent_id)
     *
     * @throws ilException
     * @throws ilPositionPermissionsNotActive
     */
    public function getLocalorDefault(int $parent_id, int $position_id): ilOrgUnitPermission;

    /**
     * Get the default setting for a specified context
     * If the setting does not exist, it is created (if permissions are enabled for this context)
     */
    public function getDefaultForContext(string $context_name, int $position_id, bool $editable = false): ilOrgUnitPermission;

    /**
    * Get an array of default settings for all active contexts
    * If the settings don't exist yet, they will be created (if permissions are enabled for these contexts)
    *
    * @return array ilOrgUnitPermission[]
    */
    public function getDefaultsForActiveContexts(int $position_id, bool $editable = false): array;
}
