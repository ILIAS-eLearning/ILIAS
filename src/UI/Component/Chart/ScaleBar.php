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

namespace ILIAS\UI\Component\Chart;

use ILIAS\UI\Component\Component;

/**
 * Interface Scale Bars
 */
interface ScaleBar extends Component
{
    /**
     * Sets a key value pair as items for the list. Key is used as title and value is a boolean marking highlighted values.
     * @param array string => boolean Set of elements to be rendered, boolean should be true if highlighted
     */
    public function withItems(array $items): ScaleBar;

    /**
     * Gets the key value pair as array. Key is used as title and value is a boolean marking highlighted values.
     * @return array $items string => boolean
     */
    public function getItems(): array;
}
