<?php
function mainbar()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$mainbar = buildMainbar($f);
	return $renderer->render($mainbar);
}

function buildMainbar($f)
{
	list($entries, $tools) = getSomeEntries($f);

	$mainbar = $f->mainControls()->mainbar();

	foreach ($entries as $id=>$entry) {
		$mainbar = $mainbar->withEntry($id, $entry);
	}
	foreach ($tools as $id=>$entry) {
		$mainbar = $mainbar->withToolEntry($id, $entry);
	}

	return $mainbar;
}

function getSomeEntries($f)
{
	$entries = [];
	$tools = [];

	//add a slate
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/icon-sb-navigation.svg', '');
	$slate = $f->maincontrols()->slate()->legacy('Legacy', $symbol, 'legacy content');
	$entries['example1'] = $slate;

	//a slate with buttons and more slates
	$icon = $f->icon()->standard('', '')->withSize('medium')->withAbbreviation('X');
	$button = $f->button()->bulky($icon, 'Button 1', '#');

	$slate1 = $f->maincontrols()->slate()->legacy('Legacy 1', $symbol, 'legacy content1');
	$slate2 = $f->maincontrols()->slate()->legacy('Legacy 2', $symbol, 'legacy content 2');
	$slate3_1 = $f->maincontrols()->slate()->legacy('Legacy 3.1', $symbol, 'legacy content 3.1');
	$slate3_2 = $f->maincontrols()->slate()->legacy('Legacy 3.2', $symbol, 'legacy content 3.2');
	$slate3 = $f->maincontrols()->slate()->combined('SubSlates', $symbol)
		->withEntry($slate3_1)
		->withEntry($slate3_2)
		->withEntry($button->withLabel('Button 3.3'));

	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/icon-sb-cockpit.svg', '');
	$slate = $f->maincontrols()->slate()->combined('Combined', $symbol)
		->withEntry($button)
		->withEntry($button->withLabel('Button 2'))
		->withEntry($button->withLabel('Button 3'))
		->withEntry($slate1)
		->withEntry($slate2)
		->withEntry($button->withLabel('Button 4'))
		->withEntry($slate3)
		->withEntry($button->withLabel('Button 5'))
	;
	$entries['example2'] = $slate;

	//add a button
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/icon-sb-more.svg', '');
	$entries['extra'] = $f->button()->bulky($symbol,'Extra', '#');

	//add tool(slate)
	$symbol = $f->icon()->standard('', 'Tool 1')->withAbbreviation('T1');
	$slate = $f->maincontrols()->slate()->legacy('Tool 1', $symbol, 'tool 1');
	$tools['tool1'] = $slate;

	$symbol = $f->icon()->standard('', 'Tool 2')->withAbbreviation('T2');
	$slate = $f->maincontrols()->slate()->legacy('Tool 2', $symbol, 'tool 2');
	$tools['tool2'] = $slate;

	$symbol = $f->icon()->standard('', 'Tool 3')->withAbbreviation('T3');
	$slate = $f->maincontrols()->slate()->legacy('Tool 3', $symbol, 'tool 3');
	$tools['tool3'] = $slate;


	return [$entries, $tools];
}
