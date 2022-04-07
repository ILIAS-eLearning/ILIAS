<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery;

use ILIAS\Data\Result;
use Exception;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
trait DeriveTransformFromApplyTo
{
    abstract public function applyTo(Result $result) : Result;

    /**
     * @param mixed $from
     * @return mixed
     * @throws Exception
     */
    public function transform($from)
    {
        /** @var Result $result */
        $result = $this->applyTo(new Result\Ok($from));
        if (true === $result->isError()) {
            $error = $result->error();

            if ($error instanceof Exception) {
                throw $error;
            }

            throw new Exception($error);
        }
        return $result->value();
    }
}
