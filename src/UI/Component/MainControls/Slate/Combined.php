<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\MainControls\Slate;

use ILIAS\UI\Component\Divider\Horizontal;

/**
 * This describes the Combined Slate
 */
interface Combined extends Slate
{
    /**
     * @param Slate|Combined $entry
     */
    public function withAdditionalEntry($entry) : Combined;
}
