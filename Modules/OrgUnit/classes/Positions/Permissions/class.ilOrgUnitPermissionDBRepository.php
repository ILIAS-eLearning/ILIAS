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

use JetBrains\PhpStorm\NoReturn;

class ilOrgUnitPermissionDBRepository implements OrgUnitPermissionRepository
{
    public const TABLE_NAME = 'il_orgu_permissions';
    protected ilOrgUnitOperationContextDBRepository $contextRepo;
    protected ilDBInterface $db;
    protected ilOrgUnitOperationDBRepository $operationRepo;

    public function __construct(ilDBInterface $db, ilOrgUnitOperationDBRepository $operationRepo, ilOrgUnitOperationContextDBRepository $contextRepo)
    {
        $this->db = $db;
        $this->operationRepo = $operationRepo;
        $this->contextRepo = $contextRepo;
    }

    public function get(int $parent_id, int $position_id): ilOrgUnitPermission
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }
        if ($parent_id <= 0) {
            throw new ilException('$parent_id cannot be <=0');
        }

        $context = $this->contextRepo->getByRefId($parent_id);
        if (!$context) {
            throw new ilException('Context for ref_id ' . $parent_id . ' not found');
        }

        if (!$this->isContextEnabled($context->getContext())) {
            throw new ilPositionPermissionsNotActive(
                "Position-related permissions not active in {$context->getContext()}",
                $context->getContext()
            );
        }

        $permission = $this->find($parent_id, $position_id);
        if ($permission) {
            return $permission;
        }

        $template = $this->getDefaultForContext($context->getContext(), $position_id);
        $permission = (new ilOrgUnitPermission())
            ->withParentId($parent_id)
            ->withContextId($context->getId())
            ->withPositionId($position_id)
            ->withOperations($template->getOperations())
            ->withProtected(false);
        $permission = $this->store($permission);

        return $permission;
    }

    public function find(int $parent_id, int $position_id): ?ilOrgUnitPermission
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }
        if ($parent_id <= 0) {
            throw new ilException('$parent_id cannot be <=0');
        }

        $context = $this->contextRepo->getByRefId($parent_id);
        if (!$context) {
            return null;
        }

        if (!$this->isContextEnabled($context->getContext())) {
            return null;
        }

        $query = 'SELECT id, parent_id, context_id, position_id, protected, operations FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.parent_id = ' . $this->db->quote($parent_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.context_id = ' . $this->db->quote($context->getId(), 'integer') . PHP_EOL;
        $res = $this->db->query($query);
        if ($res->numRows() === 0) {
            return null;
        }

        $rec = $this->db->fetchAssoc($res);
        $ret = (new ilOrgUnitPermission((int) $rec['id']))
            ->withParentId((int) $rec["parent_id"])
            ->withContextId((int) $rec['context_id'])
            ->withPositionId((int) $rec['position_id'])
            ->withProtected((bool) $rec['protected'])
            ->withOperations($this->convertToArray((string) $rec["operations"]));

        $ret = $this->update($ret);
        return $ret;
    }

    public function store(ilOrgUnitPermission $permission): ilOrgUnitPermission
    {
        if ($permission->getId() === 0) {
            $permission = $this->insertDB($permission);
        } else {
            if ($permission->isProtected()) {
                throw new ilException("Protected permission " . $permission->getId() . " can not be updated");
            }
            if ($permission->getParentId() == ilOrgUnitPermission::PARENT_TEMPLATE) {
                $permission = $permission->withProtected(true);
            }
            $this->updateDB($permission);
        }

        $permission = $this->update($permission);
        return $permission;
    }

    private function insertDB(ilOrgUnitPermission $permission): ilOrgUnitPermission
    {
        $id = $this->db->nextId(self::TABLE_NAME);

        $values = [
            'id' => [ 'integer', $id ],
            'parent_id' => [ 'string', $permission->getParentId()],
            'context_id' => [ 'string', $permission->getContextId()],
            'position_id' => [ 'integer', $permission->getPositionId() ],
            'protected' => [ 'integer',  ($permission->isProtected()) ? 1 : 0],
            'operations' => [ 'string', $this->convertToJson($permission->getOperations())]
        ];

        $this->db->insert(self::TABLE_NAME, $values);

        return (new ilOrgUnitPermission($id))
            ->withParentId($permission->getParentId())
            ->withContextId($permission->getContextId())
            ->withPositionId($permission->getPositionId())
            ->withProtected($permission->isProtected())
            ->withOperations($permission->getOperations());
    }

    private function updateDB(ilOrgUnitPermission $permission): void
    {
        $where = [ 'id' => [ 'integer', $permission->getId() ] ];

        $values = [
            'parent_id' => [ 'string', $permission->getParentId()],
            'context_id' => [ 'string', $permission->getContextId()],
            'position_id' => [ 'integer', $permission->getPositionId() ],
            'protected' => [ 'integer',  ($permission->isProtected()) ? 1 : 0],
            'operations' => [ 'string', $this->convertToJson($permission->getOperations())]
        ];

        $this->db->update(self::TABLE_NAME, $values, $where);
    }

    public function delete(int $parent_id, int $position_id): bool
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }
        if ($parent_id <= 0) {
            throw new ilException('$parent_id cannot be <=0');
        }

        $context = $this->contextRepo->getByRefId($parent_id);
        if (!$context) {
            throw new ilException('Context for ref_id ' . $parent_id . ' not found');
        }

        if (!$this->isContextEnabled($context->getContext())) {
            throw new ilPositionPermissionsNotActive(
                "Position-related permissions not active in {$context->getContext()}",
                $context->getContext()
            );
        }

        $permission = $this->find($parent_id, $position_id);
        if ($permission) {
            $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
                . ' WHERE id = ' . $this->db->quote($permission->getId(), 'integer');
            $this->db->manipulate($query);

            return true;
        }

        return false;
    }

    // TODO: Check if public use in GUI is still necessary, otherwise make this private
    public function update(ilOrgUnitPermission $permission): ilOrgUnitPermission
    {
        $permission = $permission->withPossibleOperations(
            $this->operationRepo->getOperationsByContextId($permission->getContextId())
        );
        $permission = $permission->withOperations(
            is_array($permission->getOperations()) ? $permission->getOperations() : []
        );
        $selected_operation_ids = [];
        foreach ($permission->getOperations() as $operation) {
            $selected_operation_ids[] = $operation->getOperationId();
        }
        $permission = $permission->withSelectedOperationIds($selected_operation_ids);
        $permission = $permission->withContext(
            $this->contextRepo->getById($permission->getContextId())
        );

        return $permission;
    }

    public function getLocalorDefault(int $parent_id, int $position_id): ilOrgUnitPermission
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }

        $context = $this->contextRepo->getByRefId($parent_id);
        if (!$context) {
            throw new ilException('Context for ref_id ' . $parent_id . ' not found');
        }

        if (!$this->isContextEnabled($context->getContext())) {
            throw new ilPositionPermissionsNotActive(
                "Position-related permissions not active in {$context->getContext()}",
                $context->getContext()
            );
        }

        $permission = $this->find($parent_id, $position_id);
        if ($permission) {
            return $permission;
        }

        return $this->getDefaultForContext($context->getContext(), $position_id);
    }

    public function getDefaultForContext(string $context_name, int $position_id, bool $editable = false): ilOrgUnitPermission
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }

        $context = $this->contextRepo->find($context_name);
        if (!$context) {
            throw new ilException('Context ' . $context_name . ' not found');
        }

        $template = false;
        $query = 'SELECT id, parent_id, context_id, position_id, protected, operations FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.parent_id = ' . $this->db->quote(ilOrgUnitPermission::PARENT_TEMPLATE, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.context_id = ' . $this->db->quote($context->getId(), 'integer') . PHP_EOL;
        $res = $this->db->query($query);
        if ($res->numRows() > 0) {
            $rec = $this->db->fetchAssoc($res);
            $template = (new ilOrgUnitPermission((int) $rec['id']))
                ->withParentId((int) $rec["parent_id"])
                ->withContextId((int) $rec['context_id'])
                ->withPositionId((int) $rec['position_id'])
                ->withProtected((bool) $rec['protected'])
                ->withOperations($this->convertToArray((string) $rec["operations"]));
            $template = $this->update($template);
        }

        if (!$template) {
            $template = (new ilOrgUnitPermission())
                ->withParentId(ilOrgUnitPermission::PARENT_TEMPLATE)
                ->withContextId($context->getId())
                ->withPositionId($position_id)
                ->withProtected(true);
            $template = $this->store($template);
        }

        $template = $template->withProtected(!$editable);
        $template = $this->update($template);

        return $template;
    }

    public function getDefaultsForActiveContexts(int $position_id, bool $editable = false): array
    {
        $active_contexts = [];
        foreach (ilOrgUnitGlobalSettings::getInstance()->getPositionSettings() as $ilOrgUnitObjectPositionSetting) {
            if ($ilOrgUnitObjectPositionSetting->isActive()) {
                $active_contexts[] = $ilOrgUnitObjectPositionSetting->getType();
            }
        }

        $permissions = [];
        foreach ($active_contexts as $context) {
            $permissions[] = $this->getDefaultForContext($context, $position_id, $editable);
        }

        return $permissions;
    }

    private function isContextEnabled(string $context): bool
    {
        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context);
        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            return false;
        }

        return true;
    }

    /**
     * This will be replaced in a future update
     * including a migration for existing db entries
     */
    private function convertToArray(string $operations)
    {
        $ids = json_decode($operations);
        $ret = [];
        foreach ($ids as $operation_id) {
            $ret[] = $this->operationRepo->getById($operation_id);
        }
        return $ret;
    }

    /**
     * This will be replaced in a future update
     * including a migration for existing db entries
     */
    private function convertToJson(array $operations)
    {
        $ids = [];
        foreach ($operations as $operation) {
            $ids[] = $operation->getOperationId();
        }
        return json_encode($ids);
    }
}
