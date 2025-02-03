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

namespace ILIAS\UI\examples\MessageBox\Confirmation;

/**
 * ---
 * description: >
 *   Example for rendering a confirmation message box.
 *
 * expected output: >
 *   ILIAS shows a yellow box with a dummy text and two buttons.
 *   Clicking the buttons does not do anything.
 * ---
 */
function confirmation()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buttons = [$f->button()->standard("Confirm", "#"), $f->button()->standard("Cancel", "#")];

    return $renderer->render($f->messageBox()->confirmation("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")->withButtons($buttons));
}
