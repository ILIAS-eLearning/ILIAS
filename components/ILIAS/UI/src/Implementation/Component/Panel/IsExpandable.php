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

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component\Panel\IsExpandable as IsExpandableInterface;
use ILIAS\Data\URI;
use ILIAS\UI\Component\Signal;

/**
 * Trait for panels which are expandable
 */
trait IsExpandable
{
    protected bool $expandable = false;
    protected bool $expanded = false;
    protected URI | Signal | null $expand_action = null;
    protected URI | Signal | null $collapse_action = null;

    public function withExpandable(
        bool $expanded,
        URI | Signal | null $expand_action = null,
        URI | Signal | null $collapse_action = null
    ): IsExpandableInterface {
        /**
         * @var $clone IsExpandableInterface
         */
        $clone = clone $this;
        $clone->expandable = true;
        $clone->expanded = $expanded;
        $clone->expand_action = $expand_action;
        $clone->collapse_action = $collapse_action;
        return $clone;
    }

    /**
     * Is the Panel expandable?
     */
    public function isExpandable(): bool
    {
        return $this->expandable;
    }

    /**
     * Is the Panel expanded?
     */
    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    /**
     * Get the expand action of the Panel
     */
    public function getExpandAction(): URI | Signal | null
    {
        return $this->expand_action;
    }

    /**
     * Get the collapse action of the Panel
     */
    public function getCollapseAction(): URI | Signal | null
    {
        return $this->collapse_action;
    }
}
