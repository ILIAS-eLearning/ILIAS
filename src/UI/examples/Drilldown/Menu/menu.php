<?php
function menu() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$fd = $f->drilldown();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->icon()->standard('', '')->withSize('medium')->withAbbreviation('+');

	$image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
	$page = $f->modal()->lightboxImagePage($image, 'Mountains');
	$modal = $f->modal()->lightbox($page);

	$button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '#')
		->withOnClick($modal->getShowSignal());


	$dd = $fd->menu('root', $ico->withAbbreviation('DD'))
	->withEntries([
		$fd->submenu('1', $ico)->withEntries([
			$fd->submenu('1.1', $ico)->withEntries([
				$button,
				$button
			]),
			$fd->submenu('1.2', $ico)->withEntries([
				$fd->submenu('1.2.1', $ico)->withEntries([
					$button
				]),
				$fd->submenu('1.2.2', $ico)->withEntries([
					$button
				]),
			]),
			$button
		]),
		$fd->submenu('2', $ico)->withEntries([
			$fd->submenu('2.1'),
			$fd->submenu('2.2'),
			$fd->submenu('2.3')
		]),
		$fd->submenu('3', $ico)
			->withAdditionalEntry($button)
			->withAdditionalEntry($button)
	]);

	return $renderer->render([
		$dd,
		$modal
	]);
}