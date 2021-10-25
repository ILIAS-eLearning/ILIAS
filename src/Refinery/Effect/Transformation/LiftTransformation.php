<?php declare(strict_types=1);

namespace ILIAS\Refinery\Effect\Transformation;

use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Effect\IdentityEffect;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\Effect\Effect;

class LiftTransformation implements Transformation
{
    /**
     * Same as transform but returns a Result instead of an exception.
     *
     * @return Result<Effect>
     */
    public function transformResult($from) : Result
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
        return $result->then([$this, 'transformResult']);
    }

    /**
     * @return Result<Effect>
     */
    public function __invoke($from)
    {
        return $this->transform($from);
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
