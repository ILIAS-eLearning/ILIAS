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

class DictionaryTransformation implements Transformation
{
    use DeriveApplyToFromTransform;
    use DeriveInvokeFromTransform;

    private Transformation $transformation;

    public function __construct(Transformation $transformation)
    {
        $this->transformation = $transformation;
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
