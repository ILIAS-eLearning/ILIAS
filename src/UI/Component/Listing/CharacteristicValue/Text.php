<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\CharacteristicValue;

use ILIAS\UI\Component\Component;

/**
 * Interface Text
 */
interface Text extends Component
{
    /**
     * Gets the items as array of key value pairs for the list.
     * Key is used as label for the value.
     *
     * @return array $items string => string
     */
    public function getItems() : array;
}
