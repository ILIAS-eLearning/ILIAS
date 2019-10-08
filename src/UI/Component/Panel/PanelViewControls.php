<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Panel;

/**
 * This describes a Standard Panel.
 */
interface PanelViewControls
{
    /**
     * Add View Controls to panel
     *
     * @param array $view_controls Array Of ViewControls
     * @return \ILIAS\UI\Component\Component
     */
    public function withViewControls(array $view_controls) : \ILIAS\UI\Component\Component;

    /**
     * Get view controls to be shown in the header of the panel.
     *
     * @return array Array of ViewControls
     */
    public function getViewControls(): ?array;
}
