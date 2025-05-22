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

namespace ILIAS\UI\examples\Modal\RoundTrip;

/**
 * ---
 * description: >
 *   Example for rendering a round trip modal with different buttons.
 *
 * expected output: >
 *   ILIAS shows three buttons with different titles. After clicking one of the first two buttonns a modal with primary
 *   and secondary buttons is opened. Both buttons do not have any functions.
 * ---
 */
function show_the_same_modal_with_different_buttons()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->roundtrip('My Modal 1', $factory->legacy()->content('My Content'))
        ->withActionButtons([
            $factory->button()->primary('Primary Action', ''),
            $factory->button()->standard('Secondary Action', ''),
        ]);

    $out = '';
    $button1 = $factory->button()->standard('Open Modal 1', '#')
        ->withOnClick($modal->getShowSignal());
    $out .= ' ' . $renderer->render($button1);

    $button2 = $button1->withLabel('Also opens modal 1');
    $out .= ' ' . $renderer->render($button2);

    $button3 = $button2->withLabel('Does not open modal 1')
        ->withResetTriggeredSignals();
    $out .= ' ' . $renderer->render($button3);

    return $out . $renderer->render($modal);
}
