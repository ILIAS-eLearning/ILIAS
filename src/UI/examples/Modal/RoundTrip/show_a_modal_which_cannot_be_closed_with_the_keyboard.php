<?php
function show_a_modal_which_cannot_be_closed_with_the_keyboard()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->roundtrip('My Modal 1', $factory->legacy('You cannot close this modal with the ESC key'));
    $modal = $modal->withCloseWithKeyboard(false);
    $button1 = $factory->button()->standard('Open Modal 1', '#')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button1, $modal]);
}
