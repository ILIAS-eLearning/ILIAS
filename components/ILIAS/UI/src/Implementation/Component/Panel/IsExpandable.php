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

/**
 * Trait for panels which are expandable
 */
trait IsExpandable
{
    protected bool $expandable = false;
    protected bool $expanded = false;
    protected string $expand_action = "";
    protected string $collapse_action = "";

    public function withExpandable(
        bool $expanded,
        string $expand_action = "",
        string $collapse_action = ""
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

    public function isExpandable(): bool
    {
        return $this->expandable;
    }

    public function isExpanded(): bool
    {
        return $this->expanded;
    }

    public function getExpandAction(): string
    {
        return $this->expand_action;
    }

    public function getCollapseAction(): string
    {
        return $this->collapse_action;
    }
}
