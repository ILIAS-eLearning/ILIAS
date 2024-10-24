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
 *   Example for rendering a standard Progress Meter with an comparison value only
 *
 * expected output: >
 *   ILIAS shows a rounded progress meter with two bars. The outer bar is colored red and is displayed as a dot (equals 0%).
 *   The inner bar is colored yellow and takes up half of the progress meter. A triangle within the progress meter marks
 *   the needed value at 75%. The informations "0%" and "75%" are also positioned within the progress meter.
 *
 *   Changing the size of the browser window will change the size of the progress meter: it gets smaller or bigger.
 * ---
 */
function only_comparison_value()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the standard progressmeter
    $progressmeter = $f->chart()->progressMeter()->standard(100, 0, 75, 50);

    // render
    return $renderer->render($progressmeter);
}
