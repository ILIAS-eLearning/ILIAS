<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use ILIAS\Data\Result;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
trait DeriveApplyToFromTransform
{
    /**
     * @param mixed $from
     * @return mixed
     * @throws \Exception
     */
    abstract public function transform($from);

    public function applyTo(Result $result) : Result
    {
        try {
            $value = $this->transform($result->value());
        } catch (\Exception $exception) {
            return new Result\Error($exception);
        }

        return new Result\Ok($value);
    }
}
