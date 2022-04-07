<?php declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;
use ilLanguage;

class Sequential extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected array $constraints;

    /**
     * There's a test to show this state will never be visible
     * SequentialTest::testCorrectErrorMessagesAfterMultiAccept
     *
     * @var Constraint
     */
    protected Constraint $failed_constraint;

    public function __construct(array $constraints, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->constraints = $constraints;
        parent::__construct(
            function ($value) {
                foreach ($this->constraints as $constraint) {
                    if (!$constraint->accepts($value)) {
                        $this->failed_constraint = $constraint;
                        return false;
                    }
                }

                return true;
            },
            function ($txt, $value) {
                return $this->failed_constraint->getErrorMessage($value);
            },
            $data_factory,
            $lng
        );
    }
}
