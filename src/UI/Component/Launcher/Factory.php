<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Component\Launcher;

use ILIAS\Data\Link;

interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *     The Inline Launcher is meant to be used nested within Components (e.g. Cards or Panels).
     *     It provides description and status information at a glance.
     *   effect: >
     *     The Form will be shown in an Interruptive Modal when clicking the Button.
     *     On Submitting the modal, either a message is being displayed (in case of error)
     *     or the process/object is launched.
     * ---
     * @return \ILIAS\UI\Component\Launcher\Inline
     */
    public function inline(Link $target): Inline;
}
