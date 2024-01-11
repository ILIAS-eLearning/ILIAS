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

namespace ILIAS\UI\Component\Menu\Drilldown;

use ILIAS\UI\Component\Menu\Menu;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes a Categorised Items Drilldown Menu Control
 */
interface CategorisedItems extends Menu, JavaScriptBindable
{
    /**
     * Return a Catorised Items with the item filter enabled/disabled. If the
     * filter is enabled, it will be shown instead of the label.
     */
    public function withItemsFilter(bool $enabled): self;
}
