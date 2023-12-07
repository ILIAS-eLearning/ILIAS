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

class DictionaryTransformation implements Transformable
{
    private Transformable $transformation;

    public function __construct(Transformable $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @inheritDoc
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
        foreach ($from as $key => $value) {
            if (!(is_int($key) || is_string($key))) {
                throw new ConstraintViolationException(
                    'Key is not a string or int',
                    'key_is_no_string_or_int'
                );
            }
            $transformedValue = $this->transformation->transform($value);
            $result[(string) $key] = $transformedValue;
        }

        return $result;
    }
}
