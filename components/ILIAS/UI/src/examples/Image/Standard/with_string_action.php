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

namespace ILIAS\UI\examples\Image\Standard;

/**
 * ---
 * description: >
 *   Example for rendering an Image with a string as action
 *
 * expected output: >
 *   Clicking onto the rendered image will open a new tab to ilias.de.
 * ---
 */
function with_string_action()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image and modal
    $image = $f->image()->standard(
        "assets/ui-examples/images/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    )->withAction("https://www.ilias.de");

    $html = $renderer->render($image);

    return $html;
}
