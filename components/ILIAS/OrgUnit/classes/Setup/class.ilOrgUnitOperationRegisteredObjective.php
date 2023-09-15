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
 ********************************************************************
 */

use ILIAS\Setup;
use ILIAS\Setup\Environment;

class ilOrgUnitOperationRegisteredObjective implements Setup\Objective
{
    protected string $operation_name;
    protected string $description;
    protected string $context;

    public function __construct(
        string $operation_name,
        string $description,
        string $context = ilOrgUnitOperationContext::CONTEXT_OBJECT
    ) {
        $this->operation_name = $operation_name;
        $this->description = $description;
        $this->context = $context;
    }

    public function getHash(): string
    {
        return hash('sha256', self::class . '::' . $this->operation_name);
    }

    public function getLabel(): string
    {
        return 'Add OrgUnit operation (name=' . $this->operation_name .
            ';context=' . $this->context . ')';
    }

    public function isNotable(): bool
    {
        return true;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        // abort if context does not exist, just to be safe
        if (!($context_id = $this->getContextId($db, $this->context))) {
            throw new Exception(
                'Context ' . $this->context . ' does not exists,
                 this objective should not be applied!'
            );
        }

        // abort if operation already exists in this context, just to be safe
        if ($this->doesOperationExistInContext(
            $db,
            $context_id,
            $this->operation_name
        )) {
            return $environment;
        }

        $id = $db->nextId('il_orgu_operations');
        $db->insert('il_orgu_operations', [
            'operation_id' => ['integer', $id],
            'operation_string' => ['text', $this->operation_name],
            'description' => ['text', $this->description],
            'list_order' => ['integer', 0],
            'context_id' => ['integer', $context_id],
        ]);

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        // something is wrong if context does not exist
        if (!($context_id = $this->getContextId($db, $this->context))) {
            throw new Setup\UnachievableException(
                'Cannot find context ' . $this->context
            );
        }

        // not applicable if operation already exists in this context
        if ($this->doesOperationExistInContext(
            $db,
            $context_id,
            $this->operation_name
        )) {
            return false;
        }

        return true;
    }

    protected function doesOperationExistInContext(
        ilDBInterface $db,
        int $context_id,
        string $operation
    ): bool {
        $result = $db->query('SELECT * FROM il_orgu_operations
            WHERE context_id = ' . $db->quote($context_id, 'integer') .
            ' AND operation_string = ' . $db->quote($operation, 'text'));
        if ($result->numRows()) {
            return true;
        }
        return false;
    }

    /**
     * Defaults to 0 if context is not found
     */
    protected function getContextId(
        ilDBInterface $db,
        string $context
    ): int {
        $result = $db->query('SELECT id FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($context, 'text'));
        if (!($row = $result->fetchObject())) {
            return 0;
        }
        return (int) $row->id;
    }
}
