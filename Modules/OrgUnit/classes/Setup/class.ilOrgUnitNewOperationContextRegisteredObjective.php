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

class ilOrgUnitNewOperationContextRegisteredObjective implements Setup\Objective
{
    protected string $context_name;
    protected ?string $parent_context;

    public function __construct(
        string $context_name,
        ?string $parent_context = null
    ) {
        $this->context_name = $context_name;
        $this->parent_context = $parent_context;
    }

    public function getHash(): string
    {
        return hash('sha256', self::class . '::' . $this->context_name);
    }

    public function getLabel(): string
    {
        return 'Add OrgUnit operation context (name=' . $this->context_name .
            ';parent_context=' . $this->parent_context . ')';
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

        // abort if the context already exists
        $result = $db->query('SELECT * FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($this->context_name, 'text'));
        if ($result->numRows()) {
            return $environment;
        }

        $parent_context_id = 0;
        if (isset($this->parent_context)) {
            // abort if the parent context does not exist
            $result = $db->query('SELECT id FROM il_orgu_op_contexts
                WHERE context = ' . $db->quote($this->parent_context, 'text'));
            if (!($row = $result->fetchObject())) {
                return $environment;
            }
            $parent_context_id = (int) $row->id;
        }

        $id = $db->nextId('il_orgu_op_contexts');
        $db->insert('il_orgu_op_contexts', [
            'id' => ['integer', $id],
            'context' => ['text', $this->context_name],
            'parent_context_id' => ['integer', $parent_context_id]
        ]);

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        // not applicable if the context already exists
        $result = $db->query('SELECT * FROM il_orgu_op_contexts
            WHERE context = ' . $db->quote($this->context_name, 'text'));
        if ($result->numRows()) {
            return false;
        }

        if (isset($this->parent_context)) {
            // not applicable if the parent context does not exist
            $result = $db->query('SELECT * FROM il_orgu_op_contexts
                WHERE context = ' . $db->quote($this->parent_context, 'text'));
            if (!$result->numRows()) {
                return false;
            }
        }

        return true;
    }
}
