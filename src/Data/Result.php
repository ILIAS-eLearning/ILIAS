<?php

declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Data;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * To be implemented as immutable object.
 */
interface Result
{
    /**
     * Get to know if the result is ok.
     */
    public function isOK(): bool;

    /**
     * Get the encapsulated value.
     *
     * @return mixed
     * @throws \Exception    if !isOK, will either throw the contained exception or
     *                      a NotOKException if a string is contained as error.
     */
    public function value();

    /**
     * Get to know if the result is an error.
     */
    public function isError(): bool;

    /**
     * Get the encapsulated error.
     *
     * @return \Exception|string
     * @throws \LogicException   if isOK
     */
    public function error();

    /**
     * Get the encapsulated value or the supplied default if result is an error.
     *
     * @param mixed $default
     * @return mixed
     */
    public function valueOr($default);

    /**
     * Create a new result where the contained value is modified with $f.
     *
     * Does nothing if !isOK.
     *
     * @param callable $f mixed -> mixed
     */
    public function map(callable $f): Result;

    /**
     * Get a new result from the callable or do nothing if this is an error.
     *
     * If null is returned from $f, the result is not touched.
     *
     * Does nothing if !isOK. This is monadic bind.
     *
     * @param callable $f mixed -> Result|null
     * @throws    \UnexpectedValueException    If callable returns no instance of Result
     */
    public function then(callable $f): Result;

    /**
     * Feed the error into a callable and replace this with the result
     * or do nothing if this is a value.
     *
     * If null is returned from $f, the error in the result is not touched.
     *
     * Does nothing if !isError.
     *
     * @param callable $f string|\Exception -> Result|null
     * @throws    \UnexpectedValueException    If callable returns no instance of Result
     */
    public function except(callable $f): Result;
}
