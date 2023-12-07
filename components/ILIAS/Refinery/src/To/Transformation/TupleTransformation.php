<?php

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

declare(strict_types=1);

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\Transformable;
use ILIAS\Refinery\ConstraintViolationException;
use UnexpectedValueException;

class TupleTransformation implements Transformable
{
    private string $error = '';
    /** @var Transformable[] */
    private array $transformations;

    /**
     * @param Transformable[] $transformations
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $transformation) {
            if (!$transformation instanceof Transformable) {
                $transformationClassName = Transformable::class;

                throw new ConstraintViolationException(
                    sprintf('The array MUST contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }
        }

        $this->transformations = $transformations;
    }

    public function transform($from): array
    {
        $this->check($from);

        $result = [];
        foreach ($from as $key => $value) {
            $transformedValue = $this->transformations[$key]->transform($value);
            $result[] = $transformedValue;
        }

        return $result;
    }

    private function check($value): void
    {
        $this->validateLengthOfValueAndTransformationEqual($value);

        array_walk($value, function ($v, $key): bool {
            if (!array_key_exists($key, $this->transformations)) {
                throw new UnexpectedValueException(sprintf('There is no entry "%s" defined in the transformation array', $key));
            }
        });
    }

    private function validateLengthOfValueAndTransformationEqual($values): void
    {
        $countOfValues = count($values);
        $countOfTransformations = count($this->transformations);

        if ($countOfValues !== $countOfTransformations) {
            throw new UnexpectedValueException(sprintf(
                'The given values(count: "%s") does not match with the given transformations("%s")',
                $countOfValues,
                $countOfTransformations
            ));
        }
    }
}
