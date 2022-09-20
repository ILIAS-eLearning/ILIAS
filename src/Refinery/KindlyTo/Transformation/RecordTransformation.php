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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\DeriveApplyToFromTransform;
use ILIAS\Refinery\DeriveInvokeFromTransform;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\ConstraintViolationException;

class RecordTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

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
     * @return array<string, mixed>
     */
    public function transform($from): array
    {
        if (!is_array($from)) {
            throw new ConstraintViolationException(
                sprintf('The value "%s" is no array.', var_export($from, true)),
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
}
