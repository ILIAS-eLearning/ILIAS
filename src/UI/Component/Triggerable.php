<?php
namespace ILIAS\UI\Component;

/**
 * Interface Triggerable
 *
 * Describes a component offering signals that can be triggered by other components on events.
 * Example: A modal offers signals to show and close the modal.
 *
 * A signal is represented by a unique string identifier and may offer some options which can be passed
 * by a triggerer component when triggering the signal.
 *
 * @package ILIAS\UI\Component
 */
interface Triggerable extends JavaScriptBindable
{

    /**
     * Get a component like this but reset (regenerate) its signals.
     *
     * @return $this
     */
    public function withResetSignals();
}
