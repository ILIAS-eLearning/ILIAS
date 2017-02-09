<?php
function show_popover_on_button_click()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();
	$popover = $factory->popover('Title', 'Hello World');
	$button = $factory->button()->standard('Show Popover', '#')
		->withOnClick($popover->getShowSignal());
	$out = $renderer->render($button);

	$button2 = $button->withLabel('Show the same Popover', '#');
	$out .= ' ' . $renderer->render($button2);

	return $out . $renderer->render($popover);
}