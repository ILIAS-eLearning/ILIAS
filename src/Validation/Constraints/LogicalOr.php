<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation\Constraints;

use ILIAS\Validation\Constraint;
use ILIAS\Data;

/**
 * Class LogicalOr
 * @package ILIAS\Validation\Constraints
 * @author  Michael Jansen <mjansen@databay.de>
 */
class LogicalOr extends Custom implements Constraint
{
    /**
     * @var Constraint[]
     */
    protected $other = [];

    /**
     * LogicalOr constructor.
     * @param Constraint[] $other
     * @param Data\Factory $data_factory
     */
    public function __construct(array $other, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->other = $other;

        parent::__construct(
            function ($value) {
                foreach ($this->other as $constraint) {
                    if ($constraint->accepts($value)) {
                        return true;
                    }
                }

                return false;
            },
            function ($value) {
                $problems = [];

                foreach ($this->other as $constraint) {
                    $problems[] = (string) $constraint->problemWith($value);
                }

                return 'Please fix one of these: ' . implode(', ', array_filter($problems));
            },
            $data_factory,
            $lng
        );
    }
}
