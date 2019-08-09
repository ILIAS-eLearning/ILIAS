<?php
namespace ILIAS\UI\Component;

use ILIAS\UI\Implementation\Component\TriggeredSignal;

/**
 * Interface Triggerer
 *
 * Describes a component that can trigger signals of other components on given events, such as click or hover.
 * All supported events are abstracted with interfaces (see Clickable, Hoverable).
 * Example: A button can trigger the show signal of a modal on click (which will open the modal on button click).
 *
 * @package ILIAS\UI\Component
 */
interface Triggerer extends JavaScriptBindable
{

    /**
     * Get a component like this but reset any triggered signals of other components
     *
     * @return $this
     */
    public function withResetTriggeredSignals();

    /**
     * Get all triggered signals of this component
     *
     * @return TriggeredSignal[]
     */
    public function getTriggeredSignals();
}
