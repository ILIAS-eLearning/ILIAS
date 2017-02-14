<?php
function show_modal_on_button_click()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$message = 'Are you sure you want to delete the following items?';
	$form_action = $DIC['ilCtrl']->getFormActionByClass('ilsystemstyledocumentationgui');
	$modal = $factory->modal()->interruptive('My Title', $message, $form_action)
		->withAffectedItems(array(
			$factory->modal()->interruptiveItem(10, 'Item1'),
			$factory->modal()->interruptiveItem(20, 'Item2'),
			$factory->modal()->interruptiveItem(30, 'Item3'),
		));
	$button = $factory->button()->standard('Show Modal', '');
	$connection = $factory->connector()->onClick($button, $modal->getShowAction());

	return implode('', $renderer->render([$button, $modal], [$connection]));
}