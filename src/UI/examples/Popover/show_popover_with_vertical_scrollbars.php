<?php
function show_popover_with_vertical_scrollbars()
{
	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$series = [
		'Breaking Bad',
		'Big Bang Theory',
		'Dexter',
		'Better Call Saul',
		'Narcos',
		'Ray Donovan',
		'Simpsons',
		'South Park',
		'Fargo',
		'Bloodline',
		'The Walking Dead',
		'New Girl',
		'Sons of Anarchy',
		'How I Met Your Mother',
	];
	$list = $renderer->render($factory->listing()->unordered($series));
	// Note: The Popover does not restrict the height. It is the responsibility of the content component
	// to define the max height and to display vertical scrollbars, if necessary.
	// At the moment, the renderer of a component is not aware of the context it is rendering the component,
	// e.g. inside a Popover.
	// The inline code below simulates this behaviour. Here we want to reduce the
	// height of the list to 200px and display vertical scrollbars, if needed.
	$content = "<div style='max-height: 200px; overflow-y: auto; padding-right: 10px;'>{$list}</div>";

	$popover = $factory->popover($factory->legacy($content))->withTitle('Series');
	$button = $factory->button()->standard('Show me some Series', '#')
		->withOnClick($popover->getShowSignal());

	return $renderer->render([$popover, $button]);
}