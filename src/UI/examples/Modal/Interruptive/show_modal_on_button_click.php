<?php
function show_modal_on_button_click()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$message = 'Are you sure you want to delete the following items?';
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$modal = $factory->modal()->interruptive('My Title', $message, $form_action)
		->withAffectedItems(array(
			$factory->modal()->interruptiveItem(10, 'Item1'),
			$factory->modal()->interruptiveItem(20, 'Item2'),
			$factory->modal()->interruptiveItem(30, 'Item3'),
		));
	$button = $factory->button()->standard('Show Modal', '')
		->withOnClick($modal->getShowSignal());

	// Display POST data of affected items in a panel
	$panel = '';
	if (isset($_POST['interruptive_items'])) {
		$panel = $factory->panel()->standard(
			'Affected Items',
			$factory->legacy(print_r($_POST['interruptive_items'], true))
		);
		$panel = $renderer->render($panel);
	}

	return $renderer->render([$button, $modal]) . $panel;
}