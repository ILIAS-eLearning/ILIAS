<?php declare(strict_types=1);

namespace ILIAS\UI\Component;

/**
 * Interface Changeable
 *
 * Describes a component that can trigger signals of other components on change.
 *
 * @package ILIAS\UI\Component
 */
interface Changeable extends Triggerer
{
    /**
     * Get a component like this, triggering a signal of another component on change.
     * Note: Any previous signals registered on change are replaced.
     *
     * @param Signal $signal A signal of another component
     */
    public function withOnChange(Signal $signal) : Changeable;

    /**
     * Get a component like this, triggering a signal of another component on change.
     * In contrast to withOnChange, the signal is appended to existing signals for the change event.
     */
    public function appendOnChange(Signal $signal) : Changeable;
}
