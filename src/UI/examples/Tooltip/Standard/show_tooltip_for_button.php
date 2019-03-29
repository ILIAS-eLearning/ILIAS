<?php
function show_tooltip_for_button() {
	global $DIC;

	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$tooltip = $factory->tooltip()->standard([$factory->legacy('Hello World')]);
	$button = $factory->button()
		->standard('Hover Me!', '#')
		->withOnHover($tooltip->getShowSignal());

	return $renderer->render([$tooltip, $button]);
}