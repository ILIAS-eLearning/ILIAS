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

namespace ILIAS\UI\examples\Chart\ProgressMeter\Mini;

/**
 * ---
 * description: >
 *   Example for rendering a mini Progress Meter as part of a headline
 *
 * expected output: >
 *   ILIAS shows a rounded progress meter with a red colored bar and the headline "Your Progress".
 * ---
 */
function headline()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini progressmeter
    $progressmeter = $f->chart()->progressMeter()->mini(100, 75);

    // render
    return '<h3 style="display: inline-block;">Your Progress: </h3><div style="display: inline-block; padding-left: 20px">' .
        $renderer->render($progressmeter) . '</div>';
}
