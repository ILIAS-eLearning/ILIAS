<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Button;

/**
 * This describes a Section Control
 */
interface Section extends Component
{

    /**
     * Returns the action executed by clicking on previous.
     */
    public function getPreviousActions() : Button;

    /**
     * Returns the action executed by clicking on next.
     */
    public function getNextActions() : Button;

    /**
     * Returns the Default- or Month Button placed in the middle of the control
     *
     * @return Component the Default- or Month Button placed in the middle of the control
     */
    public function getSelectorButton() : Component;
}
