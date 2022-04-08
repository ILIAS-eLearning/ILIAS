<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class TupleTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    /** @var Transformation[] */
    private array $transformations;

    /**
     * @param Transformation[] $transformations;
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $transformation) {
            if (!$transformation instanceof Transformation) {
                $transformationClassName = Transformation::class;

                throw new ConstraintViolationException(
                    sprintf('The array must contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }
        }
        $this->transformations = $transformations;
    }

    /**
     * @inheritDoc
     */
    public function transform($from) : array
    {
        if (!is_array($from)) {
            $from = [$from];
        }

        if ([] === $from) {
            throw new ConstraintViolationException(
                sprintf('The array "%s" ist empty', var_export($from, true)),
                'value_array_is_empty',
                $from
            ) ;
        }

        $this->testLengthOf($from);

        $result = [];
        foreach ($from as $key => $value) {
            if (!array_key_exists($key, $this->transformations)) {
                throw new ConstraintViolationException(
                    sprintf('Matching key "%s" not found', $key),
                    'matching_values_not_found',
                    $value
                );
            }
            $transformedValue = $this->transformations[$key]->transform($value);
            $result[] = $transformedValue;
        }

        return $result;
    }

    private function testLengthOf(array $values) : void
    {
        $countOfValues = count($values);
        $countOfTransformations = count($this->transformations);

        if ($countOfValues !== $countOfTransformations) {
            throw new ConstraintViolationException(
                sprintf('The length of given value "%s" does not match with the given transformations', $countOfValues),
                'given_values_',
                $countOfValues
            );
        }
    }
}
