<?php
function show_modal_on_button_click()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$content = $factory->legacy('Hello World 1');
	$modal = $factory->modal()->interruptive('Modal 1', $content)
		->withActionButton($factory->button()->primary('Delete', ''));
	$action_button = $factory->button()->primary('Open modal 1 with this button', '');
	$modal2 = $modal->withTitle('Modal 2')
		->withActionButton($action_button)
		->withContent($factory->legacy('Hello World 2'));

	// Buttons triggering the modals
	$button = $factory->button()->standard('Open modal 1 on click', '');
	$button2 = $button->withLabel('Also open modal 1 on click');
	$button3 = $button2->withLabel('Open modal 2 on hover');

	// Connect buttons with modals
	$connection = $factory->connector()->onClick($button, $modal->getShowAction());
	$connection2 = $factory->connector()->onClick($button2, $modal->getShowAction());
	$connection3 = $factory->connector()->onHover($button3, $modal2->getShowAction());
	$connection4 = $factory->connector()->onClick($action_button, $modal->getShowAction());

	// Note that the first button is rendered twice
	return implode('', $renderer->render([$button, $button, $button2, $button3, $modal, $modal2], [$connection, $connection2, $connection3, $connection4]));
}