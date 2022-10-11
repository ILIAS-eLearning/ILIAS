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
     *     Clicking the Primary Button starts an object or a process.
     *     If the Component is configured with inputs, this will be communicated
     *     to the user with an appropriate icon.
     *     The Form will be shown in an Interruptive Modal when clicking the Primary Button.
     *     On Submitting the modal, either a message is being displayed (in case of error)
     *     or the process/object is launched.
     *   rivals:
     *     Modal Launcher: >
     *       The Modal Launcher will inform the user about a process after the basic
     *       identification of the respective object was made or will further specify 
     *       the initially chosen action.
     * rules:
     *   usage:
     *     1: The Launcher SHOULD be nested in other Components like e.g. Panels or Cards.
     *
     * ---
     * @return \ILIAS\UI\Component\Launcher\Inline
     */
    public function inline(Link $target): Inline;

    /**
     * ---
     * description:
     *   purpose: >
     *     For further specification of a process or to avoid another view change,
     *     the Modal Launcher can be used to show its contents wrapped within a Modal.
     *   effect: >
     *     Upon Signal, the Modal Launcher will open an Interruptive Modal and
     *     displays a title, a desription, status information and a Primary Button.
     *     If Inputs are configured with the Launcher, the resulting Form replaces
     *     the contents of the Modal when the Primary Button is clicked.
     *     Submitting the Form starts the object or process.
     *   rivals:
     *     Inline Launcher: >
     *       The Inline Launcher also identifies an object/process. The Modal Launcher
     *       is a reaction to an interaction with another Component (e.g. item)
     *     Interruptive Modal: >
     *       The Interruptive Modal is meant to display objects affected by a critical action. 
     *       The Modal Launcher presents relevant information to identify an object/process to launch
     *       or communicates that the intended launching action is permitted.
     * context:
     *   - List of launchable items
     *
     * ---
     * @return \ILIAS\UI\Component\Launcher\Modal
     */
    public function modal(Link $target): Modal;
}
