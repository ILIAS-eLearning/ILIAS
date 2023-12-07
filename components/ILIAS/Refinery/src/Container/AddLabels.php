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

namespace ILIAS\Refinery\Container;

use ILIAS\Refinery\Transformable;
use InvalidArgumentException;

/**
 * Adds to any array keys for each value
 */
class AddLabels implements Transformable
{
    /**
     * @param string[]|int[] $labels
     */
    public function __construct(private readonly array $labels)
    {
    }

    /**
     * @return array<int|string, mixed>
     */
    public function transform($from): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(__METHOD__ . " argument is not an array.");
        }

        if (count($value) !== count($this->labels)) {
            throw new InvalidArgumentException(__METHOD__ . " number of items in arrays are not equal.");
        }

        return array_combine($this->labels, $from);
    }
}
