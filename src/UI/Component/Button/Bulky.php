<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * This describes a bulky button.
 */
interface Bulky extends Button
{

    /**
     * Get the icon or glyph the button was created with.
     *
     * @return ILIAS\UI\Component\Icon\Icon | \ILIAS\UI\Component\Glyph\Glyph
     */
    public function getIconOrGlyph();

    /**
     * The button is stateful (engaged or disengaged).
     * Get a copy of Bulky Button with engaged state for $state=true
     * and with disengaged state for $state=false.
     *
     * @param 	bool 	$state
     * @return 	Bulky
     */
    public function withEngagedState($state);

    /**
     * Return whether the button is currently engaged.
     *
     * @return 	bool
     */
    public function isEngaged();
}
