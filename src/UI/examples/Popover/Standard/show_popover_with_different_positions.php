<?php
function show_popover_with_different_positions()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $content = $factory->legacy('The position of this popover is calculated automatically based on the available space. Note that the max width CSS setting is used here, as this text is quite long.');
    $popover = $factory->popover()->standard($content);
    $button = $factory->button()->standard('Auto Popover', '#')
        ->withOnClick($popover->getShowSignal());

    $content = $factory->legacy('The position of this popover is either on top or bottom of the triggerer, based on the available space');
    $popover2 = $factory->popover()->standard($content)
        ->withVerticalPosition();
    $button2 = $factory->button()->standard('Vertical Popover', '#')
        ->withOnClick($popover2->getShowSignal());

    $content = $factory->legacy('The position of this popover is either on the left or right of the triggerer, based on the available space');
    $popover3 = $factory->popover()->standard($content)
        ->withHorizontalPosition();
    $button3 = $factory->button()->standard('Horizontal Popover', '#')
        ->withOnClick($popover3->getShowSignal());

    $buttons = implode(' ', [$renderer->render($button), $renderer->render($button2), $renderer->render($button3)]);

    return $buttons . $renderer->render([$popover, $popover2, $popover3]);
}
