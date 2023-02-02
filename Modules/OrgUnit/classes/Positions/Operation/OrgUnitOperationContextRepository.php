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

interface OrgUnitOperationContextRepository
{
    /**
     * Get a context
     * If the context does not exist, it is created
     * @throws ilException
     */
    public function get(string $context, ?string $parent_context): ilOrgUnitOperationContext;

    /**
     * Store context to db
     */
    public function store(ilOrgUnitOperationContext $operation_context): ilOrgUnitOperationContext;

    /**
     * Delete context by name
     * Returns false, if no context is found
     */
    public function delete(string $context): bool;

    /**
     * Find an existing context
     * Returns null if no context is found
     */
    public function find(string $context): ?ilOrgUnitOperationContext;

    /**
     * Get context by id
     * Returns null if no context is found
     *
     * This is kept for backwards compatibility, but might be removed at a later date
     * @deprecated Please refer to contexts by context name
     */
    public function getById(int $id): ?ilOrgUnitOperationContext;

    /**
     * Get context by ref_id
     * Returns null if no context is found
     */
    public function getByRefId(int $ref_id): ?ilOrgUnitOperationContext;

    /**
     * Get context by obj_id
     * Returns null if no context is found
     */
    public function getByObjId(int $obj_id): ?ilOrgUnitOperationContext;
}
