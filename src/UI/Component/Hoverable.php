<?php
namespace ILIAS\UI\Component;

/**
 * Interface Hoverable
 *
 * Describes a component that can trigger signals of other components on hover.
 *
 * @package ILIAS\UI\Component
 */
interface Hoverable extends Triggerer
{

    /**
     * Get a component like this, triggering a signal of another component on hover.
     * Note: Any previous signals registered on hover are replaced.
     *
     * @param Signal $signal A signal of another component
     *
     * @return $this
     */
    public function withOnHover(Signal $signal);

    /**
     * Get a component like this, triggering a signal of another component on hover.
     * In contrast to withOnHover, the signal is appended to existing signals for the hover event.
     *
     * @param Signal $signal
     *
     * @return $this
     */
    public function appendOnHover(Signal $signal);
}
