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

namespace ILIAS\UI\examples\Modal\Lightbox;

/**
 * ---
 * description: >
 *   Example for rendering a lightbox modal.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show some fancy images and texts".
 *   A click onto the button will grey out ILIAS and opens a modal including multiple contents which can be displayed by
 *   clicking the arrow glyphs.
 *   All contents have got their own title.
 *   The contents consist of images and texts.
 *   In the lower part of the modal a circle glyph is displayed centered about which you can see that you can look at
 *   five contents within the modal.
 * ---
 */
function show_different_page_types()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $image = $factory->image()->responsive('assets/ui-examples/images/Image/mountains.jpg', 'Nice view on some mountains');
    $page = $factory->modal()->lightboxImagePage(
        $image,
        'Mountains',
        'Image source: https://stocksnap.io, Creative Commons CC0 license'
    );

    $page2 = $factory->modal()->lightboxTextPage('Some text content you have to agree on!', 'User Agreement');

    $image2 = $factory->image()->responsive('assets/ui-examples/images/Image/sanfrancisco.jpg', 'The golden gate bridge');
    $page3 = $factory->modal()->lightboxImagePage(
        $image2,
        'San Francisco',
        'Image source: https://stocksnap.io, Creative Commons CC0 license'
    );

    $page4 = $factory->modal()->lightboxTextPage(
        'Another text content you have to agree on!',
        'Data Privacy Statement'
    );

    $image3 = $factory->image()->responsive('assets/ui-examples/images/Image/ski.jpg', 'Skiing');
    $page5 = $factory->modal()->lightboxImagePage(
        $image3,
        'Ski Fun',
        'Image source: https://stocksnap.io, Creative Commons CC0 license'
    );

    $modal = $factory->modal()->lightbox([$page, $page2, $page3, $page4, $page5]);
    $button = $factory->button()->standard('Show some fancy images and texts', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
