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
class ilOrgUnitPermission
{
    public const PARENT_TEMPLATE = -1;
    public const TABLE_NAME = 'il_orgu_permissions';

    protected int $id = 0;
    protected int $parent_id = self::PARENT_TEMPLATE;
    protected int $context_id = 0;
    protected int $position_id = 0;
    protected bool $protected = false;
    /**
     * @var ilOrgUnitOperation[]
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
    protected ?ilOrgUnitOperationContext $context = null;

    public function __construct($id = 0)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParentId(): int
    {
        return $this->parent_id;
    }

    public function withParentId(int $parent_id): self
    {
        $clone = clone $this;
        $clone->parent_id = $parent_id;
        return $clone;
    }

    public function getContextId(): int
    {
        return $this->context_id;
    }

    public function withContextId(int $context_id): self
    {
        $clone = clone $this;
        $clone->context_id = $context_id;
        return $clone;
    }

    public function getPositionId(): int
    {
        return $this->position_id;
    }

    public function withPositionId(int $position_id): self
    {
        $clone = clone $this;
        $clone->position_id = $position_id;
        return $clone;
    }

    /**
     * @return ilOrgUnitOperation[]
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    public function withOperations(array $operations): self
    {
        $clone = clone $this;
        $clone->operations = $operations;
        return $clone;
    }

    public function getPossibleOperations(): array
    {
        return $this->possible_operations;
    }

    public function withPossibleOperations(array $possible_operations): self
    {
        $clone = clone $this;
        $clone->possible_operations = $possible_operations;
        return $clone;
    }

    public function getSelectedOperationIds(): array
    {
        return $this->selected_operation_ids;
    }

    public function withSelectedOperationIds(array $selected_operation_ids): self
    {
        $clone = clone $this;
        $clone->selected_operation_ids = $selected_operation_ids;
        return $clone;
    }

    public function isOperationIdSelected(int $operation_id): bool
    {
        return in_array($operation_id, $this->selected_operation_ids);
    }

    public function getContext(): ?ilOrgUnitOperationContext
    {
        return $this->context;
    }

    public function withContext(ilOrgUnitOperationContext $context): self
    {
        $clone = clone $this;
        $clone->context = $context;
        return $clone;
    }

    public function isProtected(): bool
    {
        return $this->protected;
    }

    public function withProtected(bool $protected): self
    {
        $clone = clone $this;
        $clone->protected = $protected;
        return $clone;
    }

    public function isTemplate(): bool
    {
        return ($this->getParentId() === self::PARENT_TEMPLATE);
    }
}
