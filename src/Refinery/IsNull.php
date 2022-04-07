<?php declare(strict_types=1);

/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use ILIAS\Refinery\Custom\Constraint as CustomConstraint;
use ILIAS\Data;
use ilLanguage;

/**
 * Class IsNull
 *
 * @package ILIAS\Refinery\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsNull extends CustomConstraint
{
    public function __construct(Data\Factory $data_factory, ilLanguage $lng)
    {
        parent::__construct(
            static function ($value) : bool {
                return is_null($value);
            },
            static function ($txt, $value) : string {
                return $txt("not_a_null", gettype($value));
            },
            $data_factory,
            $lng
        );
    }
}
