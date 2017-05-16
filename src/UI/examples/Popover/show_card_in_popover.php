<?php
function show_card_in_popover() {
	global $DIC;

	// This example shows how to render a card containing an image and a descriptive list inside a popover.
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $factory->listing()->descriptive(
		array(
			"Entry 1" => "Some text",
			"Entry 2" => "Lorem ipsum dolor sit amet",
		)
	);
	$image = $factory->image()->responsive("./templates/default/images/HeaderIcon.svg", "Thumbnail Example");
	$card = $factory->card("Title", $image)->withSections(array($content));
	$popover = $factory->popover($card)->withTitle('Card');
	$button = $factory->button()->standard('Show Card', '#')
		->withOnClick($popover->getShowSignal());

	return $renderer->render([$popover, $button]);
}