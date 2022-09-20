<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Component\Listing\CharacteristicValue;

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
     * ----
     * @param array $items string => string
     * @return \ILIAS\UI\Component\Listing\CharacteristicValue\Text
     */
    public function text(array $items): Text;
}
