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
     * @throws ilException
     */
    public function registerNewContext(string $context, ?string $parent_context): void;

    public function store(ilOrgUnitOperationContext $operation_context): ilOrgUnitOperationContext;

    public function delete(int $id): void;

    public function find(string $context, int $parent_context_id): ?ilOrgUnitOperationContext;

    public function findContextById(int $id): ?ilOrgUnitOperationContext;

    public function findContextByName(string $context): ?ilOrgUnitOperationContext;

    public function findContextByRefId(int $ref_id): ?ilOrgUnitOperationContext;

    public function findContextByObjId(int $obj_id): ?ilOrgUnitOperationContext;
}
