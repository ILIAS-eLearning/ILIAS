<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;
use ilLanguage;

/**
 * Class LogicalOr
 * @package ILIAS\Refinery\Validation\Constraints
 * @author  Michael Jansen <mjansen@databay.de>
 */
class LogicalOr extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected array $other = [];

    /**
     * LogicalOr constructor.
     * @param Constraint[] $other
     * @param Data\Factory $data_factory
     * @param ilLanguage $lng
     */
    public function __construct(array $other, Data\Factory $data_factory, ilLanguage $lng)
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
                    $problems[] = $constraint->getErrorMessage($value);
                }

                return 'Please fix one of these: ' . implode(', ', array_filter($problems));
            },
            $data_factory,
            $lng
        );
    }
}
