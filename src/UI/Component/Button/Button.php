<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

use ILIAS\UI\Component\Clickable;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Hoverable;
use ILIAS\UI\Component\JavaScriptBindable;

/**
 * This describes commonalities between standard and primary buttons.
 */
interface Button extends Component, JavaScriptBindable, Clickable, Hoverable, Engageable
{
    /**
     * Get the label on the button.
     */
    public function getLabel() : string;

    /**
     * Get a button like this, but with an additional/replaced label.
     */
    public function withLabel(string $label) : Button;

    /**
     * Get the action of the button, i.e. an URL that the button links to or
     * some signals the button triggers on click.
     *
     * @return	string|(Signal[])
     */
    public function getAction();

    /**
     * Get to know if the button is activated.
     */
    public function isActive() : bool;

    /**
     * Get a button like this, but action should be unavailable atm.
     *
     * The button will still have an action afterwards, this might be usefull
     * at some point where we want to reactivate the button client side.
     */
    public function withUnavailableAction() : Button;

    /**
     * Get a button like this, but with an additional/replaced aria-label.
     */
    public function withAriaLabel(string $aria_label) : Button;

    /**
     * Get the aria-label on the button.
     */
    public function getAriaLabel() : string;
}
