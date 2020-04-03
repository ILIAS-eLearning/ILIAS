<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This describes a bulky button.
 */
interface Bulky extends Button, Engageable
{
    // allowed ARIA roles
    const MENUITEM = 'menuitem';
    const MENUITEM_SEARCH = 'menuitem search';

    /**
     * Get the icon or glyph the button was created with.
     *
     * @return ILIAS\UI\Component\Symbol\Symbol
     */
    public function getIconOrGlyph();

    /**
     * Get a button like this, but with an additional ARIA role.
     *
     * @param	string	$aria_role
     * @return	Button
     */
    public function withAriaRole(string $aria_role);

    /**
     * Get the ARIA role on the button.
     *
     * @return	string
     */
    public function getAriaRole();
}
