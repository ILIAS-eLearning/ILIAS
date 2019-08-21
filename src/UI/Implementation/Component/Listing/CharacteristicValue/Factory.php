<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\CharacteristicValue;

use ILIAS\UI\Component as C;

/**
 * Factory for report listings
 * @package ILIAS\UI\Implementation\Component\Listing\Report
 */
class Factory implements C\Listing\CharacteristicValue\Factory
{
    /**
     * @inheritdoc
     */
    public function text(array $items)
    {
        return new Text($items);
    }
}
