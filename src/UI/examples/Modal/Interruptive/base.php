<?php
function base() {
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $content = $factory->legacy('Modal Content');

    // Note: This modal is just rendered in the DOM but not displayed
    return $renderer->render($factory->modal()->interruptive("Modal Title", $content));
}