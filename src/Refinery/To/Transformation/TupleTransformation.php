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

class TupleTransformation implements Constraint
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;
    use ProblemBuilder;

    private string $error = '';
    /** @var Transformation[] */
    private array $transformations;

    /**
     * @param Transformation[] $transformations
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
     * @inheritDoc
     */
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
        if (!$this->isLengthOfValueAndTransformationEqual($value)) {
            return false;
        }

        return count(array_filter($value, function ($key): bool {
            if (!array_key_exists($key, $this->transformations)) {
                $this->error = sprintf('There is no entry "%s" defined in the transformation array', $key);
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_KEY)) === 0;
    }

    private function isLengthOfValueAndTransformationEqual($values): bool
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
