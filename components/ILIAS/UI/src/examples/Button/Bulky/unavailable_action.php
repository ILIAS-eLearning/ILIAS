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

namespace ILIAS\UI\examples\Button\Bulky;

/**
 * ---
 * description: >
 *   This example provides the given button with an unavailable action.
 *
 * expected output: >
 *   ILIAS shows a button with a glyph and the title "Unavailable". The button's background is grayed out.
 *   Clicking the button won't activate any actions.
 *
 * note: >
 *   The disabled attribute is set in the DOM.
 *   No action must be fired, even if done by keyboard.
 * ---
 */
function unavailable_action()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->attachment();
    $button = $f->button()->bulky($glyph, 'Unavailable', '#')->withUnavailableAction();

    return $renderer->render([$button]);
}
