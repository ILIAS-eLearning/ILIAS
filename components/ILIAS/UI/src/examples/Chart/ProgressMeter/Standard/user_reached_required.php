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

namespace ILIAS\UI\examples\Chart\ProgressMeter\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard Progress Meter when the required value was reached
 *
 * expected output: >
 *   ILIAS shows a base progress meter with a green colored bar. The bar takes up 80% of the progress meter. A triangle
 *   marks the needed value at 75%. The information "80%" and "75%" are positioned within the progress meter.
 *
 *   Changing the size of the browser window will change the size of the progress meter: it gets smaller or bigger.
 * ---
 */
function user_reached_required()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the standard progressmeter
    $progressmeter = $f->chart()->progressMeter()->standard(100, 80, 75);

    // render
    return $renderer->render($progressmeter);
}
