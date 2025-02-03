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

namespace ILIAS\UI\examples\Image\Responsive;

/**
 * ---
 * description: >
 *  Base example for rendering a responsive Image.
 *
 * expected output: >
 *   ILIAS shows a rendered image. While changing the size of the browser window the image will
 *   decrease in size bit by bit. If the element gets analyzed a text entry for "alt" is shown within the HTML.
 * ---
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the image
    $image = $f->image()->responsive(
        "assets/ui-examples/images/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    );
    $html = $renderer->render($image);

    return $html;
}
