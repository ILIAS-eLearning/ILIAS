<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use InvalidArgumentException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class NewMethodTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private object $object;
    private string $method;

    public function __construct(object $object, string $methodToCall)
    {
        if (false === method_exists($object, $methodToCall)) {
            throw new InvalidArgumentException(
                'The second parameter MUST be an method of the object'
            );
        }

        $this->object = $object;
        $this->method = $methodToCall;
    }

    /**
     * @inheritdoc
     * @return mixed
     */
    public function transform($from)
    {
        if (false === is_array($from)) {
            $from = array($from);
        }

        return call_user_func_array(array($this->object, $this->method), $from);
    }
}
