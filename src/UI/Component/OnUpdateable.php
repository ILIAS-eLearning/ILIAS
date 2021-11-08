<?php declare(strict_types=1);

namespace ILIAS\UI\Component;

/**
 * Interface OnUpdateable
 *
 * Describes a component that can trigger signals of other components on update.
 *
 * @package ILIAS\UI\Component
 */
interface OnUpdateable extends Triggerer
{
    /**
     * Trigger a signal of another component on update
     *
     * @param Signal $signal A signal of another component
     * @return static
     */
    public function withOnUpdate(Signal $signal);

    /**
     * Get a component like this, triggering a signal of another component on update.
     * In contrast to withOnUpdate, the signal is appended to existing signals for the on update event.
     *
     * @return static
     */
    public function appendOnUpdate(Signal $signal);
}
