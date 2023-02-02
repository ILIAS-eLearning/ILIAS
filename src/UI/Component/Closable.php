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

namespace ILIAS\UI\Component;

/**
 * Interface Closacle
 *
 * Describes a component that can trigger signals of other components on close.
 *
 * @package ILIAS\UI\Component
 */
interface Closable extends Triggerer
{
    /**
     * Get a component like this, triggering a signal of another component on close.
     * Note: Any previous signals registered on close are replaced.
     *
     * @param Signal $signal A signal of another component
     * @return static
     */
    public function withOnClose(Signal $signal): self;

    /**
     * Get a component like this, triggering a signal of another component on close.
     * In contrast to withOnClose, the signal is appended to existing signals for the close event.
     *
     * @return static
     */
    public function appendOnClose(Signal $signal): self;
}
