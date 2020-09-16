<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Logical;

use ILIAS\Refinery\Custom\Constraint;
use ILIAS\Data;
use ILIAS\Refinery\ConstraintViolationException;

class ByTrying extends Constraint
{
    /**
     * @var Constraint[]
     */
    protected $constraints;

    /**
    * @var Data\Factory
    */
    protected $data_factory;

    public function __construct(array $constraints, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->constraints = $constraints;
        $this->data_factory = $data_factory;

        $is_OK = function ($value) {
            foreach ($this->constraints as $constraint) {
                if ($constraint->accepts($value)) {
                    return true;
                }
            }
            return false;
        };

        $is_not_OK = function () {
            throw new ConstraintViolationException(
                'no valid constraints',
                'no_valid_costraints'
            );
        };

        parent::__construct(
            $is_OK,
            $is_not_OK,
            $data_factory,
            $lng
        );
    }

    public function transform($from)
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->accepts($from)) {
                $result = $this->data_factory->ok($from);
                return $constraint->applyTo($result)->value();
            }
        }
        throw new \Exception($this->getErrorMessage($from));
    }
}
