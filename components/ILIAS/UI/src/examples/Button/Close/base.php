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

namespace ILIAS\UI\examples\Button\Close;

/**
 * ---
 * description: >
 *   This example is rather artificial, since the close button is only used
 *   in other components (see purpose).
 *
 * expected output: >
 *   ILIAS a dark grey "X" in the right corner. Clicking the "X" won't activate any action.
 *
 * note: >
 *  In some cases, additional CSS will be required for placing the button
 *  properly by the surrounding component.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->close());
}
