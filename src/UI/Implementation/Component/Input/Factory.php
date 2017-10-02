<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component;

class Factory implements Component\Input\Factory {
    /**
     * @inheritdoc
     */
    public function field()
    {
        return new Field\Factory();
    }

    /**
     * @inheritdoc
     */
    public function container()
    {
        return new Container\Factory();
    }
}
