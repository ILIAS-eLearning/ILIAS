<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ProblemBuilder;
use UnexpectedValueException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class DictionaryTransformation implements Constraint
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

        $result = array();
        foreach ($from as $key => $value) {
            $transformedValue = $this->transformation->transform($value);
            $result[$key] = $transformedValue;
        }

        return $result;
    }

    public function getError() : string
    {
        return 'The value MUST be an array with only string keys.';
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
        if (!is_array($value)) {
            return false;
        }

        return count(array_filter($value, static function ($key) : bool {
            return !is_string($key);
        }, ARRAY_FILTER_USE_KEY)) === 0;
    }

    public function problemWith($value) : ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }
}
