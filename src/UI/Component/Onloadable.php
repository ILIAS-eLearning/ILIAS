<?php declare(strict_types=1);

namespace ILIAS\UI\Component;

/**
 * Interface Onloadable
 *
 * Describes a component that can trigger signals of other components on load.
 *
 * @package ILIAS\UI\Component
 */
interface Onloadable extends Triggerer
{
    /**
     * Trigger a signal of another component on load
     *
     * @param Signal $signal A signal of another component
     * @return static
     */
    public function withOnLoad(Signal $signal);

    /**
     * Get a component like this, triggering a signal of another component on load.
     * In contrast to withOnLoad, the signal is appended to existing signals for the on load event.
     *
     * @return static
     */
    public function appendOnLoad(Signal $signal);
}
