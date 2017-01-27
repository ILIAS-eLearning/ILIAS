<?php
function show_modal_on_button_click_with_async_loaded_content()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$message = 'Are you sure you want to delete the following item?';
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$items = ['First Item', 'Second Item', 'Third Item'];

	// Check if this is the ajax request to deliver the new modal showing the affected item
	if (isset($_GET['item'])) {
		$item = $items[(int)$_GET['item']];
		$affected_item = $factory->modal()->interruptiveItem((int) $_GET['item'], $item);
		$modal = $factory->modal()->interruptive('Delete Items', $message, $form_action)
			->withAffectedItems([$affected_item]);
		echo $renderer->render($modal);
		exit();
	}

	// Note: The modal is only rendered once in the DOM, its content is loaded via ajax
	$modal = $factory->modal()->interruptive('Delete Item', $message, $form_action);

	// Create a button per item
	$buttons = [];
	foreach ($items as $i => $item) {
		$ajax_url = $_SERVER['REQUEST_URI'] . '&item=' . $i;
		$buttons[] = $factory->button()->standard('Delete ' . $item, '#')
			->withOnClick($modal->getShowSignal(), ['ajaxUrl' => $ajax_url]);
	}

	return implode(' ', $renderer->render(array_merge($buttons, [$modal])));
}