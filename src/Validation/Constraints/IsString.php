<?php
/* Copyright (c) 2018 Fabian Schmid <fs@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;
use ILIAS\Data\Result;

/**
 * Class IsString
 *
 * @package ILIAS\Validation\Constraints
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class IsString extends Custom implements Constraint
{

    /**
     * IsString constructor.
     */
    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        parent::__construct(
            function ($value) {
                return is_string($value);
            },
            function ($txt, $value) {
                return $txt("not_a_string", gettype($value));
            },
            $data_factory,
            $lng
        );
    }
}
