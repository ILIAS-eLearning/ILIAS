<?php
declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class FloatTransformation implements Transformation
{
    use DeriveApplyToFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (false === is_float($from)) {
            throw new ConstraintViolationException(
                'The value MUST be of type float',
                'not_float'
            );
        }
        return (float) $from;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
