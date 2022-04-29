<?php declare(strict_types=1);

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

namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use InvalidArgumentException;

/**
 * A transformation is a function from one datatype to another.
 *
 * It MUST NOT perform any sideeffects, i.e. it must be morally impossible to observe
 * how often the transformation was actually performed. It MUST NOT touch the provided
 * value, i.e. it is allowed to create new values but not to modify existing values.
 * This would be an observable sideeffect.
 */
interface Transformation
{
    /**
     * Perform the transformation.
     * Please use this for transformations. It's more performant than calling invoke.
     *
     * @param mixed $from
     * @return mixed
     * @throws InvalidArgumentException  if the argument could not be transformed
     */
    public function transform($from);

    /**
     * Perform the transformation and reify possible failures.
     *
     * If `$data->isError()`, the method MUST do nothing. It MUST transform the value
     * in `$data` like it would transform $data provided to `transform`. It must reify
     * every exception thrown in this process by returning a `Result` that `isError()`
     * and contains the exception that happened.
     *
     * If you simply need to implement a transformation you most probably want to
     * implement transform and derive this via the trait `DeriveTransformationInterface`.
     *
     * If you simply want to call the transformation, you most probably want to use
     * `transform`, since it simply throws exceptions that occurred while doing the
     * transformation.
     *
     * If you are implementing some entity that performs processing of input data at
     * some boundary, the reification of exceptions might help you to write cleaner
     * code.
     */
    public function applyTo(Result $result) : Result;

    /**
     * Transformations should be callable. This MUST do the same as transform.
     *
     * @param mixed $from
     * @return mixed
     * @throws InvalidArgumentException  if the argument could not be transformed
     */
    public function __invoke($from);
}
