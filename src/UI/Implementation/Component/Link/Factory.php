<?php

/* Copyright (c) 2017 Alexander Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Component\Link as L;

class Factory implements L\Factory
{
    /**
     * @inheritdoc
     */
    public function standard($label, $action)
    {
        return new Standard($label, $action);
    }
}
