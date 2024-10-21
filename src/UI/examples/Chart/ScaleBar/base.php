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
 *   Example of rendering a base scale bar.
 *
 * expected output: >
 *   ILIAS shows four equal stripes with a label each: None, Low, Medium, High.
 *   The "Medium" stripe is particularly highlighted. No stripe is clickable.
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $c = $f->chart()->scaleBar(
        array(
            "None" => false,
            "Low" => false,
            "Medium" => true,
            "High" => false
        )
    );

    //Render
    return $renderer->render($c);
}
