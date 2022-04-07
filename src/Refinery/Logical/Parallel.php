<?php declare(strict_types=1);

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;
use ilLanguage;

class Parallel extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected array $constraints;

    /**
     * There's a test to show this state will never be visible
     * ParallelTest::testCorrectErrorMessagesAfterMultiAccept
     *
     * @var Constraint[]
     */
    protected array $failed_constraints;

    public function __construct(array $constraints, Data\Factory $data_factory, ilLanguage $lng)
    {
        $this->constraints = $constraints;
        parent::__construct(
            function ($value) {
                $ret = true;
                $this->failed_constraints = array();
                foreach ($this->constraints as $constraint) {
                    if (!$constraint->accepts($value)) {
                        $this->failed_constraints[] = $constraint;
                        $ret = false;
                    }
                }

                return $ret;
            },
            function ($txt, $value) {
                $messages = [];
                foreach ($this->failed_constraints as $constraint) {
                    $messages[] = $constraint->getErrorMessage($value);
                }

                return implode(" ", $messages);
            },
            $data_factory,
            $lng
        );
    }
}
