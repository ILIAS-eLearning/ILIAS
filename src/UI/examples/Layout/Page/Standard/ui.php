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

	$logo = $f->image()->responsive("src/UI/examples/Image/HeaderIconLarge.svg", "ILIAS");
	$breadcrumbs = pagedemoCrumbs($f);
	$content = pagedemoContent($f);
	$metabar = pagedemoMetabar($f);
	$mainbar = pagedemoMainbar($f, $renderer)
		->withActive("pws");

	$page = $f->layout()->page()->standard(
		$metabar,
		$mainbar,
		$content,
		$breadcrumbs,
		$logo
	);

	echo $renderer->render($page);
}
