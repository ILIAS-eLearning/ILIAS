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

namespace ILIAS\UI\examples\Button\Toggle;

/**
 * ---
 * description: >
 *   Example for rendering a Toggle Button.
 *
 * expected output: >
 *   ILIAS shows a switch. Clicking onto the switch will result in the switch moving to the right side and opening a
 *   dialog: "Toggle Button has been turned on".
 *   Closing the dialog via "delete" will move back the switch. The switch will stay on the right side if the dialog
 *   is closed via "close".
 *   Another click onto the switch will move the switch to the left side. ILIAS notifies: "Toggle Button has been turned off".
 * ---
 */
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message1 = 'Toggle Button has been turned on';
    $message2 = 'Toggle Button has been turned off';
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    $modal = $factory->modal()->interruptive('ON', $message1, $form_action);
    $modal2 = $factory->modal()->interruptive('OFF', $message2, $form_action);

    //Note, important do not miss to set a proper aria-label (see rules above).
    //Note that aria-pressed is taken care off by the default implementation.
    $button = $factory->button()->toggle("", $modal->getShowSignal(), $modal2->getShowSignal())
        ->withAriaLabel("Switch the State of XY");

    return $renderer->render([$button, $modal, $modal2]);
}
