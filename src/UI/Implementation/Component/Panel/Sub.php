<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * Class Sub
 * @package ILIAS\UI\Implementation\Component\Sub
 */
class Sub extends Panel implements C\Panel\Sub
{
    use ComponentHelper;

    /**
     * Component to be displayed on the right of the Sub Panel
     * @var C\Card\Card | C\Panel\Secondary\Secondary
     */
    private $component = null;

    /**
     * @inheritdoc
     */
    public function withFurtherInformation($component)
    {
        $clone = clone $this;
        $clone->component = $component;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getFurtherInformation()
    {
        return $this->component;
    }
}
