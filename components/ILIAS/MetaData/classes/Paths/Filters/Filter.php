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

namespace ILIAS\MetaData\Paths\Filters;

class Filter implements FilterInterface
{
    protected FilterType $type;
    /**
     * @var string[]
     */
    protected array $values;

    public function __construct(FilterType $type, string ...$values)
    {
        $this->type = $type;
        $this->values = $values;
    }

    public function type(): FilterType
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function values(): \Generator
    {
        yield from $this->values;
    }
}
