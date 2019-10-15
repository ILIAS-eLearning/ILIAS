<?php
/**
 * Interface Droppable
 *
 * Describes a UI component that can handle drop events from the browser.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 * @date    05.05.17
 * @version 0.0.1
 *
 * @package ILIAS\UI\Component
 */

namespace ILIAS\UI\Component;

interface Droppable extends Triggerer
{

    /**
     * Get a component like this, triggering a signal of another component when files have been dropped.
     * Note: Any previous signals registered on drop are replaced.
     *
     * @param Signal $signal a ILIAS UI signal which is used on drop event
     *
     * @return $this
     */
    public function withOnDrop(Signal $signal);


    /**
     * Get a component like this, triggering a signal of another component when files have been dropped.
     * In contrast to withOnDrop, the signal is appended to existing signals for the click event.
     *
     * @param Signal $signal a ILIAS UI signal which is used on drop event
     *
     * @return $this
     */
    public function withAdditionalDrop(Signal $signal);
}
