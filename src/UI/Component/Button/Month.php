<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Component;

/**
 * This describes the Month Button. Note that actions are bound to the month by using the JavaScriptBindable
 * withOnLoadCodeFunction.
 */
interface Month extends Component, JavaScriptBindable
{

    /**
     * Get the default value of the button
     *
     * @return	string
     */
    public function getDefault();
}
