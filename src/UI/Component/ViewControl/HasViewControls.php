<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;

/**
 * Trait for adding view controls to a component
 */
interface HasViewControls extends Component
{
    /**
     * Add View Controls
     *
     * @param array $view_controls Array Of ViewControls
     * @return static
     */
    public function withViewControls(array $view_controls);

    /**
     * Get View Controls
     *
     * @return array|null Array of ViewControls
     */
    public function getViewControls() : ?array;
}
