<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\ViewControl;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Component\Triggerer;

/**
 * This describes the basis of all View Control Inputs.
 */
interface ViewControl extends Component, Input, Triggerer
{
    /**
     * Trigger this signal when the control is being operated.
     */
    public function withOnChange(Signal $signal) : ViewControl;
}
