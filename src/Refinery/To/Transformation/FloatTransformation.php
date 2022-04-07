<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ProblemBuilder;
use UnexpectedValueException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class FloatTransformation implements Constraint
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        $this->check($from);
        return (float) $from;
    }

    public function getError() : string
    {
        return 'The value MUST be of type string.';
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
        return is_float($value);
    }

    public function problemWith($value) : ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }
}
