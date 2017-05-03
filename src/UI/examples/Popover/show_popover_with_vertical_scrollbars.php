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
	$content = "<div style='max-height: 200px; overflow-y: auto; padding-right: 10px;'>{$list}</div>";

	$popover = $factory->popover($factory->legacy($content))->withTitle('Series');
	$button = $factory->button()->standard('Show me some Series', '#')
		->withOnClick($popover->getShowSignal());

	return $renderer->render([$popover, $button]);
}