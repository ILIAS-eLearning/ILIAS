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

namespace ILIAS\Export\ImportHandler\Path\Comparison;

use ILIAS\Export\ImportHandler\I\Path\Comparison\HandlerInterface as FilePathComparisonInterface;
use ILIAS\Export\ImportHandler\Path\Comparison\Operator as PathComparisonOperator;

class Handler implements FilePathComparisonInterface
{
    protected Operator $operator;
    protected string $value;

    public function toString()
    {
        return (Operator::toString($this->operator) ?? "") . ($this->value ?? "");
    }

    public function withOperator(
        PathComparisonOperator $operator
    ): FilePathComparisonInterface {
        $clone = clone $this;
        $clone->operator = $operator;
        return $clone;
    }

    public function withValue(
        string $value
    ): FilePathComparisonInterface {
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }
}
