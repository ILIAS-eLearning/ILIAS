<?php
function base() {
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$message = 'Are you sure you want to delete the following items?';
	$form_action = $DIC->ctrl()->getFormActionByClass('ilsystemstyledocumentationgui');
	$icon = $factory->image()->standard('./templates/default/images/icon_crs.svg', '');
	$modal = $factory->modal()->interruptive('My Title', $message, $form_action)
		->withAffectedItems(array(
			$factory->modal()->interruptiveItem(10, 'Course 1', $icon, 'Some description text'),
			$factory->modal()->interruptiveItem(20, 'Course 2', $icon, 'Another description text'),
			$factory->modal()->interruptiveItem(30, 'Course 3', $icon, 'Last but not least, a description'),
		));

	$modal2 = $factory->modal()->interruptive('Your Title', $message, $form_action)
		->withAffectedItems(array(
			$factory->modal()->interruptiveItem(40, 'Course 4', $icon, 'Some description text'),
			$factory->modal()->interruptiveItem(50, 'Course 5', $icon, 'Another description text'),
			$factory->modal()->interruptiveItem(60, 'Course 6', $icon, 'Last but not least, a description'),
		));

	$button = $factory->button()->toggle("", $modal->getShowSignal(), $modal2->getShowSignal());

	// Display POST data of affected items in a panel
	$panel = '';
	if (isset($_POST['interruptive_items'])) {
		$panel = $factory->panel()->standard(
			'Affected Items',
			$factory->legacy(print_r($_POST['interruptive_items'], true))
		);
		$panel = $renderer->render($panel);
	}

	return $renderer->render([$button, $modal, $modal2]) . $panel;
}