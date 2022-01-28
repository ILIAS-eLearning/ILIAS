<?php declare(strict_types=1);

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\ViewControl;

use ILIAS\UI\Component\Component;

/**
 * This describes a Mode Control
 */
interface Mode extends Component
{
    /**
     * set the currently active/engaged Button by label.
     *
     * @param string $label. The label of the button to activate
     */
    public function withActive(string $label) : Mode;

    /**
     * get the label of the currently active/engaged button of the mode control
     *
     * @return string the label of the currently active button of the mode control
     */
    public function getActive() : ?string;

    /**
     * Get the array containing the actions and labels of the mode control
     *
     * @return array (string|string)[]. Array containing keys as label and values as actions.
     */
    public function getLabelledActions() : array;

    /**
    * Get the aria-label on the ViewControl
    */
    public function getAriaLabel() : string;
}
