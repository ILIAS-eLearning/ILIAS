<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls\Slate;

use ILIAS\UI\Component\Link\Bulky;

/**
 * This describes the Combined Slate
 */
interface Combined extends Slate
{
    /**
     * @param Slate|Combined|Bulky $entry
     */
    public function withAdditionalEntry($entry, ?string $id = null) : Combined;
}
