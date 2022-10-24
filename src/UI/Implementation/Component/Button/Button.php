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

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements commonalities between standard and primary buttons.
 */
abstract class Button implements C\Button\Button
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;
    use Engageable;

    protected string $label;
    protected ?string $action;
    protected bool $active = true;
    protected string $aria_label = '';
    protected bool $aria_checked = false;


    public function __construct(string $label, $action)
    {
        $this->checkStringOrSignalArg("action", $action);
        $this->label = $label;
        if (is_string($action)) {
            $this->action = $action;
        } else {
            $this->action = null;
            $this->setTriggeredSignal($action, "click");
        }
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withLabel(string $label): C\Button\Button
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAction()
    {
        if ($this->action !== null) {
            return $this->action;
        }
        return $this->getTriggeredSignalsFor("click");
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function withUnavailableAction(): C\Button\Button
    {
        $clone = clone $this;
        $clone->active = false;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnClick(Signal $signal): C\Button\Button
    {
        $this->action = null;
        return $this->withTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function appendOnClick(Signal $signal): C\Clickable
    {
        return $this->appendTriggeredSignal($signal, 'click');
    }

    /**
     * @inheritdoc
     */
    public function withOnHover(Signal $signal): C\Hoverable
    {
        // Note: The event 'hover' maps to 'mouseenter' in javascript. Although 'hover' is available in JQuery,
        // it encodes the 'mouseenter' and 'mouseleave' events and thus expects two event handlers.
        // In the context of this framework, the signal MUST only be triggered on the 'mouseenter' event.
        // See also: https://api.jquery.com/hover/
        return $this->withTriggeredSignal($signal, 'mouseenter');
    }

    /**
     * @inheritdoc
     */
    public function appendOnHover(Signal $signal): C\Hoverable
    {
        return $this->appendTriggeredSignal($signal, 'mouseenter');
    }

    /**
     * @inheritdoc
     */
    public function withAriaLabel(string $aria_label): C\Button\Button
    {
        $clone = clone $this;
        $clone->aria_label = $aria_label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getAriaLabel(): string
    {
        return $this->aria_label;
    }
}
