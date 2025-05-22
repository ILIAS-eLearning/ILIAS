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

namespace ILIAS\UI\examples\Modal\LightboxTextPage;

/**
 * ---
 * description: >
 *   Example for rendering a lightbox text page modal with a single text.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Text".
 *   A click onto the button greys out ILIAS and opens the modal titled "User Agreement" and also a text.
 * ---
 */
function show_a_single_text()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $page = $factory->modal()->lightboxTextPage('Some text content you have to agree on!', 'User Agreement');
    $modal = $factory->modal()->lightbox($page);
    $button = $factory->button()->standard('Show Text', '')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button, $modal]);
}
