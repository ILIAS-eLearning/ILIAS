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

class ilOrgUnitPermissionDBRepository implements OrgUnitPermissionRepository
{
    public const TABLE_NAME = 'il_orgu_permissions';
    protected ilDBInterface $db;
    protected ilOrgUnitOperationDBRepository $operationRepo;
    protected ilOrgUnitOperationContextDBRepository $contextRepo;


    public function __construct(ilDBInterface $db, ilOrgUnitOperationDBRepository $operationRepo, ilOrgUnitOperationContextDBRepository $contextRepo)
    {
        $this->db = $db;
        $this->operationRepo = $operationRepo;
        $this->contextRepo = $contextRepo;
    }

    public function store(ilOrgUnitPermission $permission): ilOrgUnitPermission
    {
        if ($permission->getId() === 0) {
            $operation = $this->insert($permission);
        } else {
            if ($permission->isProtected()) {
                throw new ilException("Protected permission " . $permission->getId() . " can not be updated");
            }
            $this->update($permission);
        }

        $permission = $this->updatePermission($permission);
        return $permission;
    }

    private function insert(ilOrgUnitPermission $permission): ilOrgUnitPermission
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

    private function update(ilOrgUnitPermission $permission): void
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

    private function delete(int $permission_id): void
    {
        if ($permission_id === 0) {
            return;
        }

        $permission = $this->findPermissionById($permission_id);
        if ($permission) {
            $query = 'DELETE FROM ' . self::TABLE_NAME . PHP_EOL
                . ' WHERE id = ' . $this->db->quote($permission_id, 'integer');
            $this->db->manipulate($query);
        }
    }

    public function getTemplateByContext(string $context_name, int $position_id, bool $editable = false): ilOrgUnitPermission
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }

        $context = $this->contextRepo->findContextByName($context_name);
        if (!$context) {
            throw new ilException('Context ' . $context_name . ' not found');
        }

        $template = $this->findPermissionByParentAndContext(
            ilOrgUnitPermission::PARENT_TEMPLATE,
            $context->getId(),
            $position_id
        );

        if (!$template) {
            $template = (new ilOrgUnitPermission())
                ->withParentId(ilOrgUnitPermission::PARENT_TEMPLATE)
                ->withContextId($context->getId())
                ->withPositionId($position_id)
                ->withNewlyCreated(true)
                ->withProtected(true);
            $template = $this->store($template);
        }

        $template = $template->withProtected(!$editable);
        $template = $this->updatePermission($template);

        return $template;
    }

    public function hasLocalPermission(int $ref_id, int $position_id): bool
    {
        if ($this->findPermissionByParent($ref_id, $position_id)) {
            return true;
        }

        return false;
    }

    public function getPermissionByRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }
        if ($ref_id === 0) {
            throw new ilException('$ref_id cannot be 0');
        }

        $context = $this->contextRepo->findContextByRefId($ref_id);
        if (!$context) {
            throw new ilException('Context for ref_id ' . $ref_id . ' not found');
        }
        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());
        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive(
                "Position-related permissions not active in {$context->getContext()}",
                $context->getContext()
            );
        }

        $permission = $this->findPermissionByParentAndContext($ref_id, $context->getId(), $position_id);
        if ($permission) {
            return $permission;
        }

        return $this->getTemplateByContext($context->getContext(), $position_id);
    }

    public function createPermissionByRefId(int $ref_id, int $position_id): ilOrgUnitPermission
    {
        $permission = $this->getPermissionByRefId($ref_id, $position_id);

        if (!$permission->isTemplate()) {
            return $permission;
        }

        $permission = $permission
            ->withParentId($ref_id)
            ->withProtected(false)
            ->withNewlyCreated(true);

        $permission = $this->store($permission);
        return $permission;
    }

    public function deletePermissionByRefId(int $ref_id, int $position_id): bool
    {
        if ($position_id === 0) {
            throw new ilException('$position_id cannot be 0');
        }
        if ($ref_id === 0) {
            throw new ilException('$ref_id cannot be 0');
        }

        $context = $this->contextRepo->findContextByRefId($ref_id);
        if (!$context) {
            throw new ilException('Context for ref_id ' . $ref_id . ' not found');
        }
        $ilOrgUnitGlobalSettings = ilOrgUnitGlobalSettings::getInstance();
        $ilOrgUnitObjectPositionSetting = $ilOrgUnitGlobalSettings->getObjectPositionSettingsByType($context->getContext());
        if (!$ilOrgUnitObjectPositionSetting->isActive()) {
            throw new ilPositionPermissionsNotActive(
                "Position-related permissions not active in {$context->getContext()}",
                $context->getContext()
            );
        }

        $permission = $this->findPermissionByParentAndContext($ref_id, $context->getId(), $position_id);
        if ($permission) {
            $this->delete($permission->getId());
            return true;
        }

        return false;
    }

    public function getTemplatesForActiveContexts(int $position_id, bool $editable = false): array
    {
        $active_contexts = [];
        foreach (ilOrgUnitGlobalSettings::getInstance()->getPositionSettings() as $ilOrgUnitObjectPositionSetting) {
            if ($ilOrgUnitObjectPositionSetting->isActive()) {
                $active_contexts[] = $ilOrgUnitObjectPositionSetting->getType();
            }
        }

        $permissions = [];
        foreach ($active_contexts as $context) {
            $permissions[] = $this->getTemplateByContext($context, $position_id, $editable);
        }

        return $permissions;
    }

    public function updatePermission(ilOrgUnitPermission $permission): ilOrgUnitPermission
    {
        $permission = $permission->withPossibleOperations(
            $this->operationRepo->findOperationsByContextId($permission->getContextId())
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
            $this->contextRepo->findContextById($permission->getContextId())
        );

        return $permission;
    }

    private function findPermissionByParent(int $parent_id, int $position_id): ?ilOrgUnitPermission
    {
        $query = 'SELECT id, parent_id, context_id, position_id, protected, operations FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.parent_id = ' . $this->db->quote($parent_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL;
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

        $ret = $this->updatePermission($ret);
        return $ret;
    }

    private function findPermissionByParentAndContext(int $parent_id, int $context_id, int $position_id): ?ilOrgUnitPermission
    {
        $query = 'SELECT id, parent_id, context_id, position_id, protected, operations FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.parent_id = ' . $this->db->quote($parent_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.context_id = ' . $this->db->quote($context_id, 'integer') . PHP_EOL
            . ' AND ' . self::TABLE_NAME . '.position_id = ' . $this->db->quote($position_id, 'integer') . PHP_EOL;
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

        $ret = $this->updatePermission($ret);
        return $ret;
    }

    private function findPermissionById(int $permission_id): ?ilOrgUnitPermission
    {
        $query = 'SELECT id, parent_id, context_id, position_id, protected, operations FROM' . PHP_EOL
            . self::TABLE_NAME
            . '	WHERE ' . self::TABLE_NAME . '.id = ' . $this->db->quote($permission_id, 'integer') . PHP_EOL;
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

        $ret = $this->updatePermission($ret);
        return $ret;
    }

    private function convertToJson(array $operations)
    {
        $ids = [];
        foreach ($operations as $operation) {
            $ids[] = $operation->getOperationId();
        }
        return json_encode($ids);
    }

    private function convertToArray(string $operations)
    {
        $ids = json_decode($operations);
        $ret = [];
        foreach ($ids as $operation_id) {
            $ret[] = $this->operationRepo->findOperationById($operation_id);
        }
        return $ret;
    }
}
