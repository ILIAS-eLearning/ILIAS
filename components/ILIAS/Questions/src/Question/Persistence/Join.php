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

namespace ILIAS\Questions\Question\Persistence;

class Join
{
    public function __construct(
        private readonly Column $left,
        private readonly Column $right,
        private readonly JoinType $type = JoinType::Inner
    ) {
    }

    public function toSql(): string
    {
        return "{$this->type->value} JOIN {$this->right->getTable()} ON {$this->left->toColumnString()} = {$this->right->toColumnString()}";
    }

    public function getLeft(): Column
    {
        return $this->left;
    }

    public function getRight(): Column
    {
        return $this->right;
    }

    public function getType(): JoinType
    {
        return $this->type;
    }
}
