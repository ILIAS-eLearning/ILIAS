<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 Luka Kai Alexander Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;

class ListTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private $transformation;

    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        if (!is_array($from)) {
            $from = [$from];
        }

        $result = [];
        foreach ($from as $val) {
            $transformedVal = $this->transformation->transform($val);
            $result[] = $transformedVal;
        }
        return $result;
    }
}
