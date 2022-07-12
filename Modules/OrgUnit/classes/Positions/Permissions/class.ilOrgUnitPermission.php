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

/**
 * Class ilOrgUnitPermissionGUI
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPermission extends ActiveRecord
{
    public const PARENT_TEMPLATE = -1;
    public  const TABLE_NAME = 'il_orgu_permissions';
    /**
     * @var int
     * @con_is_primary true
     * @con_is_unique  true
     * @con_has_field  true
     * @con_sequence   true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected ?int $id = 0;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $context_id = 0;
    /**
     * @var ilOrgUnitOperation[]
     * @con_has_field  true
     * @con_fieldtype  text
     * @con_length     2048
     */
    protected array $operations = [];
    /**
     * @var ilOrgUnitOperation[]
     */
    protected array $possible_operations = [];
    /**
     * @var int[]
     */
    protected array $selected_operation_ids = [];
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $parent_id = self::PARENT_TEMPLATE;
    /**
     * @var int
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     8
     */
    protected int $position_id = 0;
    protected ?ilOrgUnitOperationContext $context = null;
    /**
     * @con_has_field  true
     * @con_fieldtype  integer
     * @con_length     1
     */
    protected bool $protected = false;
    protected bool $newly_created = false;

    public function update(): void
    {
        if ($this->isProtected()) {
            throw new ilException('Cannot modify a protected ilOrgUnitPermission');
        }
        parent::update();
    }

    public function create() : void
    {
        if ($this->isProtected()) {
            throw new ilException('Cannot modify a protected ilOrgUnitPermission');
        }
        parent::create();
    }

    public function delete(): void
    {
        if ($this->isProtected()) {
            throw new ilException('Cannot modify a protected ilOrgUnitPermission');
        }
        parent::delete();
    }

    public function afterObjectLoad() : void
    {
        $this->possible_operations = ilOrgUnitOperationQueries::getOperationsForContextId($this->getContextId());
        $this->operations = is_array($this->operations) ? $this->operations : array();
        foreach ($this->operations as $operation) {
            $this->selected_operation_ids[] = $operation->getOperationId();
        }
        $this->context = ilOrgUnitOperationContextQueries::findById($this->getContextId());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getContextId(): int
    {
        return $this->context_id;
    }

    /**
     * @param int $context_id
     */
    public function setContextId(int $context_id): void
    {
        $this->context_id = $context_id;
    }

    /**
     * @return ilOrgUnitOperation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * @param ilOrgUnitOperation[] $operations
     */
    public function setOperations(array $operations): void
    {
        $this->operations = $operations;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function setParentId(int $parent_id)
    {
        $this->parent_id = $parent_id;
    }

    public function getPossibleOperations(): array
    {
        return $this->possible_operations;
    }

    public function getSelectedOperationIds(): array
    {
        return $this->selected_operation_ids;
    }

    public function isOperationIdSelected(string $operation_id): bool
    {
        return in_array($operation_id, $this->selected_operation_ids);
    }

    public function getContext(): ?ilOrgUnitOperationContext
    {
        return $this->context;
    }

    public function setContext(ilOrgUnitOperationContext $context)
    {
        $this->context = $context;
    }

    public static function returnDbTableName() : string
    {
        return self::TABLE_NAME;
    }

    public function getPositionId(): int
    {
        return $this->position_id;
    }

    public function setPositionId(int $position_id)
    {
        $this->position_id = $position_id;
    }

    public function isTemplate(): bool
    {
        return ($this->getParentId() === self::PARENT_TEMPLATE);
    }

    public function isDedicated(): bool
    {
        return ($this->getParentId() != self::PARENT_TEMPLATE);
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function setProtected(bool $protected): void
    {
        $this->protected = $protected;
    }

    public function isNewlyCreated(): bool
    {
        return $this->newly_created;
    }

    public function setNewlyCreated(bool $newly_created)
    {
        $this->newly_created = $newly_created;
    }

    public function sleep($field_name)
    {
        switch ($field_name) {
            case 'operations':
                $ids = [];
                foreach ($this->operations as $operation) {
                    $ids[] = $operation->getOperationId();
                }

                return json_encode($ids);
        }

        return parent::sleep($field_name);
    }

    /**
     * @param $field_name
     * @param $field_value
     * @return mixed
     */
    public function wakeUp($field_name, $field_value)
    {
        switch ($field_name) {
            case 'operations':
                $ids = json_decode($field_value);
                $ids = is_array($ids) ? $ids : array();
                $operations = [];
                foreach ($ids as $id) {
                    $ilOrgUnitOperation = ilOrgUnitOperationQueries::findById($id);
                    if ($ilOrgUnitOperation) {
                        $operations[] = $ilOrgUnitOperation;
                    }
                }

                return $operations;
        }

        return parent::wakeUp($field_name, $field_value);
    }
}
