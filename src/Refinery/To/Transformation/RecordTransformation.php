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
class RecordTransformation implements Constraint
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
     * @param Transformation[] $transformations
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $key => $transformation) {
            if (!$transformation instanceof Transformation) {
                $transformationClassName = Transformation::class;

                throw new ConstraintViolationException(
                    sprintf('The array MUST contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }

            if (false === is_string($key)) {
                throw new ConstraintViolationException(
                    'The array key MUST be a string',
                    'key_is_not_a_string'
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
            $transformation = $this->transformations[$key];
            $transformedValue = $transformation->transform($value);

            $result[$key] = $transformedValue;
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
        if (!$this->validateValueLength($value)) {
            return false;
        }

        foreach ($value as $key => $v) {
            if (!is_string($key)) {
                $this->error = 'The array key MUST be a string';
                return false;
            }

            if (!isset($this->transformations[$key])) {
                $this->error = sprintf('Could not find transformation for array key "%s"', $key);
                return false;
            }
        }

        return true;
    }

    private function validateValueLength($values) : bool
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
