<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ProblemBuilder;
use UnexpectedValueException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class TupleTransformation implements Constraint
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    protected string $error = '';

    /**
     * @var Transformation[]
     */
    private array $transformations;

    /**
     * @param array $transformations
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $transformation) {
            if (!$transformation instanceof Transformation) {
                $transformationClassName = Transformation::class;

                throw new ConstraintViolationException(
                    sprintf('The array MUST contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }
        }

        $this->transformations = $transformations;
    }

    /**
     * @inheritdoc
     */
    public function transform($from)
    {
        $this->check($from);

        $result = array();
        foreach ($from as $key => $value) {
            $transformedValue = $this->transformations[$key]->transform($value);
            $result[] = $transformedValue;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getError() : string
    {
        return $this->error;
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
        if (!$this->isLengthOfValueAndTransformationEqual($value)) {
            return false;
        }

        return count(array_filter($value, function ($key) {
            if (!array_key_exists($key, $this->transformations)) {
                $this->error = sprintf('There is no entry "%s" defined in the transformation array', $key);
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_KEY)) == 0;
    }

    private function isLengthOfValueAndTransformationEqual($values) : bool
    {
        $countOfValues = count($values);
        $countOfTransformations = count($this->transformations);

        if ($countOfValues !== $countOfTransformations) {
            $this->error = sprintf(
                'The given values(count: "%s") does not match with the given transformations("%s")',
                $countOfValues,
                $countOfTransformations
            );
            return false;
        }

        return true;
    }

    public function problemWith($value) : ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }
}
