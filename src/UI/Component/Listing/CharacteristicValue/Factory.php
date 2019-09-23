<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Listing\CharacteristicValue;

use ILIAS\UI\Component\Listing\CharacteristicValue\Text as CharacteristicValueText;

/**
 * This is the interface for a characteristic value factory.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Characteristic Value Text Listing is a listing that takes labeled
     *     characteristic values that are displayed side by side.
     *   composition: >
     *     Characteristic Value Text Listing are composed of items containing
     *     a key labeling the characteristic value where the labels as well as
     *     the values itself are expected as strings.
     *   effect: >
     *     The items will be presented underneath, whereby each items' label and value
     *     will be presented side by side.
     *
     * ----
     *
     * @param array $items string => string
     *
     * @return CharacteristicValueText
     */
    public function text(array $items) : CharacteristicValueText;
}
