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

final class ilPDSelectedItemBlockMembershipsDTO
{
    public function __construct(
        protected readonly int $ref_id,
        protected readonly int $obj_id,
        protected readonly string $type,
        protected readonly string $title,
        protected readonly string $description,
        protected readonly int $parent_ref_id,
        protected readonly int $parent_lft_tree,
        protected readonly bool $object_period_has_time,
        protected readonly ?DateTimeImmutable $period_start,
        protected readonly ?DateTimeImmutable $period_end
    ) {
    }

    final public function getRefId(): int
    {
        return $this->ref_id;
    }

    final public function getObjId(): int
    {
        return $this->obj_id;
    }

    final public function getType(): string
    {
        return $this->type;
    }

    final public function getTitle(): string
    {
        return $this->title;
    }

    final public function getDescription(): string
    {
        return $this->description;
    }

    final public function getParentRefId(): int
    {
        return $this->parent_ref_id;
    }

    final public function getParentLftTree(): int
    {
        return $this->parent_lft_tree;
    }

    final public function objectPeriodHasTime(): bool
    {
        return $this->object_period_has_time;
    }

    final public function getPeriodStart(): ?DateTimeImmutable
    {
        return $this->period_start;
    }

    final public function getPeriodEnd(): ?DateTimeImmutable
    {
        return $this->period_end;
    }
}
