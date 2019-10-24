<?php
function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $message1 = 'Toggle Button has been turned on';
    $message2 = 'Toggle Button has been turned off';
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');

    $modal = $factory->modal()->interruptive('ON', $message1, $form_action);
    $modal2 = $factory->modal()->interruptive('OFF', $message2, $form_action);

    $button = $factory->button()->toggle("", $modal->getShowSignal(), $modal2->getShowSignal());

    return $renderer->render([$button, $modal, $modal2]);
}
