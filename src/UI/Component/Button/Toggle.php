<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Signal;

//use ILIAS\UI\Implementation\Component\TriggeredSignal;


/**
 * This describes a toggle button.
 */
interface Toggle extends Button
{

    /**
     * Get to know if the Toggle Button is on or off.
     *
     * @return bool
     */
    public function isOn() : bool;

    /**
     * Get the action of the Toggle Button when it is set from off to on.
     *
     * @return	string|Signal[]
     */
    public function getActionOn();

    /**
     * Get the action of the Toggle Button when it is set from on to off.
     *
     * @return	string|Signal[]
     */
    public function getActionOff();

    /**
     * @param Signal $signal
     * @return Toggle
     */
    public function withAdditionalToggleOnSignal(Signal $signal) : Toggle;

    /**
     * @param Signal $signal
     * @return Toggle
     */
    public function withAdditionalToggleOffSignal(Signal $signal) : Toggle;
}
