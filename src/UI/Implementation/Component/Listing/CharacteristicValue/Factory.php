<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\CharacteristicValue;

use ILIAS\UI\Component as C;

/**
 * Factory for characteristic value listings
 * @package ILIAS\UI\Implementation\Component\Listing\CharacteristicValue
 */
class Factory implements C\Listing\CharacteristicValue\Factory
{
    /**
     * @inheritdoc
     */
    public function text(array $items) : \ILIAS\UI\Component\Listing\CharacteristicValue\Text
    {
        return new Text($items);
    }
}
