<?php
function base() {
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $message = 'Are you sure you want to delete the following items?';
    $form_action = $DIC['ilCtrl']->getFormActionByClass('ilsystemstyledocumentationgui');
	$modal = $factory->modal()->interruptive('My Title', $message, $form_action);
    // Note: This modal is just rendered in the DOM but not displayed
    return $renderer->render($modal);
}