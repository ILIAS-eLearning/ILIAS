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
 *   Example for rendering a responsive image with additional high resolution sources.
 *
 * expected output: >
 *   Example showing different card sizes which use an image with additional
 *   high resolution sources. The selected version of the image depends on the
 *   space available, meaning that the large image version is displayed on very
 *   large screens (even if the browser does not use the entire screen width).
 *   On very small screens, the small image version is displayed.
 *   The effect is best seen on desktop devices:
 *   Open the browser's developer tools ("Inspect" or F12). Select a screen
 *   width (very small, medium, large, extra-large) one after the other, reload
 *   the page each time and check the image source (img src) of the image.
 *   Depending on the screen width, different versions of the image are loaded
 *   here (144w, 301w, 602w, original).
 * ---
 */
function with_additional_high_resolution_source(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $factory
        ->image()
        ->responsive('assets/ui-examples/images/Image/mountains-144w.jpg', 'Mountains')
        ->withAdditionalHighResSource('assets/ui-examples/images/Image/mountains-301w.jpg', 100)
        ->withAdditionalHighResSource('assets/ui-examples/images/Image/mountains-602w.jpg', 300)
        ->withAdditionalHighResSource('assets/ui-examples/images/Image/mountains.jpg', 500);

    $card = $factory->card()->standard('Mountains', $image);

    // render each card individually so every image has a different id.
    return
        '<div style="width: 100%; display: flex; justify-content: space-between">' .
        '<div style="width: 49%;">' . $renderer->render($card) . '</div>' .
        '<div style="width: 30%;">' . $renderer->render($card) . '</div>' .
        '<div style="width: 19%;">' . $renderer->render($card) . '</div>' .
        '</div>';
}
