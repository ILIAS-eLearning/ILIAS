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

namespace ILIAS\UI\examples\Item\Shy;

/**
 * ---
 * description: >
 *   Example for rendering a shy item with a close function.
 *
 * expected output: >
 *   ILIAS renders a base shy item. Additionally a "X" for closing the item is displayed on the right side. If you
 *   click onto the "X" nothing will happen.
 * ---
 */
function with_close()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->shy('Test shy Item')->withClose(
            $DIC->ui()->factory()->button()->close()
        )
    );
}
