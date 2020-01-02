<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Validation;

use ILIAS\Data;

/**
 * Factory for creating constraints.
 */
class Factory
{
    const LANGUAGE_MODULE = "validation";

    /**
     * @var Data\Factory
     */
    protected $data_factory;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * Factory constructor.
     *
     * @param Data\Factory $data_factory
     */
    public function __construct(Data\Factory $data_factory, \ilLanguage $lng)
    {
        $this->data_factory = $data_factory;
        $this->lng = $lng;
        $this->lng->loadLanguageModule(self::LANGUAGE_MODULE);
    }


    // COMBINATORS

    /**
     * Get a constraint that sequentially checks the supplied constraints.
     *
     * The new constraint tells the problem of the first violated constraint.
     *
     * @param   Constraint[]   $others
     * @return  Constraint
     */
    public function sequential(array $others)
    {
        return new Constraints\Sequential($others, $this->data_factory, $this->lng);
    }

    /**
     * Get a constraint that checks the supplied constraints in parallel.
     *
     * The new constraint tells the problems of all violated constraints.
     *
     * @param   Constraint[]   $others
     * @return	Constraint
     */
    public function parallel(array $others)
    {
        return new Constraints\Parallel($others, $this->data_factory, $this->lng);
    }

    /**
     * Get a negated constraint.
     *
     * @param   Constraint   $other
     * @return  Constraint
     */
    public function not(Constraint $other)
    {
        return new Constraints\Not($other, $this->data_factory, $this->lng);
    }

    /**
     * Get a logical or constraint.
     * @param   Constraint[]   $others
     * @return  Constraint
     */
    public function or(array $others)
    {
        return new Constraints\LogicalOr($others, $this->data_factory, $this->lng);
    }

    // SOME RESTRICTIONS

    /**
     * Get a constraint for an integer.
     *
     * @return  Constraint
     */
    public function isInt()
    {
        return new Constraints\IsInt($this->data_factory, $this->lng);
    }


    /**
     * Get a constraint for a string.
     *
     * @return  Constraint
     */
    public function isString()
    {
        return new Constraints\IsString($this->data_factory, $this->lng);
    }


    /**
     * Get a constraint for a array with constraint to all elements.
     *
     * @param Constraint $on_element
     *
     * @return Constraints\IsArrayOf
     */
    public function isArrayOf(Constraint $on_element)
    {
        return new Constraints\IsArrayOf($this->data_factory, $on_element, $this->lng);
    }

    /**
     * Get the constraint that some value is larger than $min.
     *
     * @param   int   $min
     * @return  Constraint
     */
    public function greaterThan($min)
    {
        return new Constraints\GreaterThan($min, $this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that some value is smaller then $max.
     *
     * @param   int   $max
     * @return  Constraint
     */
    public function lessThan($max)
    {
        return new Constraints\LessThan($max, $this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that some value is a number
     *
     * @return  Constraint
     */
    public function isNumeric()
    {
        return new Constraints\IsNumeric($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that some value is null
     *
     * @return  Constraint
     */
    public function isNull()
    {
        return new Constraints\IsNull($this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that some string has a minimum length.
     *
     * @param	int	$min_length
     * @return	Constraint
     */
    public function hasMinLength($min_length)
    {
        return new Constraints\HasMinLength($min_length, $this->data_factory, $this->lng);
    }

    /**
     * Get the constraint that limits the maximum length of the string.
     *
     * @param	int	$max_length
     * @return	Constraint
     */
    public function hasMaxLength($max_length)
    {
        return new Constraints\HasMaxLength($max_length, $this->data_factory, $this->lng);
    }

    /**
     * Get a custom constraint.
     *
     * If the provided value !$is_ok will either use the $error (if it is a string)
     * or provide the value to the $error callback.
     *
     * If $error is a callable it needs to take two parameters:
     *      - one callback $txt($lng_id, ($value, ...)) that retrieves the lang var
     *        with the given id and uses sprintf to replace placeholder if more
     *        values are provide
     *      - the $value for which the error message should be build.
     *
     * @param   callable          $is_ok MUST return boolean
     * @param   string|callable   $error
     * @return  Constraint
     */
    public function custom(callable $is_ok, $error)
    {
        return new Constraints\Custom($is_ok, $error, $this->data_factory, $this->lng);
    }

    /**
     * Get the factory for password constraints.
     *
     * @return   ILIAS\Validation\Constraints\Password\Factory;
     */
    public function password()
    {
        return new Constraints\Password\Factory($this->data_factory, $this->lng);
    }
}
