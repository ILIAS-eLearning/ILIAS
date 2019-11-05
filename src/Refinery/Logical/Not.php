<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;

class Not extends Constraint
{
    /**
     * @var Constraint
     */
    protected $constraint;

    public function __construct(Constraint $constraint, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->constraint = $constraint;
        parent::__construct(
            function ($value) {
                return !$this->constraint->accepts($value);
            },
            function ($txt, $value) {
                return $txt("not_generic", $this->constraint->getErrorMessage($value));
            },
            $data_factory,
            $lng
        );
    }
}
