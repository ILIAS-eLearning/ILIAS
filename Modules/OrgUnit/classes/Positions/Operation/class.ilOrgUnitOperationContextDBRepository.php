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

class ilOrgUnitOperationContextDBRepository implements OrgUnitOperationContextRepository
{
    public const TABLE_NAME = 'il_orgu_op_contexts';
    protected ilDBInterface $db;


    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function get(string $context, ?string $parent_context): ilOrgUnitOperationContext
    {
        $found_context = $this->find($context);
        if ($found_context) {
            return $found_context;
        }

        $parent_id = 0;
        if ($parent_context !== null) {
            $parent = $this->find($parent_context);
            if (!$parent) {
                throw new ilException("Parent context not found");
            }
            $parent_id = $parent->getId();
        }

        $context = (new ilOrgUnitOperationContext())
            ->withContext($context)
            ->withParentContextId($parent_id);
        $this->store($context);

        $context = $context
            ->withPathNames([$context->getContext()])
            ->withPathIds([$context->getId()]);
        $context = $this->appendPath($context);

        return $context;
    }


    public function store(ilOrgUnitOperationContext $operation_context): ilOrgUnitOperationContext
    {
        if ($operation_context->getId() === 0) {
            $operation_context = $this->insert($operation_context);
        } else {
            $this->update($operation_context);
            $operation_context = $operation_context
                ->withPathNames([$operation_context->getContext()])
                ->withPathIds([$operation_context->getId()]);
            $operation_context = $this->appendPath($operation_context);
        }

        return $operation_context;
    }

    private function insert(ilOrgUnitOperationContext $operation_context): ilOrgUnitOperationContext
    {
        $id = $this->db->nextId(self::TABLE_NAME);

        $values = [
            'id' => [ 'integer', $id ],
            'context' => [ 'string', $operation_context->getContext() ],
            'parent_context_id' => [ 'integer', $operation_context->getParentContextId() ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        $ret = (new ilOrgUnitOperationContext($id))
            ->withContext($operation_context->getContext())
            ->withParentContextId($operation_context->getParentContextId())
            ->withPathNames([$operation_context->getContext()])
            ->withPathIds([$id]);
        $ret = $this->appendPath($ret);

        return $ret;
    }

    private function update(ilOrgUnitOperationContext $operation_context): void
    {
        $where = [ 'id' => [ 'integer', $operation_context->getId() ] ];

        $values = [
            'context' => [ 'integer', $operation_context->getContext() ],
            'parent_context_id' => [ 'integer', $operation_context->getParentContextId() ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(string $context): bool
    {
        $operation_context = $this->find($context);
        if (!$operation_context) {
            return false;
        }

        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
                . ' WHERE id = ' . $this->db->quote($operation_context->getId(), 'integer');
        $rows = $this->db->manipulate($query);
        if ($rows > 0) {
            return true;
        }

        return false;
    }

    public function find(string $context): ?ilOrgUnitOperationContext
    {
        $query = 'SELECT id, context, parent_context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.context = ' . $this->db->quote($context, 'string');
        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        $operation_context = (new ilOrgUnitOperationContext((int) $rec['id']))
            ->withContext((string) $rec['context'])
            ->withParentContextId((int) $rec['parent_context_id'])
            ->withPathNames([(string) $rec['context']])
            ->withPathIds([(int) $rec['id']]);
        $operation_context = $this->appendPath($operation_context);

        return $operation_context;
    }

    public function getById(int $id): ?ilOrgUnitOperationContext
    {
        $query = 'SELECT id, context, parent_context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.id = ' . $this->db->quote($id, 'integer');
        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        $operation_context = (new ilOrgUnitOperationContext((int) $rec['id']))
            ->withContext((string) $rec['context'])
            ->withParentContextId((int) $rec['parent_context_id'])
            ->withPathNames([(string) $rec['context']])
            ->withPathIds([(int) $rec['id']]);
        $operation_context = $this->appendPath($operation_context);

        return $operation_context;
    }

    public function getByRefId(int $ref_id): ?ilOrgUnitOperationContext
    {
        $type_context = ilObject2::_lookupType($ref_id, true);
        return $this->find($type_context);
    }

    public function getByObjId(int $obj_id): ?ilOrgUnitOperationContext
    {
        $type_context = ilObject2::_lookupType($obj_id, false);
        return $this->find($type_context);
    }


    private function appendPath(ilOrgUnitOperationContext $operation_context, int $next = null): ilOrgUnitOperationContext
    {
        $parent_context_id = ($next >= 0) ? $next : $operation_context->getParentContextId();
        if ($parent_context_id > 0) {
            $parent = $this->getById($parent_context_id);
            if ($parent) {
                $path_names = $operation_context->getPathNames();
                $path_names[] = $parent->getContext();
                $path_ids = $operation_context->getPathIds();
                $path_ids[] = $parent->getId();

                $operation_context = $operation_context
                    ->withPathNames($path_names)
                    ->withPathIds($path_ids);

                $operation_context = $this->appendPath($operation_context, $parent->getId());
            }
        }
        return $operation_context;
    }
}
