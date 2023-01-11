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
     * @param array $contexts string[]
     */
    public function registerNewOperation(string $operation_string, string $description, array $contexts, int $list_order): void;

    public function store(ilOrgUnitOperation $operation): ilOrgUnitOperation;

    public function delete(int $id): void;

    public function findOperationById(int $operation_id): ?ilOrgUnitOperation;

    public function findOperationByName(string $operation_string): ?ilOrgUnitOperation;

    public function findOperationByNameAndContext(string $operation_string, string $context): ?ilOrgUnitOperation;

    /**
     * @return array ilOperation[]
     */
    public function findOperationsByContextId(int $context_id): array;

    /**
     * @return array ilOperation[]
     */
    public function findOperationsByContextName(string $context): array;
}
