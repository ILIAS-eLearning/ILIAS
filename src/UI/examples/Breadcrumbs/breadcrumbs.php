<?php
function breadcrumbs() {
	global $DIC;
	$renderer = $DIC->ui()->renderer();
	$f = $DIC->ui()->factory();

	$entry =

	$crumbs = array(
		$f->button()->shy("entry1", '#'),
		$f->button()->shy("entry2", '#'),
		$f->button()->shy("entry3", '#'),
		$f->button()->shy("entry4", '#')
	);

	$bar = $f->breadcrumbs($crumbs);

	$bar_extended = $bar->withAppendedEntry(
		$f->button()->shy("entry5", '#')
	);

	return $renderer->render($bar)
		.$renderer->render($bar_extended);
}