<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;

class ListTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    /**
     * @var Transformation
     */
    private $transformation;

    /**
     * @param Transformation $transformation
     */
    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (false === is_array($from)) {
            throw new ConstraintViolationException(
                'The input value must be an array',
                'must_be_array'
            );
        }
        if (array() === $from) {
            throw new ConstraintViolationException(
                'Value array is empty',
                'value_array_is_empty'
            );
        }

        $result = array();
        foreach ($from as $value) {
            $transformedValue = $this->transformation->transform($value);
            $result[] = $transformedValue;
        }



        return $result;
    }

    /**
     * @inheritdoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
