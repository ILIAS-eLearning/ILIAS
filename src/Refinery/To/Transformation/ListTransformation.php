<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ProblemBuilder;
use UnexpectedValueException;
use ILIAS\Refinery\Constraint;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ListTransformation implements Constraint
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    private Transformation $transformation;

    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        $this->check($from);

        $result = [];
        foreach ($from as $value) {
            $transformedValue = $this->transformation->transform($value);
            $result[] = $transformedValue;
        }

        return $result;
    }

    public function getError() : string
    {
        return 'The value MUST be of type array.';
    }

    public function check($value)
    {
        if (!$this->accepts($value)) {
            throw new UnexpectedValueException($this->getErrorMessage($value));
        }

        return null;
    }

    public function accepts($value) : bool
    {
        return is_array($value);
    }

    public function problemWith($value) : ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }
}
