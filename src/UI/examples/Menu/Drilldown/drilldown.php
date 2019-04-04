<?php
function drilldown() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->icon()->standard('', '')->withSize('medium')->withAbbreviation('+');

	$image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
	$page = $f->modal()->lightboxImagePage($image, 'Mountains');
	$modal = $f->modal()->lightbox($page);

	$button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '#')
		->withOnClick($modal->getShowSignal());


	$dd = $f->menu()->drilldown('root', $ico->withAbbreviation('DD'))
	->withEntries([
		$f->menu()->sub('1', $ico)->withEntries([
			$f->menu()->sub('1.1', $ico)->withEntries([
				$button,
				$button
			]),
			$f->menu()->sub('1.2', $ico)->withEntries([
				$f->menu()->sub('1.2.1', $ico)->withEntries([
					$button
				]),
				$f->menu()->sub('1.2.2', $ico)->withEntries([
					$button
				]),
			]),
			$button
		]),

		$f->menu()->sub('2', $ico)->withEntries([
			$f->menu()->sub('2.1'),
			$f->menu()->sub('2.2'),
			$f->menu()->sub('2.3')
		])
		->withInitiallyActive(),

		$f->menu()->sub('3', $ico)
			->withAdditionalEntry($button)
			->withAdditionalEntry($button)
	]);

	return $renderer->render([
		$dd,
		$modal
	]);
}