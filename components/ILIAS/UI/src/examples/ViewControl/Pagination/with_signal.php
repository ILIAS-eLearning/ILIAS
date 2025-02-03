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

namespace ILIAS\UI\examples\ViewControl\Pagination;

/**
 * ---
 * description: >
 *   Example for rendering a pagination view control with a signal
 *
 * expected output: >
 *   ILIAS shows a series of numbers 1-10 in between the "Back" (<) and "Next" (>) glyph. Clicking a number  or a glyph
 *   will open a modal including an image.
 * ---
 */
function with_signal()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $factory->image()->responsive("assets/ui-examples/images/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
    $page = $factory->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $factory->modal()->lightbox($page);

    $pagination = $factory->viewControl()->pagination()
        ->withTotalEntries(98)
        ->withPageSize(10)
        ->withResetSignals()
        ->withOnSelect($modal->getShowSignal());

    return $renderer->render([$pagination, $modal]);
}
