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
     * @return static
     */
    public function withOnHover(Signal $signal);

    /**
     * Get a component like this, triggering a signal of another component on hover.
     * In contrast to withOnHover, the signal is appended to existing signals for the hover event.
     *
     * @return static
     */
    public function appendOnHover(Signal $signal);
}
