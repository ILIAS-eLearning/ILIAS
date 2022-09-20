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

namespace ILIAS\UI\Component\Listing;

use ILIAS\UI\Component\Component;

interface Listing extends Component
{
    /**
     * Sets the items to be listed
     *
     * @param array $items (Component|string)[]
     */
    public function withItems(array $items): Listing;

    /**
     * Gets the items to be listed
     *
     * @return array $items (Component|string)[]
     */
    public function getItems(): array;
}
