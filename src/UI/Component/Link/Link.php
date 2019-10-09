<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Link;

use ILIAS\UI\Component\Component;

/**
 * Link base interface.
 */
interface Link extends Component
{
    /**
     * Get the action url of a link
     *
     * @return	string
     */
    public function getAction();

    /**
     * Set if link should be opened in new viewport
     * @param bool $open_in_new_viewport
     * @return Link
     */
    public function withOpenInNewViewport($open_in_new_viewport);

    /**
     * Get if the link should be opened in new viewport
     * @return bool
     */
    public function getOpenInNewViewport();
}
