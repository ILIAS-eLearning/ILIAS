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

namespace ILIAS\UI\Component\Breadcrumbs;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Link\Standard;

/**
 * Interface for Breadcrumbs
 * @package ILIAS\UI\Component\Breadcrumbs
 */
interface Breadcrumbs extends Component
{
    /**
     * Get all crumbs.
     *
     * @return 	Standard[]
     */
    public function getItems(): array;

    /**
     * Append a crumb-entry to the bar.
     */
    public function withAppendedItem(Standard $crumb): Breadcrumbs;
}
