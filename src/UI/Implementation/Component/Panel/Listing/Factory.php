<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component\Panel as P;
use ILIAS\UI\NotImplementedException;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements \ILIAS\UI\Component\Panel\Listing\Factory
{

    /**
     * @inheritdoc
     */
    public function standard($title, $items)
    {
        return new Standard($title, $items);
    }
}
