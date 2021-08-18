<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

/**
 * Trait for adding view controls to a component
 */
interface HasViewControls
{
    /**
     * Add View Controls
     *
     * @param array $view_controls Array Of ViewControls
     * @return \ILIAS\UI\Component\Component
     */
    public function withViewControls(array $view_controls) : HasViewControls;

    /**
     * Get View Controls
     *
     * @return array Array of ViewControls
     */
    public function getViewControls() : ?array;
}
