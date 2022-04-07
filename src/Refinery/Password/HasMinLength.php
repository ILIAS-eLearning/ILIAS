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

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;
use ilLanguage;

class HasMinLength extends CustomConstraint
{
    protected int $min_length;

    public function __construct(int $min_length, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->min_length = $min_length;
        parent::__construct(
            function (Data\Password $value) {
                return strlen($value->toString()) >= $this->min_length;
            },
            function ($value) {
                return "Password has a length less than '$this->min_length'.";
            },
            $data_factory,
            $lng
        );
    }
}
