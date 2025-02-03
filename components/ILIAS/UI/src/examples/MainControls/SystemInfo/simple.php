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

namespace ILIAS\UI\examples\MainControls\SystemInfo;

use ILIAS\Data\URI;

/**
 * ---
 * description: >
 *   This example show how the UI-Elements itself looks like. For a full
 *   example use the example of the UI-Component Layout\Page\Standard.
 *
 * expected output: >
 *   ILIAS shows a box with a message text.
 *   The message is dismissable with the close-glyph on the right and will vanish
 *   when clicking the glyph.
 * ---
 */
function simple()
{
    //
    //

    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $dismiss_action = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "http://localhost";
    $systemInfo = $f->mainControls()
        ->systemInfo('This is an neutral Message!', 'read it, understand it, dismiss it...')
        ->withDismissAction(new URI($dismiss_action));

    return $renderer->render([$systemInfo]);
}
