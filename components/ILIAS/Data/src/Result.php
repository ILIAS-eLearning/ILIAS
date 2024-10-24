<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Data;

/**
 * A result encapsulates a value or an error and simplifies the handling of those.
 *
 * To be implemented as immutable object.
 *
 * @template-covariant A
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
     * @return A
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
     * @template B
     *
     * @param B $default
     * @return A|B
     */
    public function valueOr($default);

    /**
     * Create a new result where the contained value is modified with $f.
     *
     * Does nothing if !isOK.
     *
     * @template B
     *
     * @param callable(A): B $f
     * @return Result<B>
     */
    public function map(callable $f): Result;

    /**
     * Get a new result from the callable or do nothing if this is an error.
     *
     * If null is returned from $f, the result is not touched.
     *
     * Does nothing if !isOK. This is monadic bind.
     *
     * @template B
     *
     * @param callable(A): ?Result<B> $f
     * @return Result<A>|Result<B>
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
     * @template B
     *
     * @param callable(string|\Exception): ?Result<B> $f
     * @return Result<A>|Result<B>
     * @throws    \UnexpectedValueException    If callable returns no instance of Result
     */
    public function except(callable $f): Result;
}
