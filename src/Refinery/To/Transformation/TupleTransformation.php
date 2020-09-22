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

class TupleTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /**
     * @var Transformation[]
     */
    private $transformations;

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
        $this->validateValueLength($from);

        $result = array();
        foreach ($from as $key => $value) {
            if (false === array_key_exists($key, $this->transformations)) {
                throw new ConstraintViolationException(
                    sprintf(
                        'There is no entry "%s" defined in the transformation array',
                        $key
                    ),
                    'values_do_not_match',
                    $key
                );
            }
            $transformedValue = $this->transformations[$key]->transform($value);

            $result[] = $transformedValue;
        }

        return $result;
    }

    /**
     * @param $values
     */
    private function validateValueLength($values)
    {
        $countOfValues = count($values);
        $countOfTransformations = count($this->transformations);

        if ($countOfValues !== $countOfTransformations) {
            throw new ConstraintViolationException(
                sprintf(
                    'The given values(count: "%s") does not match with the given transformations("%s")',
                    $countOfValues,
                    $countOfTransformations
                ),
                'given_values_',
                $countOfValues,
                $countOfTransformations
            );
        }
    }
}
