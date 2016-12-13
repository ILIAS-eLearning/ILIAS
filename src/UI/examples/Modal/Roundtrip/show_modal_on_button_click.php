<?php
function show_modal_on_button_click()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $content = $factory->legacy('This modal was opened on button click');
    $modal = $factory->modal()->roundtrip('Modal Title', $content)
        ->withButtons(array(
            $factory->button()->primary('First Action', ''),
            $factory->button()->standard('Second Action', '')
        ));
    $button = $factory->button()->standard('Open Modal', '');

    return $renderer->render($button->triggerAction($modal->show()));
}