<?php
include_once('z_auxilliary.php');

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

	$crumbs = array (
		$f->link()->standard("entry1", '#'),
		$f->link()->standard("entry2", '#'),
		$f->link()->standard("entry3", '#'),
		$f->link()->standard("entry4", '#')
	);
	$breadcrumbs = $f->breadcrumbs($crumbs);

	$content = pagedemoContent($f);
	$metabar = buildMetabar($f);
	$mainbar = buildMainbar($f)
		->withActive("tool1");
		//->withActive("example2");


	$page = $f->layout()->page()->standard(
		$metabar,
		$mainbar,
		$content,
		$breadcrumbs
	);

	echo $renderer->render($page);
}
