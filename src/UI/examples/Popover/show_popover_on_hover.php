<?php
function show_popover_on_hover()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$popover = $factory->popover('Popover', $factory->legacy('Hello World'));
	$button = $factory->button()->standard('Show Popover on Hover', '#')
		->withOnHover($popover->getShowSignal());

	return $renderer->render([$popover, $button]);
}