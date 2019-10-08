<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel\Secondary;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Panel\PanelViewControls;

/**
 * This describes a Secondary Panel.
 */
interface Secondary extends C\Component, PanelViewControls
{
    /**
     * Sets a Component being displayed below the content
     * @param \ILIAS\UI\Component\Button\Shy $component
     * @return \ILIAS\UI\Component\Panel\Secondary\Secondary
     */
    public function withFooter(C\Button\Shy $component) : Secondary;

    /**
     * Gets the Component being displayed below the content
     * @return \ILIAS\UI\Component\Button\Shy | null
     */
    public function getFooter() : ?C\Button\Shy;
}
