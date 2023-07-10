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

class ilOrgUnitOperationDBRepository implements OrgUnitOperationRepository
{
    public const TABLE_NAME = 'il_orgu_operations';
    protected ilDBInterface $db;
    protected ilOrgUnitOperationContextDBRepository $contextRepo;


    public function __construct(ilDBInterface $db, ilOrgUnitOperationContextDBRepository $contextRepo)
    {
        $this->db = $db;
        $this->contextRepo = $contextRepo;
    }

    public function get(string $operation_string, string $description, array $contexts, int $list_order = 0): array
    {
        $operations = [];
        foreach ($contexts as $context) {
            $operation = $this->find($operation_string, $context);
            if ($operation) {
                $operations[] = $operation;
                continue;
            }

            $operation_context = $this->contextRepo->get($context, null);

            $new_operation = (new ilOrgUnitOperation())
                ->withOperationString($operation_string)
                ->withDescription($description)
                ->withListOrder($list_order)
                ->withContextId($operation_context->getId());
            $new_operation = $this->store($new_operation);

            $operations[] = $new_operation;
        }

        return $operations;
    }

    public function store(ilOrgUnitOperation $operation): ilOrgUnitOperation
    {
        if ($operation->getOperationId() === 0) {
            $operation = $this->insert($operation);
        } else {
            $this->update($operation);
        }

        return $operation;
    }

    private function insert(ilOrgUnitOperation $operation): ilOrgUnitOperation
    {
        $id = $this->db->nextId(self::TABLE_NAME);

        $values = [
            'operation_id' => [ 'integer', $id ],
            'operation_string' => [ 'string', $operation->getOperationString()],
            'description' => [ 'string', $operation->getDescription()],
            'list_order' => [ 'integer', $operation->getListOrder() ],
            'context_id' => [ 'integer', $operation->getContextId() ]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        return (new ilOrgUnitOperation($id))
            ->withOperationString($operation->getOperationString())
            ->withDescription($operation->getDescription())
            ->withListOrder($operation->getListOrder())
            ->withContextId($operation->getContextId());
    }

    private function update(ilOrgUnitOperation $operation): void
    {
        $where = [ 'operation_id' => [ 'integer', $operation->getOperationId() ] ];

        $values = [
            'operation_string' => [ 'string', $operation->getOperationString()],
            'description' => [ 'string', $operation->getDescription()],
            'list_order' => [ 'integer', $operation->getListOrder() ],
            'context_id' => [ 'integer', $operation->getContextId() ]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(ilOrgUnitOperation $operation): bool
    {
        if ($operation->getOperationId() === 0) {
            return false;
        }

        $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
            . ' WHERE operation_id = ' . $this->db->quote($operation->getOperationId(), 'integer');
        $rows = $this->db->manipulate($query);
        if ($rows > 0) {
            return true;
        }

        return false;
    }

    public function find(string $operation_string, string $context): ?ilOrgUnitOperation
    {
        $context = $this->contextRepo->find($context);
        if (!$context) {
            return null;
        }

        $query = 'SELECT operation_id, operation_string, description, list_order, context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.operation_string = ' . $this->db->quote($operation_string, 'string') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.context_id = ' . $this->db->quote($context->getId(), 'integer');

        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        return (new ilOrgUnitOperation((int) $rec['operation_id']))
            ->withOperationString((string) $rec['operation_string'])
            ->withDescription((string) $rec["description"])
            ->withListOrder((int) $rec["list_order"])
            ->withContextId((int) $rec['context_id']);
    }

    public function getById(int $operation_id): ?ilOrgUnitOperation
    {
        $query = 'SELECT operation_id, operation_string, description, list_order, context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.operation_id = ' . $this->db->quote($operation_id, 'integer');

        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        return (new ilOrgUnitOperation((int) $rec['operation_id']))
            ->withOperationString((string) $rec['operation_string'])
            ->withDescription((string) $rec["description"])
            ->withListOrder((int) $rec["list_order"])
            ->withContextId((int) $rec['context_id']);
    }

    public function getByName(string $operation_string): array
    {
        $query = 'SELECT operation_id, operation_string, description, list_order, context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.operation_string = ' . $this->db->quote($operation_string, 'string');

        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return [];
        }

        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $operation = (new ilOrgUnitOperation((int)$rec['operation_id']))
                ->withOperationString((string)$rec['operation_string'])
                ->withDescription((string)$rec["description"])
                ->withListOrder((int)$rec["list_order"])
                ->withContextId((int)$rec['context_id']);
            $ret[] = $operation;
        }

        return $ret;
    }

    public function getOperationsByContextId(int $context_id): array
    {
        $operation_context = $this->contextRepo->getById($context_id);
        if (!$operation_context) {
            throw new ilException('Context with id ' . $context_id . ' does not exist!');
        }

        $query = 'SELECT operation_id, operation_string, description, list_order, context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.context_id = ' . $this->db->quote($operation_context->getId(), 'integer');
        $res = $this->db->query($query);

        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $operation = (new ilOrgUnitOperation((int)$rec['operation_id']))
                ->withOperationString((string)$rec['operation_string'])
                ->withDescription((string)$rec["description"])
                ->withListOrder((int)$rec["list_order"])
                ->withContextId((int)$rec['context_id']);
            $ret[] = $operation;
        }

        return $ret;
    }

    public function getOperationsByContextName(string $context): array
    {
        $operation_context = $this->contextRepo->find($context);
        if (!$operation_context) {
            throw new ilException('Context ' . $context . ' does not exist!');
        }

        $query = 'SELECT operation_id, operation_string, description, list_order, context_id FROM' . PHP_EOL
            . self::TABLE_NAME
            . ' WHERE ' . self::TABLE_NAME . '.context_id = ' . $this->db->quote($operation_context->getId(), 'integer');
        $res = $this->db->query($query);

        $ret = [];
        while ($rec = $this->db->fetchAssoc($res)) {
            $operation = (new ilOrgUnitOperation((int)$rec['operation_id']))
                ->withOperationString((string)$rec['operation_string'])
                ->withDescription((string)$rec["description"])
                ->withListOrder((int)$rec["list_order"])
                ->withContextId((int)$rec['context_id']);
            $ret[] = $operation;
        }

        return $ret;
    }
}
