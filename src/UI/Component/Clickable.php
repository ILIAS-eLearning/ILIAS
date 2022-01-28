<?php declare(strict_types=1);

namespace ILIAS\UI\Component;

/**
 * Interface Clickable
 *
 * Describes a component that can trigger signals of other components on click.
 *
 * @package ILIAS\UI\Component
 */
interface Clickable extends Triggerer
{
    /**
     * Get a component like this, triggering a signal of another component on click.
     * Note: Any previous signals registered on click are replaced.
     *
     * @param Signal $signal A signal of another component
     * @return static
     */
    public function withOnClick(Signal $signal);

    /**
     * Get a component like this, triggering a signal of another component on click.
     * In contrast to withOnClick, the signal is appended to existing signals for the click event.
     *
     * @return static
     */
    public function appendOnClick(Signal $signal);
}
