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

namespace ILIAS\Questions\Presentation\Layout\Definitions;

use ILIAS\Refinery\Transformation;

class CarryWrapper
{
    public function __construct(
        private array $raw_values
    ) {
    }

    /**
     * The Transformation will receive either the value from $_POST or an instance
     * of `CarrySectionData` containing the next nesting level if the value contains
     * values from multiple variables from $_POST.
     */
    public function retrieve(string $key, Transformation $transformation): mixed
    {
        return $transformation->transform(
            $this->retrieveValueFromArray($key)
        );
    }

    private function retrieveValueFromArray(string $key): mixed
    {
        $value = $this->raw_values[$key] ?? null;

        if ($value === null) {
            return null;
        }

        if ($value instanceof Leaf) {
            return $value->get();
        }

        return new self($value);
    }
}
