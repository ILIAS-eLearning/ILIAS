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

namespace ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Result\Result;

use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Operators;
use ILIAS\Modules\DataCollection\Fields\Formula\FormulaParser\Math\Functions;

class DateResult extends IntegerResult
{
    public function __construct(
        string $value,
        ?Functions $from_function = null,
        protected ?Operators $from_operator = null,
    ) {
        parent::__construct(
            $value,
            $from_function
        );
    }

    public function getFromOperator(): ?Operators
    {
        return $this->from_operator;
    }

}
