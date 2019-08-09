<?php
function show_card_in_popover()
{
    global $DIC;

    // This example shows how to render a card containing an image and a descriptive list inside a popover.
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $factory->image()->responsive("./templates/default/images/HeaderIcon.svg", "Thumbnail Example");
    $card = $factory->card()->standard("Title", $image)->withSections(array($factory->legacy("Hello World, I'm a card")));
    $popover = $factory->popover()->standard($card)->withTitle('Card');
    $button = $factory->button()->standard('Show Card', '#')
        ->withOnClick($popover->getShowSignal());

    return $renderer->render([$popover, $button]);
}
