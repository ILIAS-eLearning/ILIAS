<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Popover\Standard;

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
        $content = $factory->legacy('This text is rendered async');
        echo $renderer->render($content);
        exit();
    }

    $async_url = $_SERVER['REQUEST_URI'] . '&renderPopoverAsync=1';
    $popover = $factory->popover()->standard($factory->legacy(''))
        ->withTitle('Popover')
        ->withAsyncContentUrl($async_url);
    $button = $factory->button()->standard('Show Popover', '#')
        ->withOnClick($popover->getShowSignal());

    return $renderer->render([$popover, $button]);
}
