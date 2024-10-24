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

namespace ILIAS\UI\examples\Button\Shy;

/**
 * ---
 * description: >
 *   This example provides the given button with an unavailable action. Note
 *   that the disabled attribute is set in the DOM. No action must be fired,
 *   even if done by keyboard.
 *
 * expected output: >
 *   ILIAS shows a small button titled "Unavailable". The button's background is colored dark grey. Clicking the button
 *   won't activate any actions.
 * ---
 */
function unavailable_action()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $button = $f->button()->shy('Unavailable', '#')->withUnavailableAction();

    return $renderer->render([$button]);
}
