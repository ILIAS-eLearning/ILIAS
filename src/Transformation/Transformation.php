<?php
/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Transformation;

/**
 * A transformation is a function from one datatype to another.
 *
 * It MUST NOT perform any sideeffects, i.e. it must be morally impossible to observe
 * how often the transformation was actually performed. It MUST NOT touch the provided
 * value, i.e. it is allowed to create new values but not to modify existing values.i
 * This would be an observable sideeffect.
 */
interface Transformation
{
    /**
     * Perform the transformation.
     * Please use this for transformations. It's more performant than calling invoke.
     *
     * @throws \InvalidArgumentException  if the argument could not be transformed
     * @param  mixed  $from
     * @return mixed
     */
    public function transform($from);

    /**
     * Transformations should be callable. This MUST do the same as transform.
     *
     * @throws \InvalidArgumentException  if the argument could not be transformed
     * @param  mixed  $from
     * @return mixed
     */
    public function __invoke($from);
}
