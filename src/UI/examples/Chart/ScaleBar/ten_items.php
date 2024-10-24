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

namespace ILIAS\UI\examples\Chart\ScaleBar;

/**
 * ---
 * description: >
 *   Example for rendering a scale bar with ten items
 *
 * expected output: >
 *   ILIAS shows ten equal stripes with a number between 0-9 each.
 *   Stripe "6" is particularly highlighted. No stripe is clickable.
 * ---
 */
function ten_items()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $c = $f->chart()->scaleBar(
        array(
            "0" => false,
            "1" => false,
            "2" => false,
            "3" => false,
            "4" => false,
            "5" => false,
            "6" => true,
            "7" => false,
            "8" => false,
            "9" => false
        )
    );

    //Render
    return $renderer->render($c);
}
