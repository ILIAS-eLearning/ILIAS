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

namespace ILIAS\Questions\Definitions;

use ILIAS\Language\Language;

enum Range: string
{
    case InRange = 'i';
    case OutOfRange = 'o';
    case BelowRange = 'b';
    case AboveRange = 'a';

    public function getLabel(
        Language $lng
    ): string {
        return match($this) {
            self::InRange => $lng->txt('in_range'),
            self::OutOfRange => $lng->txt('out_of_range'),
            self::BelowRange => $lng->txt('below_range'),
            self::AboveRange => $lng->txt('above_range')
        };
    }
}
