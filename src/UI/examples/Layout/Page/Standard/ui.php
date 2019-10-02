<?php
function ui()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$url = 'src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1';
	$btn = $f->button()->standard('See UI in fullscreen-mode', $url);
	return $renderer->render($btn);
}


if ($_GET['new_ui'] == '1') {

	_initIliasForPreview();

	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$logo = $f->image()->responsive("src/UI/examples/Image/HeaderIconLarge.svg", "ILIAS");
	$breadcrumbs = pagedemoCrumbs($f);
	$content = pagedemoContent($f);
	$metabar = pagedemoMetabar($f);
	$mainbar = pagedemoMainbar($f, $renderer)
		->withActive("pws");

	$footer = pagedemoFooter($f);

	$page = $f->layout()->page()->standard(
		$content,
		$metabar,
		$mainbar,
		$breadcrumbs,
		$logo,
		$footer
	)
	->withUIDemo(true);
	;

	echo $renderer->render($page);
}

/**
 * Below are helpers for the construction of demo-content
 */

function _initIliasForPreview()
{
	chdir('../../../../../../');
	require_once("Services/Init/classes/class.ilInitialisation.php");
	require_once('src/UI/examples/Layout/Page/Standard/ui.php');
	ilInitialisation::initILIAS();
}

function pagedemoCrumbs($f)
{
	$crumbs = array (
		$f->link()->standard("entry1", '#'),
		$f->link()->standard("entry2", '#'),
		$f->link()->standard("entry3", '#'),
		$f->link()->standard("entry4", '#')
	);
	return $f->breadcrumbs($crumbs);
}

function pagedemoContent($f)
{
	return array (
		$f->panel()->standard('Demo Content',
			$f->legacy("some content<br>some content<br>some content<br>x.")),
		$f->panel()->standard('Demo Content 2',
			$f->legacy("some content<br>some content<br>some content<br>x.")),
		$f->panel()->standard('Demo Content 3',
			$f->legacy(loremIpsum())),
		$f->panel()->standard('Demo Content 4',
			$f->legacy("some content<br>some content<br>some content<br>x."))
	);
}



function pagedemoFooter($f)
{
	$df = new \ILIAS\Data\Factory();
	$text = 'Additional info:';
	$links = [];
	$links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
	$links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

	$footer = $f->mainControls()->footer($links, $text)
		->withPermanentURL(
			$df->uri(
				$_SERVER['REQUEST_SCHEME'].
				'://'.
				$_SERVER['SERVER_NAME'].
				':'.
				$_SERVER['SERVER_PORT'].
				$_SERVER['SCRIPT_NAME'].
				'?'.
				$_SERVER['QUERY_STRING']
			)
		);
	return $footer;
}

function pagedemoMetabar($f)
{
	$help = $f->button()->bulky($f->symbol()->glyph()->help(),'Help', '#');
	$user = $f->button()->bulky($f->symbol()->glyph()->user(),'User', '#');
	$search = $f->maincontrols()->slate()->legacy(
		'Search',
		$f->symbol()->glyph()->search()->withCounter($f->counter()->status(1)),
		$f->legacy(substr(loremIpsum(), 0, 180))
	);
	$notes = $f->maincontrols()->slate()->legacy(
		'Notification',
		$f->symbol()->glyph()->notification()->withCounter($f->counter()->novelty(3)),
		$f->legacy('<p>some content</p>')
	);

	$metabar = $f->mainControls()->metabar()
		->withAdditionalEntry('search', $search)
		->withAdditionalEntry('help', $help)
		->withAdditionalEntry('notes', $notes)
		->withAdditionalEntry('user', $user)
		;

	return $metabar;
}

function pagedemoMainbar($f, $r)
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
	$entries['pws'] = getDemoEntryPersonalWorkspace($f, $r);
	$entries['achievements'] = getDemoEntryAchievements($f);
	$entries['communication'] = getDemoEntryCommunication($f);
	$entries['organisation'] = getDemoEntryOrganisation($f);
	$entries['administration'] = getDemoEntryAdministration($f);

	foreach ($entries as $id => $entry) {
		$mainbar = $mainbar->withAdditionalEntry($id, $entry);
	}
	$tools = getDemoEntryTools($f);
	foreach ($tools as $id => $entry) {
		$mainbar = $mainbar->withAdditionalToolEntry($id, $entry);
	}

	return $mainbar;
}


function getDemoEntryRepository($f)
{
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/layers.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->combined('Repository', $symbol, '');

	$icon = $f->symbol()->icon()
		->standard('', '')
		->withSize('small')
		->withAbbreviation('X');

	$button = $f->button()->bulky(
		$icon,
		'Button 1',
		'./src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1'
	);

	$df = new \ILIAS\Data\Factory();
	$url = $df->uri(
		$_SERVER['REQUEST_SCHEME'].
		'://'.
		$_SERVER['SERVER_NAME'].
		':'.
		$_SERVER['SERVER_PORT'].
		$_SERVER['SCRIPT_NAME'].
		'?'.
		$_SERVER['QUERY_STRING']
	);
	$link1 = $f->link()->bulky($icon, 'Favorites (Link)', $url);
	$link2 = $f->link()->bulky($icon, 'Courses (Link2)', $url);
	$link3 = $f->link()->bulky($icon, 'Groups (Link)', $url);

	$slate = $slate
		->withAdditionalEntry($button->withLabel('Repository - Home'))
		->withAdditionalEntry($button->withLabel('Repository - Tree'))
		->withAdditionalEntry($button->withLabel('Repository - Last visited'))
		->withAdditionalEntry($link1)
		->withAdditionalEntry($link2)
		->withAdditionalEntry($link3)
		->withAdditionalEntry($button->withLabel('Study Programme'))
		->withAdditionalEntry($button->withLabel('Own Repository-Objects'))
		;

	foreach (range(1, 20) as $cnt) {
		$slate = $slate
			->withAdditionalEntry($button->withLabel('fillup ' .$cnt));
	}

	return $slate;
}

function getDemoEntryPersonalWorkspace($f, $r)
{
	$icon = $f->symbol()->icon()
		->standard('', '')
		->withSize('small')
		->withAbbreviation('X');

	$button = $f->button()->bulky(
		$icon,
		'Button 1',
		'./src/UI/examples/Layout/Page/Standard/ui.php?new_ui=1'
	);

	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/user.svg', '')
		->withSize('small');

	$slate = $f->maincontrols()->slate()
		->combined('Personal Workspace', $symbol, '');

	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/bookmarks.svg', '')
		->withSize('small');

	$bookmarks = $f->legacy(implode('<br />', [
		$r->render($f->button()->shy('my bookmark 1', '#')),
		$r->render($f->button()->shy('my bookmark 2', '#'))
	]));
	$slate_bookmarks = $f->maincontrols()->slate()
		->legacy('Bookmarks', $symbol, $bookmarks);

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
		->withAdditionalEntry($slate_bookmarks)
		;
	return $slate;
}

function getDemoEntryAchievements($f)
{
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/achievements.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Achievements',
		$symbol,
		$f->legacy('content: Achievements')
	);
	return $slate;
}

function getDemoEntryCommunication($f)
{
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/communication.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Communication',
		$symbol,
		$f->legacy('content: Communication')
	);
	return $slate;
}

function getDemoEntryOrganisation($f)
{
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/organisation.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Organisation',
		$symbol,
		$f->legacy('content: Organisation')
	);
	return $slate;
}

function getDemoEntryAdministration($f)
{
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/administration.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Administration',
		$symbol,
		$f->legacy('content: Administration')
	);
	return $slate;
}

function getDemoEntryTools($f)
{
	$tools = [];

	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/question.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Help',
		$symbol,
		$f->legacy('<h2>tool 1</h2><p>Some Text for Tool 1 entry</p>')
	);
	$tools['tool1'] = $slate;
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/pencil.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Editor',
		$symbol,
		$f->legacy('<h2>tool 2</h2><p>Some Text for Tool 1 entry</p>')
	);
	$tools['tool2'] = $slate;
	$symbol = $f->symbol()->icon()
		->custom('./src/UI/examples/Layout/Page/Standard/notebook.svg', '')
		->withSize('small');
	$slate = $f->maincontrols()->slate()->legacy(
		'Local Navigation',
		$symbol,
		$f->legacy(loremIpsum())
	);
	$tools['tool3'] = $slate;

	return $tools;
}

function loremIpsum():string {
	return <<<EOT
	<h2>Lorem ipsum</h2>
	<p>
	Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
	tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
	At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren,
	no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
	consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
	dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo
	duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus
	est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur
	sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore
	magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo
	dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est
	Lorem ipsum dolor sit amet.
	</p>
	<p>
	Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie
	 consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan
	  et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis
	   dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer
	   adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore
	   magna aliquam erat volutpat.
	</p>
	<p>
	Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit
	lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure
	dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore
	eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui
	blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
	</p>
	<p>
	Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming
	id quod mazim placerat facer
	</p>
EOT;
}
