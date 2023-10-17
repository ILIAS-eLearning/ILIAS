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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MetaData\Repository\Utilities\Queries;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\RowInterface;
use ILIAS\MetaData\Repository\Dictionary\LOMDictionaryInitiator;
use ILIAS\MetaData\Repository\Utilities\Queries\Results\ResultFactoryInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\AssignmentRowInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\Action;
use ILIAS\MetaData\Repository\Utilities\Queries\Assignments\ActionAssignmentInterface;

class DatabaseQuerier implements DatabaseQuerierInterface
{
    protected ResultFactoryInterface $data_row_factory;
    protected \ilDBInterface $db;
    protected \ilLogger $logger;

    public function __construct(
        ResultFactoryInterface $data_row_factory,
        \ilDBInterface $db,
        \ilLogger $logger
    ) {
        $this->data_row_factory = $data_row_factory;
        $this->db = $db;
        $this->logger = $logger;
    }

    protected function checkTable(string $table): void
    {
        if (
            is_null($this->table($table)) ||
            is_null($this->IDName($table))
        ) {
            throw new \ilMDRepositoryException('Invalid MD table: ' . $table);
        }
    }

    protected function table(string $table): ?string
    {
        return LOMDictionaryInitiator::TABLES[$table] ?? null;
    }

    protected function IDName(string $table): ?string
    {
        return LOMDictionaryInitiator::ID_NAME[$table] ?? null;
    }

    public function manipulate(
        RessourceIDInterface $ressource_id,
        AssignmentRowInterface $row
    ): void {
        $create_assignments = [];
        $update_assignments = [];
        $delete_assignments = [];
        $delete_full_row = false;
        $create_new_row = false;

        foreach ($row->actions() as $action) {
            switch ($action->action()) {
                case Action::CREATE:
                    $create_assignments[] = $action;
                    if ($action->tag()->hasRowInTable()) {
                        $create_new_row = true;
                    }
                    break;

                case Action::UPDATE:
                    $update_assignments[] = $action;
                    break;

                case Action::DELETE:
                    $delete_assignments[] = $action;
                    if ($action->tag()->hasRowInTable()) {
                        $delete_full_row = true;
                    }
            }
        }

        if ($delete_full_row) {
            $this->delete($row->table(), $row->id());
            return;
        }
        if ($create_new_row) {
            $this->create(
                $row->table(),
                $row->id(),
                $ressource_id,
                $row->idFromParentTable(),
                ...$create_assignments
            );
            return;
        }
        $this->update(
            $row->table(),
            $row->id(),
            ...$create_assignments,
            ...$update_assignments,
            ...$delete_assignments
        );
    }

    protected function create(
        string $table,
        int $id,
        RessourceIDInterface $ressource_id,
        int $id_from_parent_table,
        ActionAssignmentInterface ...$assignments
    ): void {
        $this->checkTable($table);
        $table_name = $this->table($table);
        $has_parent = $assignments[0]->tag()->hasParent();
        $parent = $assignments[0]->tag()->parent();
        $id_field = $this->IDName($table);

        $fields = [
            $this->db->quoteIdentifier($id_field),
            'rbac_id',
            'obj_id',
            'obj_type',
        ];
        $values = [
            $this->db->quote($id, \ilDBConstants::T_INTEGER),
            $this->db->quote($ressource_id->objID(), \ilDBConstants::T_INTEGER),
            $this->db->quote($ressource_id->subID(), \ilDBConstants::T_INTEGER),
            $this->db->quote($ressource_id->type(), \ilDBConstants::T_TEXT)
        ];
        if ($has_parent) {
            $fields[] = 'parent_type';
            $values[] = $this->db->quote($parent, \ilDBConstants::T_TEXT);
            $fields[] = 'parent_id';
            $values[] = $this->db->quote($id_from_parent_table, \ilDBConstants::T_INTEGER);
        }
        foreach ($assignments as $assignment) {
            $tag = $assignment->tag();
            if ($tag->hasData() && ($data = $assignment->value()) !== '') {
                $fields[] = $this->db->quoteIdentifier($tag->dataField());
                $values[] = $this->db->quote($data, \ilDBConstants::T_TEXT);
            }
        }

        $this->db->manipulate(
            'INSERT INTO ' . $this->db->quoteIdentifier($table_name) . ' (' .
            implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')'
        );
    }

    /**
     * @return RowInterface[]
     */
    public function read(
        RessourceIDInterface $ressource_id,
        int $id_from_parent_table,
        TagInterface ...$tags
    ): \Generator {
        $table = $tags[0]->table();
        $this->checkTable($table);
        $has_parent = $tags[0]->hasParent();
        $parent = $tags[0]->parent();
        $id_field = $this->IDName($table);

        $selected_fields[] = $this->db->quoteIdentifier($id_field);
        foreach ($tags as $tag) {
            if ($tag->hasData()) {
                $selected_fields[] = $this->db->quoteIdentifier($tag->dataField());
            }
        }

        $where[] = 'rbac_id = ' . $this->db->quote($ressource_id->objID(), \ilDBConstants::T_INTEGER);
        $where[] = 'obj_id = ' . $this->db->quote($ressource_id->subID(), \ilDBConstants::T_INTEGER);
        $where[] = 'obj_type = ' . $this->db->quote($ressource_id->type(), \ilDBConstants::T_TEXT);
        if ($has_parent) {
            $where[] = 'parent_type = ' . $this->db->quote($parent, \ilDBConstants::T_TEXT);
            $where[] = 'parent_id = ' . $this->db->quote($id_from_parent_table, \ilDBConstants::T_INTEGER);
        }

        $order = 'ORDER BY ' . $this->db->quoteIdentifier($id_field);

        $result = $this->db->query(
            'SELECT ' . implode(', ', $selected_fields) . ' FROM ' .
            $this->db->quoteIdentifier($this->table($table)) . ' WHERE ' .
            implode(' AND ', $where) . ' ' . $order
        );

        while ($row = $this->db->fetchAssoc($result)) {
            $data = [];
            $id = 0;
            foreach ($row as $field => $value) {
                if ($field === $id_field) {
                    $id = $value;
                    continue;
                }
                $data[] = $this->data_row_factory->field($field, $value ?? '');
            }
            yield $this->data_row_factory->row($id, $table, ...$data);
        }
    }

    protected function update(
        string $table,
        int $id,
        ActionAssignmentInterface ...$assignments
    ): void {
        $this->checkTable($table);
        $id_field = $this->IDName($table);

        $updated_fields = [];
        foreach ($assignments as $assignment) {
            $tag = $assignment->tag();
            if (!$tag->hasData()) {
                continue;
            }
            if ($assignment->action() === Action::DELETE) {
                $updated_fields[] = $this->db->quoteIdentifier($tag->dataField()) . " = ''";
                continue;
            }
            if (($data = $assignment->value()) !== '') {
                $updated_fields[] = $this->db->quoteIdentifier($tag->dataField()) . ' = ' .
                    $this->db->quote($data, \ilDBConstants::T_TEXT);
            }
        }

        if (empty($updated_fields)) {
            return;
        }

        $this->db->manipulate(
            'UPDATE ' . $this->db->quoteIdentifier($this->table($table)) . ' SET ' .
            implode(', ', $updated_fields) . ' WHERE ' . $this->db->quoteIdentifier($id_field) .
            ' = ' . $this->db->quote($id, \ilDBConstants::T_INTEGER)
        );
    }

    protected function delete(
        string $table,
        int $id
    ): void {
        $this->checkTable($table);
        $table_name = $this->table($table);
        $id_field = $this->IDName($table);

        $this->db->manipulate(
            'DELETE FROM ' . $this->db->quoteIdentifier($table_name) . ' WHERE ' .
            $this->db->quoteIdentifier($id_field) . ' = ' .
            $this->db->quote($id, \ilDBConstants::T_INTEGER)
        );
    }

    public function deleteAll(RessourceIDInterface $ressource_id): void
    {
        $rbac_id = $ressource_id->objID();
        $obj_id = $ressource_id->subID();
        $obj_type = $ressource_id->type();
        foreach (LOMDictionaryInitiator::TABLES as $table) {
            $query = "DELETE FROM " . $this->db->quoteIdentifier($table) . " " .
                "WHERE rbac_id = " . $this->db->quote($rbac_id, \ilDBConstants::T_INTEGER) . " " .
                "AND obj_id = " . $this->db->quote($obj_id, \ilDBConstants::T_INTEGER) . " " .
                "AND obj_type = " . $this->db->quote($obj_type, \ilDBConstants::T_TEXT);

            $this->db->manipulate($query);
        }
    }

    public function nextID(string $table): int
    {
        $this->checkTable($table);
        return $this->db->nextId($this->table($table));
    }
}
