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

namespace ILIAS\Refinery\KindlyTo\Transformation;

use ILIAS\Refinery\Transformable;
use ILIAS\Refinery\ConstraintViolationException;

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
     * @return array<string, mixed>
     */
    public function transform($from)
    {
        if (!is_array($value)) {
            throw new ConstraintViolationException(
                sprintf('The value "%s" is no array.', var_export($value, true)),
                'value_is_no_array',
                $value
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
