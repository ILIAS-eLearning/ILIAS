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

class Where
{
    public function __construct(
        private readonly Column $left,
        private readonly Value $right,
        private readonly Operator $comparison = Operator::Equal,
        private readonly Junctor $junctor = Junctor::Conjunction,
        private readonly bool $negate = false
    ) {
    }

    public function toSql(): string
    {
        return $this->negate ? 'NOT ' : ''
            . $this->comparison->toSql(
                $this->left,
                $this->right->getNumberOfElements()
            );
    }

    public function getRight(): int|string
    {
        return $this->right;
    }

    public function getLogicalOperator(): Junctor
    {
        return $this->junctor;
    }
}
