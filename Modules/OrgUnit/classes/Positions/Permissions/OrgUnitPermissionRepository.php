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
    public function store(ilOrgUnitPermission $permission): ilOrgUnitPermission;

    /**
     * Returns a permission template for a specified context
     * If the template does not exist, it is created
     */
    public function getTemplateByContext(string $context_name, int $position_id, bool $editable = false): ilOrgUnitPermission;

    public function hasLocalPermission(int $ref_id, int $position_id): bool;

    /**
     * Returns the local permission set for a ref_id (if it exists),
     * otherwise returns a permission template for the context of ref_id
     */
    public function getPermissionByRefId(int $ref_id, int $position_id): ilOrgUnitPermission;

    /**
     * Returns the local permission set for a ref_id (if it exists),
     * otherwise creates a new local permission set
     */
    public function createPermissionByRefId(int $ref_id, int $position_id): ilOrgUnitPermission;

    public function deletePermissionByRefId(int $ref_id, int $position_id): bool;

    /**
     * Returns an array of permission templates for all active contexts
     * If the templates don't exist yet, they will be created (see getTemplateByContext)
     *
     * @return array ilOrgUnitPermission[]
     */
    public function getTemplatesForActiveContexts(int $position_id, bool $editable = false): array;

    public function updatePermission(ilOrgUnitPermission $permission): ilOrgUnitPermission;
}
