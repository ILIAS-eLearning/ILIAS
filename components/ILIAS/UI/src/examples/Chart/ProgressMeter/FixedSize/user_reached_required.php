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

namespace ILIAS\UI\examples\Chart\ProgressMeter\FixedSize;

/**
 * ---
 * description: >
 *   Example for rendering a fixed size Progress Meter when a specific score was reached
 *
 * expected output: >
 *   ILIAS shows a base progress meter. The bar takes up 80% of the display. A triangle
 *   marks the needed value at 75%. The information "80%" and "75%" are positioned within the progress meter.
 *
 *   Changing the browser window's size will not change the size of the progress meter: the display stays the same!
 * ---
 */
function user_reached_required()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive progressmeter
    $progressmeter = $f->chart()->progressMeter()->fixedSize(100, 80, 75);

    // render
    return $renderer->render($progressmeter);
}
