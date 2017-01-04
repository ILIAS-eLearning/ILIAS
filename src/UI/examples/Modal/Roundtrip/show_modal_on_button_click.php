<?php
function show_modal_on_button_click()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$action_buttons = array(
		$factory->button()->primary('Primary Action', ''),
		$factory->button()->standard('Secondary Action', ''),
	);
	$modal = $factory->modal()->roundtrip('My Modal', $factory->legacy('My Content'))
		->withActionButtons($action_buttons);
	$button = $factory->button()->standard('Show Modal', '');
	$connection = $factory->connector()->onClick($button, $modal->getShowAction());

	return implode('', $renderer->render([$button, $modal], [$connection]));
}