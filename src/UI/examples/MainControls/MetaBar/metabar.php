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
	$notes = $f->button()->bulky($f->glyph()->notification(),'Notification', '#');
	$user = $f->button()->bulky($f->glyph()->user(),'User', '#');

	$metabar = $f->mainControls()->metabar()
		->withEntry('search', $search)
		->withEntry('help', $help)
		->withEntry('notes', $notes)
		->withEntry('user', $user)
		;

	return $metabar;
}
