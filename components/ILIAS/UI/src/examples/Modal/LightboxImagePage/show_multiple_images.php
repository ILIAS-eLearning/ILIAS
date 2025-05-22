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

namespace ILIAS\UI\examples\Modal\LightboxImagePage;

/**
 * ---
 * description: >
 *   Example for rendering a lightbox image page modal with multiple images.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show some fancy images".
 *   A click onto the button will grey out ILIAS and opens a modal with multiple contents which can be viewed one after
 *   another by clicking the arrow glyphs.
 *   The modal's background is dark with a light font color.
 *   All contents consist of a titled each.
 *   In the lower part of the modal a circle glyph is centered about which you can see that you can look at
 *   three contents within the modal.
 * ---
 */
function show_multiple_images()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $image = $factory->image()->responsive('assets/ui-examples/images/Image/mountains.jpg', 'Nice view on some mountains');
    $page = $factory->modal()->lightboxImagePage($image, 'Mountains', 'Image source: https://stocksnap.io, Creative Commons CC0 license');
    $image2 = $factory->image()->responsive('assets/ui-examples/images/Image/sanfrancisco.jpg', 'The golden gate bridge');
    $page2 = $factory->modal()->lightboxImagePage($image2, 'San Francisco', 'Image source: https://stocksnap.io, Creative Commons CC0 license');
    $image3 = $factory->image()->responsive('assets/ui-examples/images/Image/ski.jpg', 'Skiing');
    $page3 = $factory->modal()->lightboxImagePage($image3, 'Ski Fun', 'Image source: https://stocksnap.io, Creative Commons CC0 license');
    $modal = $factory->modal()->lightbox([$page, $page2, $page3]);
    $button = $factory->button()->standard('Show some fancy images', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
