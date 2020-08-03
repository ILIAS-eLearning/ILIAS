<?php declare(strict_types=1);

/* Copyright (c) 2020 Luka K. A. Stocker, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class RecordTransformation implements Transformation
{
    use DeriveApplyToFromTransform;

    private $transformations;

    /**
     *@param Transformation[] $transformations
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $key => $transformation) {
            if (!$transformation instanceof Transformation) {
                $transformationClassName = Transformation::class;

                throw new ConstraintViolationException(
                    sprintf('The array must contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }

            if (!is_string($key)) {
                throw new ConstraintViolationException(
                    sprintf('The array key "%s" must be a string', $key),
                    'key_is_not_a_string',
                    $key
                );
            }
        }
        $this->transformations = $transformations;
    }

    /**
     * @inheritDoc
     */
    public function transform($from)
    {
        if (!is_array($from)) {
            throw new ConstraintViolationException(
                sprintf('The value "%s" is no array.', $from),
                'value_is_no_array',
                $from
            );
        }

        $result = [];
        foreach ($this->transformations as $key => $transformation) {
            if (!array_key_exists($key, $from)) {
                throw new ConstraintViolationException(
                    sprintf('Could not find value for key "%s"', $key),
                    'no_array_key_existing',
                    $key
                );
            }
            $result[$key] = $transformation->transform($from[$key]);
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function __invoke($from)
    {
        return $this->transform($from);
    }
}
