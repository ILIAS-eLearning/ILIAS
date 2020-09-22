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
use ILIAS\Refinery\DeriveInvokeFromTransform;

class IntegerTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (false === is_int($from)) {
            throw new ConstraintViolationException(
                'The value MUST be of type integer',
                'not_integer'
            );
        }
        return (int) $from;
    }
}
