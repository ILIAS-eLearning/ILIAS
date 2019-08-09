<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Divider;

use ILIAS\UI\Component\Divider as D;

class Factory implements D\Factory
{
    /**
     * @inheritdoc
     */
    public function horizontal()
    {
        return new Horizontal();
    }

    /**
     * @inheritdoc
     */
    public function vertical()
    {
        return new Vertical();
    }
}
