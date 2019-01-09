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
		$f->icon()->custom('./src/UI/examples/Layout/Page/Standard/grid.svg', ''),
		'Tools',
		'#'
	);
	$mainbar = $f->mainControls()->mainbar()
		->withToolsButton($tools_btn);


	$entries = [];


	$entries['repository'] = getDemoEntryRepository($f);
	$entries['pws'] = getDemoEntryPersonalWorkspace($f, $r);
	$entries['achievements'] = getDemoEntryAchievements($f);
	$entries['communication'] = getDemoEntryCommunication($f);
	$entries['organisation'] = getDemoEntryOrganisation($f);
	$entries['administration'] = getDemoEntryAdministration($f);

	//list($entries, $tools) = getDemoEntries($f);
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
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/layers.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Repository', $symbol, '');

	$icon = $f->icon()->standard('', '')->withSize('small')->withAbbreviation('X');
	$button = $f->button()->bulky($icon, 'Button 1', './src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1');
	$slate = $slate
		->withAdditionalEntry($button->withLabel('Repository - Home'))
		->withAdditionalEntry($button->withLabel('Repository - Tree'))
		->withAdditionalEntry($button->withLabel('Repository - Last visited'))
		->withAdditionalEntry($button->withLabel('Favorites'))
		->withAdditionalEntry($button->withLabel('Courses'))
		->withAdditionalEntry($button->withLabel('Groups'))
		->withAdditionalEntry($button->withLabel('Study Programme'))
		->withAdditionalEntry($button->withLabel('Own Repository-Objects'))
		;
	return $slate;
}

function getDemoEntryPersonalWorkspace($f, $r)
{
	$icon = $f->icon()->standard('', '')->withSize('small')->withAbbreviation('X');
	$button = $f->button()->bulky($icon, 'Button 1', './src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1');

	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/user.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Personal Workspace', $symbol, '');

	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/bookmarks.svg', '')->withSize('small');
	$bookmarks = implode('<br />', [
		$r->render($f->button()->shy('my bookmark 1', '#')),
		$r->render($f->button()->shy('my bookmark 2', '#'))
	]);
	$slate_bookmarks = $f->maincontrols()->slate()->legacy('Bookmarks', $symbol, $bookmarks);


	$slate = $slate
		->withAdditionalEntry($button->withLabel('Overview'))
		->withAdditionalEntry($slate_bookmarks)
		->withAdditionalEntry($button->withLabel('Calendar'))
		->withAdditionalEntry($button->withLabel('Task'))
		->withAdditionalEntry($button->withLabel('Portfolios'))
		->withAdditionalEntry($button->withLabel('Personal Resources'))
		->withAdditionalEntry($button->withLabel('Shared Resources'))
		->withAdditionalEntry($button->withLabel('Notes'))
		->withAdditionalEntry($button->withLabel('News'))
		->withAdditionalEntry($button->withLabel('Background Tasks'))
		;
	return $slate;
}

function getDemoEntryAchievements($f)
{
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/achievements.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Achievements', $symbol, '');
	return $slate;
}

function getDemoEntryCommunication($f)
{
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/communication.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Communication', $symbol, '');
	return $slate;
}

function getDemoEntryOrganisation($f)
{
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/organisation.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Organisation', $symbol, '');
	return $slate;
}

function getDemoEntryAdministration($f)
{
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/administration.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Administration', $symbol, '');
	return $slate;
}

function getDemoEntryTools($f)
{
	$tools = [];

	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/question.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Help', $symbol, '<h2>tool 1</h2><p>Some Text for Tool 1 entry</p>');
	$tools['tool1'] = $slate;
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/pencil.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Editor', $symbol, '<h2>tool 2</h2><p>Some Text for Tool 1 entry</p>');
	$tools['tool2'] = $slate;
	$symbol = $f->icon()->custom('./src/UI/examples/Layout/Page/Standard/notebook.svg', '')->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy('Local Navigation', $symbol, loremIpsum());
	$tools['tool3'] = $slate;

	return $tools;
}

function loremIpsum():string {
	return <<<EOT
	<h2>Lorem ipsum</h2>
	<p>
	Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
	</p>
	<p>
	Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.
	</p>
	<p>
	Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
	</p>
	<p>
	Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer
	</p>
EOT;
}
