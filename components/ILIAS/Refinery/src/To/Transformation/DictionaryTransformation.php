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
use UnexpectedValueException;

class DictionaryTransformation implements Transformable
{
    private Transformable $transformation;

    public function __construct(Transformable $transformation)
    {
        $this->transformation = $transformation;
    }

    /**
     * @return array<string, mixed>
     */
    public function transform($from): array
    {
        $this->check($from);

        $result = [];
        foreach ($from as $key => $value) {
            $transformedValue = $this->transformation->transform($value);
            $result[$key] = $transformedValue;
        }

        return $result;
    }

    private function check($value)
    {
        if (!$this->accepts($value)) {
            throw new UnexpectedValueException('The value MUST be an array with only string keys.');
        }
    }

    private function accepts($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return [] === array_filter(
            $value,
            static fn($key): bool => !is_string($key),
            ARRAY_FILTER_USE_KEY
        );
    }
}
