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
 *   Example for rendering a shy item with a long description.
 *
 * expected output: >
 *   ILIAS shows a box highlighted white and including the text "Test shy Item". Additionally a multi-lined description
 *   is displayed below the text.
 * ---
 */
function with_long_description()
{
    global $DIC;

    return $DIC->ui()->renderer()->render(
        $DIC->ui()->factory()->item()->shy('Test shy Item')->withDescription(
            'This is a test shy of the root user in the current time. This is a little bit longer than
            the usual to test its visual presentation when the content exceed a minor amount of chars to see if this
            is still presented properly. This may affect its presentation inside a mobile or restricted view therefore
            the presentation of a long text is necessary to test within this example to show its responsivity above all
            views and to show the using developer, who is accessing this example for exact those information if the
            component can be used for his target purpose.'
        )
    );
}
