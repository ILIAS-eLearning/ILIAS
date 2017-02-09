<?php
function show_popover_on_button_hover()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$popover = $factory->popover('Popover', "I'm on the right side of my triggerer", 'right');
	$button = $factory->button()->standard('Show Right on Hover', '#')
		->withOnHover($popover->getShowSignal());

	return $renderer->render([$popover, $button]);
}
