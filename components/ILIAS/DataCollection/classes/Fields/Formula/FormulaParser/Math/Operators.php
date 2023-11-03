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

namespace ILIAS\components\DataCollection\Fields\Formula\FormulaParser\Math;

enum Operators: string
{
    case ADDITION = '+';
    case SUBTRACTION = '-';
    case MULTIPLICATION = '*';
    case DIVISION = '/';
    case POWER = '^';

    public function getPrecedence(): int
    {
        return match ($this) {
            self::ADDITION => 1,
            self::SUBTRACTION => 1,
            self::MULTIPLICATION => 2,
            self::DIVISION => 2,
            self::POWER => 3,
        };
    }
}
