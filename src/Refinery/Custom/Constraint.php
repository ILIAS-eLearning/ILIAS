<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\Custom;

use ILIAS\Refinery\Constraint as ConstraintInterface;
use ILIAS\Refinery\DeriveTransformFromApplyTo;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Data;
use ILIAS\Data\Result;
use ILIAS\Refinery\ProblemBuilder;

class Constraint implements ConstraintInterface
{
    use DeriveTransformFromApplyTo;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    /**
     * @var ILIAS\Data\Factory
     */
    protected $data_factory;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var callable
     */
    protected $is_ok;

    /**
     * @var callable|string
     */
    protected $error;

    /**
     * If $error is a callable it needs to take two parameters:
     *      - one callback $txt($lng_id, ($value, ...)) that retrieves the lang var
     *        with the given id and uses sprintf to replace placeholder if more
     *        values are provide.
     *      - the $value for which the error message should be build.
     *
     * @param string|callable	$error
     */
    public function __construct(callable $is_ok, $error, Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->is_ok = $is_ok;
        $this->error = $error;
        $this->data_factory = $data_factory;
        $this->lng = $lng;
    }

    /**
     * @inheritdoc
     */
    protected function getError()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    final public function check($value)
    {
        if (!$this->accepts($value)) {
            throw new \UnexpectedValueException($this->getErrorMessage($value));
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    final public function accepts($value)
    {
        return call_user_func($this->is_ok, $value);
    }

    /**
     * @inheritdoc
     */
    final public function problemWith($value)
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    final public function applyTo(Result $result) : Result
    {
        if ($result->isError()) {
            return $result;
        }

        $problem = $this->problemWith($result->value());
        if ($problem !== null) {
            $error = $this->data_factory->error($problem);
            return $error;
        }

        return $result;
    }
}
