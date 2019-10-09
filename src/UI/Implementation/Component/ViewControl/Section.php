<?php
/*Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE. */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Section implements C\ViewControl\Section
{
    use ComponentHelper;

    protected $previous_action;
    protected $button;
    protected $next_action;

    public function __construct(C\Button\Button $previous_action, \ILIAS\UI\Component\Component $button, C\Button\Button $next_action)
    {
        if (!$button instanceof \ILIAS\UI\Component\Button\Month) {
            $this->checkArgInstanceOf("button", $button, \ILIAS\UI\Component\Button\Button::class);
        }
        $this->previous_action = $previous_action;
        $this->button = $button;
        $this->next_action = $next_action;
    }

    /**
     * Returns the action executed by clicking on previous.
     *
     * @return string action
     */
    public function getPreviousActions()
    {
        return $this->previous_action;
    }

    /**
     * Returns the action executed by clicking on next.
     *
     * @return string action
     */
    public function getNextActions()
    {
        return $this->next_action;
    }

    /**
     * Returns the Default- or Split-Button placed in the middle of the control
     *
     * @return \ILIAS\UI\Component\Component the Default- or Split-Button placed in the middle of the control
     */
    public function getSelectorButton()
    {
        return $this->button;
    }
}
