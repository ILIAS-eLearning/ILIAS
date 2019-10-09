<?php
function show_modal_on_button_click_async_rendered()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $message = 'Are you sure you want to delete the following item?';
    $form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
    $items = ['First Item', 'Second Item', 'Third Item'];

    // Check if this is the ajax request to deliver the new modal showing the affected item
    if (isset($_GET['item'])) {
        $item = $items[(int) $_GET['item']];
        $affected_item = $factory->modal()->interruptiveItem((int) $_GET['item'], $item);
        $modal = $factory->modal()->interruptive('Delete Items', $message, $form_action)
            ->withAffectedItems([$affected_item]);
        echo $renderer->render($modal);
        exit();
    }

    // Create a button per item
    $out = '';
    foreach ($items as $i => $item) {
        $ajax_url = $_SERVER['REQUEST_URI'] . '&item=' . $i;
        $modal = $factory->modal()->interruptive('', '', '')
            ->withAsyncRenderUrl($ajax_url);
        $button = $factory->button()->standard('Delete ' . $item, '#')
            ->withOnClick($modal->getShowSignal());
        $out .= ' ' . $renderer->render([$button, $modal]);
    }

    return $out;
}
