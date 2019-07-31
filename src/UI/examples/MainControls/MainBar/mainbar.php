<?php
function mainbar()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$mainbar = buildMainbar($f, $renderer);
	return $renderer->render($mainbar);
}

function buildMainbar($f, $r)
{
	$tools_btn = $f->button()->bulky(
		$f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/grid.svg', ''),
		'Tools',
		'#'
	);
	$more_btn = $f->button()->bulky(
		$f->symbol()->icon()->standard('', ''),
		'more',
		'#'
	);

	$mainbar = $f->mainControls()->mainbar()
		->withToolsButton($tools_btn)
		->withMoreButton($more_btn);

	$entries = [];
	$entries['repository'] = getDemoEntryRepository($f);
	$entries['pws'] = getDemoEntryPersonalWorkspace($f);
	$entries['achievements'] = getDemoEntryAchievements($f);
	$entries['communication'] = getDemoEntryCommunication($f);
	$entries['organisation'] = getDemoEntryOrganisation($f);
	$entries['administration'] = getDemoEntryAdministration($f);

	foreach ($entries as $id=>$entry) {
		$mainbar = $mainbar->withAdditionalEntry($id, $entry);
	}
	$tools = getDemoEntryTools($f);
	foreach ($tools as $id=>$entry) {
		$mainbar = $mainbar->withAdditionalToolEntry($id, $entry);
	}

	return $mainbar;
}

function getDemoEntryRepository($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/layers.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Repository', $symbol, '');
	return $slate;
}

function getDemoEntryPersonalWorkspace($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/user.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Personal Workspace', $symbol, '');
	return $slate;
}

function getDemoEntryAchievements($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/achievements.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Achievements', $symbol, $f->legacy('content: Achievements'));
	return $slate;
}

function getDemoEntryCommunication($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/communication.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Communication', $symbol, $f->legacy('content: Communication'));
	return $slate;
}

function getDemoEntryOrganisation($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/organisation.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Organisation', $symbol, $f->legacy('content: Organisation'));
	return $slate;
}

function getDemoEntryAdministration($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/administration.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Administration', $symbol, $f->legacy('content: Administration'));
	return $slate;
}

function getDemoEntryTools($f)
{
	$symbol = $f->symbol()->icon()->custom('./src/UI/examples/Layout/Page/Standard/question.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Help', $symbol, $f->legacy('<h2>tool 1</h2><p>Some Text for Tool 1 entry</p>'));
	$tools = ['tool1' => $slate];
	return $tools;
}
