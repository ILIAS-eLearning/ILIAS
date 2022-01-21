<?php declare(strict_types=1);

/*Copyright (c) 2017 Jesús López <lopez@leifos.de> Extended GPL, see docs/LICENSE. */

namespace ILIAS\UI\Implementation\Component\ViewControl;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Button\Month;
use ILIAS\UI\Component\Button\Button;

class Section implements C\ViewControl\Section
{
    use ComponentHelper;

    protected Button $previous_action;
    protected Component $button;
    protected Button $next_action;

    public function __construct(Button $previous_action, Component $button, Button $next_action)
    {
        if (!$button instanceof Month) {
            $this->checkArgInstanceOf("button", $button, Button::class);
        }
        $this->previous_action = $previous_action;
        $this->button = $button;
        $this->next_action = $next_action;
    }

    /**
     * Returns the action executed by clicking on previous.
     */
    public function getPreviousActions() : Button
    {
        return $this->previous_action;
    }

    /**
     * Returns the action executed by clicking on next.
     */
    public function getNextActions() : Button
    {
        return $this->next_action;
    }

    /**
     * Returns the Default- or Split-Button placed in the middle of the control
     *
     * @return Component the Default- or Split-Button placed in the middle of the control
     */
    public function getSelectorButton() : Component
    {
        return $this->button;
    }
}
