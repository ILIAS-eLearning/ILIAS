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
     *     The Form will be shown in an Interruptive Modal when clicking the Primary Button.
     *     On Submitting the modal, either a message is being displayed (in case of error)
     *     or the process/object is launched.
     *   rivals:
     *     Modal Launcher: >
     *       Modal Launcher is visually only present via a Button and provides the required
     *       information in a modal when the Button is clicked. Hence it is useful for
     *       workflows where multiple launchable objects or workflows are presented and
     *       the user choses among them.
     * ---
     * @return \ILIAS\UI\Component\Launcher\Inline
     */
    public function inline(Link $target): Inline;

    /**
     * ---
     * description:
     *   purpose: >
     *     The Modal Launcher is presented via a Button, where the other information
     *     are presented in a Modal that opens one the button is clicked. Hence it can
     *     be used in cases, where multiple workflows and objects are presented and the
     *     the user is expected to chose among them. The Modal Launcher allows to do
     *     so without changing views.
     *   effect: >
     *     Upon click on the button, the Modal Launcher will open an Interruptive Modal
     *     and display title, desription and status information and, if configured, the
     *     inputs.
     *   rivals:
     *     Inline Launcher: >
     *       The Inline Launcher also identifies an object/process. The Modal Launcher
     *       is only presented via a Button and hence requires other means to be identified
     *       by the user correctly, such as Items or Cards.
     *     Interruptive Modal: >
     *       The Interruptive Modal is meant to display objects affected by a critical action. 
     *       The Modal Launcher presents relevant information to identify an object/process to launch
     *       or communicates that the intended launching action is permitted.
     * ---
     * @return \ILIAS\UI\Component\Launcher\Modal
     */
    public function modal(Link $target): Modal;
}
