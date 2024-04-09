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

namespace ILIAS\UI\Component\Panel;

use ILIAS\UI\Component\Component;

/**
 * Trait for making Panels expandable
 */
interface IsExpandable extends Component
{
    /**
     * Make the Panel expandable. Set the Panel expanded or collapsed. Set optionally an expand and collapse action
     * to handle e.g. the storage of the expand/collapse status in the session. The actions are handled asynchronously.
     */
    public function withExpandable(
        bool $expanded,
        string $expand_action = "",
        string $collapse_action = ""
    );

    /**
     * Is the Panel expandable?
     */
    public function isExpandable(): bool;

    /**
     * Is the Panel expanded?
     */
    public function isExpanded(): bool;

    /**
     * Get the expand action of the Panel
     */
    public function getExpandAction(): string;

    /**
     * Get the collapse action of the Panel
     */
    public function getCollapseAction(): string;
}
