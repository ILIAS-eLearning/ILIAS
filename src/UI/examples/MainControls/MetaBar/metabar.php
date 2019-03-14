<?php
function metabar()
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	return $renderer->render(buildMetabar($f));
}

function buildMetabar($f)
{
	$help = $f->button()->bulky($f->glyph()->help(),'Help', '#');
	$search = $f->button()->bulky($f->glyph()->search(),'Search', '#');
	$user = $f->button()->bulky($f->glyph()->user(),'User', '#');

	$notes = $f->maincontrols()->slate()->legacy(
		'Notification',
		$f->glyph()->notification(),
		$f->legacy('some content')
	);

	$metabar = $f->mainControls()->metabar()
		->withAdditionalEntry('search', $search)
		->withAdditionalEntry('help', $help)
		->withAdditionalEntry('notes', $notes)
		->withAdditionalEntry('user', $user)
		;

	return $metabar;
}
