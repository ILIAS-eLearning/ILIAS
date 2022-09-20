<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getPreviousActions(): Button
    {
        return $this->previous_action;
    }

    /**
     * Returns the action executed by clicking on next.
     */
    public function getNextActions(): Button
    {
        return $this->next_action;
    }

    /**
     * Returns the Default- or Split-Button placed in the middle of the control
     *
     * @return Component the Default- or Split-Button placed in the middle of the control
     */
    public function getSelectorButton(): Component
    {
        return $this->button;
    }
}
