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

namespace ILIAS\Refinery\String;

use ILIAS\Data;
use ILIAS\Refinery\Custom\Constraint;

class HasMaxLength extends Constraint
{
    public function __construct(int $max_length, Data\Factory $data_factory, \ILIAS\Language\Language $lng)
    {
        parent::__construct(
            static function ($value) use ($max_length): bool {
                return strlen($value) <= $max_length;
            },
            static function ($txt, $value) use ($max_length): string {
                return $txt("not_max_length", $max_length);
            },
            $data_factory,
            $lng
        );
    }
}
