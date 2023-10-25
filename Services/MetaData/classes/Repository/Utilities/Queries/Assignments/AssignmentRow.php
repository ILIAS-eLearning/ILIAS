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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Assignments;

class AssignmentRow implements AssignmentRowInterface
{
    protected string $table;
    protected int $id;
    protected int $id_from_parent_table;

    /**
     * @var ActionAssignmentInterface[]
     */
    protected array $value_assignments = [];

    public function __construct(
        string $table,
        int $id,
        int $id_from_parent_table
    ) {
        $this->table = $table;
        $this->id = $id;
        $this->id_from_parent_table = $id_from_parent_table;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function idFromParentTable(): int
    {
        return $this->id_from_parent_table;
    }

    /**
     * @return ActionAssignmentInterface[]
     */
    public function actions(): \Generator
    {
        yield from $this->value_assignments;
    }

    public function addAction(
        ActionAssignmentInterface $assignment
    ): void {
        $this->value_assignments[] = $assignment;
    }
}
