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
class MapValues implements Transformable
{
    public function __construct(private readonly Transformable $trafo)
    {
    }

    public function transform($from)
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException("Argument is not an array.");
        }
        return array_map($this->trafo->transform(...), $from);
    }
}
