<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Button;

/**
 * A Button can be stateful; when engaged, the state MUST be refelected
 * in the "aria-pressed"-, respectively the "aria-checked"-attribute.
 * If the Button is not stateful (which is the default), the aria-attribute can
 * be omitted.
 * https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/Roles/button_role
 */
interface Engageable
{
    /**
     * Returns whether the button is stateful or not.
     * Engageable must be explicitely turned on by initializing the Button with
     * a state (withEngagedState), since not all Buttons are used as toggles
     * and thus should not bear an aria-pressed attribute.
     */
    public function isEngageable() : bool;

    /**
     * Get a copy of the Engageable Button with engaged state for $state=true
     * and with disengaged state for $state=false.
     */
    public function withEngagedState(bool $state) : Engageable;

    /**
     * Returns whether the button is currently engaged or not.
     */
    public function isEngaged() : bool;
}
