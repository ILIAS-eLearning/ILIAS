<?php
/******************************************************************************
 * Copyright (c) 2014 Richard Klees <richard.klees@rwth-aachen.de>
 *
 * This software is licensed under The MIT License. You should have received 
 * a copy of the along with the code.
 *
 */

namespace Lechimp\Formlets;

/**
 * Interface to the Value representation used for the Formlets.
 *
 * It's a closed union over three types of values that is ordinary values, 
 * function values and error values.
 */
interface IValue {
    /**
     * The origin of a value is the location in the 'real' world, where the
     * value originates from.
     *
     * @return  string | null
     */
    public function origin();

    /**
     * Get the PHP value out of this.
     *
     * Throws when value is an error or a function.
     *
     * @return  mixed
     * @throws  GetError
     */
    public function get();

    /**
     * Apply the value to another value.
     *
     * Throws when value is an ordinary value.
     *
     * @return  IValue
     * @throws  ApplyError
     */
    public function apply(IValue $to);

    /**
     * Return a new function that catches Exceptions and returns them as error
     * values. Returns null when value is an ordinary value.
     *
     * @param   string          $exc_class
     * @return  IValue | null
     * @throws
     */
    public function catchAndReify($exc_class);

    /**
     * Returns string with error message when value is error and
     * null when it's not.
     *
     * @return  string | null
     */
    public function error();
} 

?> 
