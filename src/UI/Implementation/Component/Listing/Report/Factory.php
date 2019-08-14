<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Report;

use ILIAS\UI\Component as C;

/**
 * Factory for report listings
 * @package ILIAS\UI\Implementation\Component\Listing\Report
 */
class Factory implements C\Listing\Report\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(array $items)
    {
        return new Standard($items);
    }

    /**
     * @inheritdoc
     */
    public function mini(array $items)
    {
        return new Mini($items);
    }
}
