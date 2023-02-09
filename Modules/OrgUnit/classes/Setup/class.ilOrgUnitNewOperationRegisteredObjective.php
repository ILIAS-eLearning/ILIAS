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
use ILIAS\DI;

class ilOrgUnitNewOperationRegisteredObjective implements Setup\Objective
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

        // abort if context does not exist
        $result = $db->query('SELECT id FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($this->context, 'text'));
        if (!($row = $result->fetchObject())) {
            return $environment;
        }
        $context_id = (int) $row->id;

        // abort if operation already exists in this context
        $result = $db->query('SELECT * FROM il_orgu_operations
            WHERE context_id = ' . $db->quote($context_id, 'integer') .
            ' AND operation_string = ' . $db->quote($this->operation_name, 'text'));
        if ($result->numRows()) {
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

        // not applicable if context does not exist
        $result = $db->query('SELECT id FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($this->context, 'text'));
        if (!($row = $result->fetchObject())) {
            return false;
        }
        $context_id = (int) $row->id;

        // not applicable if operation already exists in this context
        $result = $db->query('SELECT * FROM il_orgu_operations
            WHERE context_id = ' . $db->quote($context_id, 'integer') .
            ' AND operation_string = ' . $db->quote($this->operation_name, 'text'));
        if ($result->numRows()) {
            return false;
        }

        return true;
    }
}
