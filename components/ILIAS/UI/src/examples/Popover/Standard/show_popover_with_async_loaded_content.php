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

namespace ILIAS\UI\examples\Popover\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard popover with asynchronous loaded content.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Popover". After the first click the text shows up with a short delay.
 *   You can close the popover b clicking onto the ILIAS background outside of the popover.
 * ---
 */
function show_popover_with_async_loaded_content()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    if (
        $request_wrapper->has('renderPopoverAsync') &&
        $request_wrapper->retrieve('renderPopoverAsync', $refinery->kindlyTo()->bool())
    ) {
        // This is the ajax request to load the content of the popover. During the ajax request,
        // a loading spinner is presented to the user. Check the code below on how to construct the popover,
        // e.g. using Popover::withAsyncContentUrl().
        $content = $factory->legacy()->content('This text is rendered async');
        echo $renderer->render($content);
        exit();
    }

    $async_url = $_SERVER['REQUEST_URI'] . '&renderPopoverAsync=1';
    $popover = $factory->popover()->standard($factory->legacy()->content(''))
        ->withTitle('Popover')
        ->withAsyncContentUrl($async_url);
    $button = $factory->button()->standard('Show Popover', '#')
        ->withOnClick($popover->getShowSignal());

    return $renderer->render([$popover, $button]);
}
