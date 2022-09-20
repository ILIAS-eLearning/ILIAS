<?php

declare(strict_types=1);

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

namespace ILIAS\Refinery\To\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\ConstraintViolationException;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\ProblemBuilder;
use UnexpectedValueException;

class RecordTransformation implements Constraint
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    private string $error = '';
    /** @var array<string, Transformation> */
    private array $transformations;

    /**
     * @param array<string, Transformation> $transformations
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
     * @inheritDoc
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

    /**
     * @inheritDoc
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @inheritDoc
     */
    public function check($value)
    {
        if (!$this->accepts($value)) {
            throw new UnexpectedValueException($this->getErrorMessage($value));
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
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

    private function validateValueLength(array $values): bool
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

    /**
     * @inheritDoc
     */
    public function problemWith($value): ?string
    {
        if (!$this->accepts($value)) {
            return $this->getErrorMessage($value);
        }

        return null;
    }
}
