<?php declare(strict_types=1);

namespace ILIAS\Refinery\Effect\Transformation;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Effect\IdentityEffect;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\Effect\Effect;
use ILIAS\Refinery\DeriveInvokeFromTransform;

/**
 * "Lifts" a value to an Effect (T => Effect<T>).
 * Creates a parameterized type (in this case Effect<T>) from another type (T).
 * @see https://wiki.haskell.org/Lifting
 */
class LiftTransformation implements Transformation
{
    use DeriveInvokeFromTransform;

    /**
     * Same as transform but returns a Result instead of an exception.
     *
     * @return Result<Effect>
     */
    private function transformResult($from) : Result
    {
        return $this->validate($from)->map([$this, 'createEffect']);
    }

    /**
     * @throws \InvalidArgumentException
     * @return Effect
     */
    public function transform($from)
    {
        return $this->transformResult($from)->value();
    }

    /**
     * @return Result<Effect>
     */
    public function applyTo(Result $result) : Result
    {
        return $result->then(function ($from) {
            return $this->transformResult($from);
        });
    }

    /**
     * @return Result<Effect>
     */
    protected function validate($from) : Result
    {
        return new Ok($from);
    }

    public function createEffect($value) : Effect
    {
        return new IdentityEffect($value);
    }
}
