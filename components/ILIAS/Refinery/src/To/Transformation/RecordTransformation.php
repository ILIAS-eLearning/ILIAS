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

class RecordTransformation implements Transformable
{
    /** @var array<string, Transformable> */
    private array $transformations;

    /**
     * @param array<string, Transformable> $transformations
     */
    public function __construct(array $transformations)
    {
        foreach ($transformations as $key => $transformation) {
            if (!$transformation instanceof Transformable) {
                $transformationClassName = Transformable::class;

                throw new ConstraintViolationException(
                    sprintf('The array MUST contain only "%s" instances', $transformationClassName),
                    'not_a_transformation',
                    $transformationClassName
                );
            }

            if (!is_string($key)) {
                throw new ConstraintViolationException(
                    'The array key MUST be a string',
                    'key_is_not_a_string'
                );
            }
        }

        $this->transformations = $transformations;
    }

    /**
     * @return array<string, mixed>
     */
    public function transform($from): array
    {
        $this->check($from);

        $result = [];
        foreach ($from as $key => $value) {
            $transformation = $this->transformations[$key];
            $transformedValue = $transformation->transform($value);

            $result[$key] = $transformedValue;
        }

        return $result;
    }

    private function check($value): void
    {
        $this->validateValueLength($value);

        foreach ($value as $key => $v) {
            if (!is_string($key)) {
                throw new UnexpectedValueException('The array key MUST be a string');
            }

            if (!isset($this->transformations[$key])) {
                throw new UnexpectedValueException(sprintf('Could not find transformation for array key "%s"', $key));
            }
        }
    }

    private function validateValueLength(array $values): void
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
