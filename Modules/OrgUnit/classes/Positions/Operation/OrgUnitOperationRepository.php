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

interface OrgUnitOperationRepository
{
    /**
     * Get an operation for each of the specified contexts
     * If the operation does not exist for one or more contexts, it is created
     * If any contexts do not exist yet, they are created as well
     *
     * @param array $contexts string[]
     * @returns array ilOrgUnitOperation[]
     */
    public function get(string $operation_string, string $description, array $contexts, int $list_order): array;

    /**
     * Store operation to db
     */
    public function store(ilOrgUnitOperation $operation): ilOrgUnitOperation;

    /**
     * Delete an operation
     * Returns false if the operation was not found
     */
    public function delete(ilOrgUnitOperation $operation): bool;

    /**
     * Find an existing operation for a specified context
     * Returns null if no operation is found
     */
    public function find(string $operation_string, string $context): ?ilOrgUnitOperation;

    /**
     * Get operation by id
     * Returns null if no operation is found
     *
     * This is only kept for backwards compatibility, but might be removed at a later date
     * @deprecated Please refer to operations by operation_string and context
     */
    public function getById(int $operation_id): ?ilOrgUnitOperation;

    /**
     * Get operation(s) by name
     *
     * @returns array ilOrgUnitOperation[]
     */
    public function getByName(string $operation_string): array;

    /**
     * Get operations by context id
     *
     * This is only kept for backwards compatibility, but might be removed at a later date
     * @deprecated Please refer to contexts by context name (see getOperationsByContextName)
     * @return array ilOrgUnitOperation[]
     */
    public function getOperationsByContextId(int $context_id): array;

    /**
     * Get operations by context name
     *
     * @return array ilOrgUnitOperation[]
     */
    public function getOperationsByContextName(string $context): array;
}
