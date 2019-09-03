<?php

declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Refinery\DateTime;

use ILIAS\Refinery\Transformation;

/**
 * @author  Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class Group
{
    public function changeTimezone(string $timezone) : Transformation
    {
        return new ChangeTimezone($timezone);
    }
}
