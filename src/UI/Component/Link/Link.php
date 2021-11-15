<?php declare(strict_types=1);

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
     */
    public function getAction() : string;

    /**
     * Set if link should be opened in new viewport
     */
    public function withOpenInNewViewport(bool $open_in_new_viewport) : Link;

    /**
     * Get if the link should be opened in new viewport
     */
    public function getOpenInNewViewport() : ?bool;
}
