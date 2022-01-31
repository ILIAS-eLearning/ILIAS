<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;

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
        return $result->then(function ($value) : Result {
            try {
                return new Ok($this->transform($value));
            } catch (\Exception $exception) {
                return new Error($exception);
            }
        });
    }
}
