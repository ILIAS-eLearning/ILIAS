<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\Signal;

/**
 * This describes commonalities between standard and primary buttons.
 */
interface Button extends Component, JavaScriptBindable, Clickable, Hoverable
{
    /**
     * Get the label on the button.
     *
     * @return	string
     */
    public function getLabel();

    /**
     * Get a button like this, but with an additional/replaced label.
     *
     * @param	string	$label
     * @return	Button
     */
    public function withLabel($label);

    /**
     * Get the action of the button, i.e. an URL that the button links to or
     * some signals the button triggers on click.
     *
     * @return	string|(Signal[])
     */
    public function getAction();

    /**
     * Get to know if the button is activated.
     *
     * @return 	bool
     */
    public function isActive();

    /**
     * Get a button like this, but action should be unavailable atm.
     *
     * The button will still have an action afterwards, this might be usefull
     * at some point where we want to reactivate the button client side.
     *
     * @return Button
     */
    public function withUnavailableAction();

    /**
     * Get a button like this, but with an additional/replaced aria-label.
     *
     * @param	string	$aria_label
     * @return	Button
     */
    public function withAriaLabel($aria_label);

    /**
     * Get the aria-label on the button.
     *
     * @return	string
     */
    public function getAriaLabel();

    /**
     * Get a button like this, but setting the aria-checked value as true
     *
     * @return Button
     */
    public function withAriaChecked();

    /**
     * Get to know if the button has the aria-checked attribute
     *
     * @return 	bool
     */
    public function isAriaChecked();

    /**
     * @inheritdocs
     *
     * This will also remove a string action if there currently is one.
     */
    public function withOnClick(Signal $signal);
}
