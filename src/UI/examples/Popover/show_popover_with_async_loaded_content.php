<?php
function show_popover_with_async_loaded_content()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	if (isset($_GET['renderPopoverAsync']) && $_GET['renderPopoverAsync']) {
		$content = $factory->legacy('This text is rendered async');
		echo $renderer->render($content);
		exit();
	}

	$async_url = $_SERVER['REQUEST_URI'] . '&renderPopoverAsync=1';
	$popover = $factory->popover($factory->legacy(''))
		->withTitle('Popover')
		->withAsyncContentUrl($async_url);
	$button = $factory->button()->standard('Show Popover', '#')
		->withOnClick($popover->getShowSignal());

	return $renderer->render([$popover, $button]);
}