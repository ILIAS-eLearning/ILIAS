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
     * @return static
     */
    public function withResetSignals();
}
