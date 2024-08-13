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

namespace ILIAS\MetaData\Search\Filters;

class Filter implements FilterInterface
{
    protected int|Placeholder $obj_id;
    protected int|Placeholder $sub_id;
    protected string|Placeholder $type;

    public function __construct(
        int|Placeholder $obj_id,
        int|Placeholder $sub_id,
        string|Placeholder $type
    ) {
        $this->obj_id = $obj_id;
        $this->sub_id = $sub_id;
        $this->type = $type;
    }

    public function objID(): int|Placeholder
    {
        return $this->obj_id;
    }

    public function subID(): int|Placeholder
    {
        return $this->sub_id;
    }

    public function type(): string|Placeholder
    {
        return $this->type;
    }
}
