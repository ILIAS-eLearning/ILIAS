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

namespace ILIAS\UI\examples\Button\Minimize;

/**
 * ---
 * description: >
 *   This example is rather artificial, since the minimize button is only used
 *   in other components (see purpose).
 *   This examples just shows how one could render the button if implementing
 *   such a component
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 *
 * note: >
 *  In some cases, additional CSS will be needed for placing the button
 *  properly by the surrounding component.
 * ---
 */
function base()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->button()->minimize()
    );
}
