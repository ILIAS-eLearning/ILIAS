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
 *   Example for rendering a lightbox image page modal with a single image.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Image".
 *   A click onto the button greys out ILIAS, opens a modal titled "Mountains",
 *   an image and a Copyright note above the image. The modal's background is dark with a light font color.
 * ---
 */
function show_a_single_image()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $image = $factory->image()->responsive("assets/ui-examples/images/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
    $page = $factory->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $factory->modal()->lightbox($page);
    $button = $factory->button()->standard('Show Image', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
