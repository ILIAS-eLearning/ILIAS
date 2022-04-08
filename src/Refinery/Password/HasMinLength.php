<?php declare(strict_types=1);

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

namespace ILIAS\Refinery\Password;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;
use ilLanguage;

class HasMinLength extends Constraint
{
    public function __construct(int $min_length, Data\Factory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            static function (Data\Password $value) use ($min_length) : bool {
                return strlen($value->toString()) >= $min_length;
            },
            static function ($value) use ($min_length) : string {
                return "Password has a length less than '$min_length'.";
            },
            $data_factory,
            $lng
        );
    }
}
