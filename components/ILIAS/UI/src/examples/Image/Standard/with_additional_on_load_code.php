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
 *   Example showing how JS-Code can be attached to images.
 *
 * expected output: >
 *   ILIAS shows an image. An alert pops up as soon as the image is clicked.
 * ---
 */
function with_additional_on_load_code()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the image and modal
    $image = $f->image()->standard(
        "assets/ui-examples/images/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    )->withAction("#")
     ->withAdditionalOnLoadCode(function ($id) {
         return "$('#$id').click(function(e) { e.preventDefault(); alert('Image Onload Code')});";
     });

    $html = $renderer->render($image);

    return $html;
}
