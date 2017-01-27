<?php
function show_the_same_modal_with_different_buttons()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$modal = $factory->modal()->roundtrip('My Modal 1', $factory->legacy('My Content'))
		->withActionButtons([
			$factory->button()->primary('Primary Action', ''),
			$factory->button()->standard('Secondary Action', ''),
		]);

	$button1 = $factory->button()->standard('Open Modal 1', '#')
		->withOnClick($modal->getShowSignal());

	$button2 = $button1->withLabel('Also opens modal 1');

	$button3 = $button2->withLabel('Does not open modal 1')
		->withResetTriggeredSignals();

	return implode(' ', $renderer->render([$button1, $button2, $button3, $modal]));
}