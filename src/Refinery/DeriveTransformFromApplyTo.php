<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery;

use ILIAS\Data\Result;

trait DeriveTransformFromApplyTo
{
    /**
     * @param Result $result
     * @return Result
     */
    abstract public function applyTo(Result $result) : Result;

    /**
     * @param mixed $from
     * @return mixed
     * @throws \Exception
     */
    public function transform($from)
    {
        /** @var Result $result */
        $result = $this->applyTo(new Result\Ok($from));
        if (true === $result->isError()) {
            $error = $result->error();

            if ($error instanceof \Exception) {
                throw $error;
            }

            throw new \Exception($error);
        }
        return $result->value();
    }
}
