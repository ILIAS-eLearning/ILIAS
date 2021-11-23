<?php declare(strict_types=1);

/**
 * @author  Lukas Scharmer <lscharmer@databay.de>
 */
namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Refinery\DeriveInvokeFromTransform;

class IdentityTransformation implements Transformation
{
    use DeriveInvokeFromTransform;

    /**
     * @throws \InvalidArgumentException
     */
    public function transform($from)
    {
        return $this->transformResult($from)->value();
    }

    /**
     * @return Result
     */
    public function applyTo(Result $result) : Result
    {
        return $result->then(function ($from) {
            return $this->transformResult($from);
        });
    }

    /**
     * @return Result
     */
    protected function validate($from) : Result
    {
        return new Ok($from);
    }

    protected function saveTransform($value)
    {
        return $value;
    }

    /**
     * Same as transform but returns a Result instead of an exception.
     *
     * @return Result
     */
    private function transformResult($from) : Result
    {
        return $this->validate($from)->map(function ($from) {
            return $this->saveTransform($from);
        });
    }
}
