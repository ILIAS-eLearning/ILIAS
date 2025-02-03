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
 *   Example for rendering a fixed size Progress Meter with minimum configuration.
 *
 * expected output: >
 *   ILIAS shows a rounded progress meter with a red bar. The bar takes up three quarter of the progress meter.
 *   You can see the information "75%" within the progress meter.
 *
 *   Changing the browser window's size will not change the size of the progress meter: the display stays the same!
 * ---
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive progressmeter
    $progressmeter = $f->chart()->progressMeter()->fixedSize(100, 75);

    // render
    return $renderer->render($progressmeter);
}
