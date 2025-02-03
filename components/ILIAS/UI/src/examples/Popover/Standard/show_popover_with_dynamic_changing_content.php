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

namespace ILIAS\UI\Examples\Popover\Standard;

use ILIAS\UI\Implementation\Component\ReplaceContentSignal;

/**
 * ---
 * description: >
 *   Example for rendering a standard popover with dynamic changing content.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Popover".
 *   A click onto the button opens the popover including buttons. You can navigate through the popover content by clicking
 *   those buttons.
 *   You can close the popover by clicking onto the ILIAS background outside of the popover.
 * ---
 */
function show_popover_with_dynamic_changing_content()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    // This example shows how to change the content of a popover dynamically with ajax requests.
    // Each popover offers a signal to replace its content, similar to the show signal which shows the popover.
    // The replace signal will load the new content via ajax from a given URL and insert it into the popover.

    // The popover in this example initially shows three buttons. Each button will replace the content
    // of the popover with a new "page" showing some text. Each page also contains a back button which
    // again replaces the content of the popover with the overview page.

    $url = $_SERVER['REQUEST_URI'];

    // This is an ajax request to render the overview page showing the three buttons
    if ($request_wrapper->has('page') && $request_wrapper->retrieve('page', $refinery->kindlyTo()->string()) == 'overview') {
        // Note: The ID of the replace signal is sent explicitly as GET parameter. This is a proof of concept
        // and may be subject to change, as the framework could send such parameters implicitly.
        $signalId = $request_wrapper->retrieve('replaceSignal', $refinery->kindlyTo()->string());
        $replaceSignal = new ReplaceContentSignal($signalId);
        $button1 = $factory->button()->standard('Go to page 1', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=1&replaceSignal=' . $signalId));
        $button2 = $factory->button()->standard('Go to page 2', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=2&replaceSignal=' . $signalId));
        $button3 = $factory->button()->standard('Go to page 3', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=3&replaceSignal=' . $signalId));
        $list = $factory->listing()->unordered([$button1, $button2, $button3]);
        echo $renderer->renderAsync($list);
        exit();
    }

    // This is an ajax request to render a page
    if ($request_wrapper->has('page')) {
        $page = $request_wrapper->retrieve('page', $refinery->kindlyTo()->int());
        $signalId = $request_wrapper->retrieve('replaceSignal', $refinery->kindlyTo()->string());
        $replaceSignal = new ReplaceContentSignal($signalId);
        $button = $factory->button()->standard('Back to Overview', '#')
            ->withOnClick($replaceSignal->withAsyncRenderUrl($url . '&page=overview&replaceSignal=' . $signalId));
        $intro = $factory->legacy()->content("<p>You are viewing page $page</p>");
        echo $renderer->renderAsync([$intro, $button]);
        exit();
    }

    // This is the "normal" request to render the popover. Any content of the popover is rendered async.
    $popover = $factory->popover()->standard($factory->legacy()->content(''))->withTitle('Pages');
    $asyncUrl = $url . '&page=overview&replaceSignal=' . $popover->getReplaceContentSignal()->getId();
    $popover = $popover->withAsyncContentUrl($asyncUrl);
    $button = $factory->button()->standard('Show Popover', '#')
        ->withOnClick($popover->getShowSignal());
    return $renderer->render([$popover, $button]);
}
