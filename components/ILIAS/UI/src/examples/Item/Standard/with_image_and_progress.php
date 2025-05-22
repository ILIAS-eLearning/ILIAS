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

namespace ILIAS\UI\examples\Item\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard item with an image and displaying the progress.
 *
 * expected output: >
 *   ILIAS shows a box including the following informations: A heading "Item Title" with a dummy text in small writings
 *   ("Lorem ipsum...") below. On the left side a ILIAS icon is displayed, on the right side you can see a pictorial representation
 *   and also a text (75%) about the progress.
 * ---
 */
function with_image_and_progress()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $chart = $f->chart()->progressMeter()->standard(100, 75);
    $app_item = $f->item()->standard("Item Title")
                  ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
                  ->withProgress($chart)
                  ->withLeadImage($f->image()->responsive(
                      "assets/ui-examples/images/Image/HeaderIconLarge.svg",
                      "Thumbnail Example"
                  ));
    return $renderer->render($app_item);
}
