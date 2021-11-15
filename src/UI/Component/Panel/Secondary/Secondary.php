<?php declare(strict_types=1);

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component;
use ILIAS\UI\Component\Dropdown;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\ViewControl\HasViewControls;

/**
 * This describes a Secondary Panel.
 */
interface Secondary extends Component\Component, HasViewControls
{
    /**
     * Sets a Component being displayed below the content
     */
    public function withFooter(Shy $component) : Secondary;

    /**
     * Gets the Component being displayed below the content
     */
    public function getFooter() : ?Shy;

    /**
     * Sets the action dropdown to be displayed on the right of the title
     */
    public function withActions(Dropdown\Standard $actions) : Secondary;

    /**
     * Gets the action dropdown to be displayed on the right of the title
     */
    public function getActions() : ?Dropdown\Standard;
}
