<?php
function drilldown() {
	global $DIC;
	$f = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$ico = $f->icon()->standard('', '')->withSize('medium')->withAbbreviation('+');

	$image = $f->image()->responsive("src/UI/examples/Image/mountains.jpg", "Image source: https://stocksnap.io, Creative Commons CC0 license");
	$page = $f->modal()->lightboxImagePage($image, 'Mountains');
	$modal = $f->modal()->lightbox($page);

	$button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '')
		->withOnClick($modal->getShowSignal());

	$label = $f->button()->bulky($ico->withAbbreviation('0'), 'root', '');

	$items = [
		$f->menu()->sub(toBulky('1'), [
			$f->menu()->sub(
					toBulky('1.1'),
					[$button, $button]
				)
				->withInitiallyActive(),
			$f->menu()->sub(toBulky('1.2'), [
				$f->menu()->sub('1.2.1', [$button]),
				$f->menu()->sub('1.2.2', [$button])
			]),
			$button
		]),

		$f->menu()->sub(toBulky('2'), [
			$f->menu()->sub('2.1', [$button]),
			$f->menu()->sub('2.2', [$button]),
			$f->divider()->horizontal(),
			$f->menu()->sub('2.3', [$button])
		])
	];

	$dd = $f->menu()->drilldown($label, $items);

	return $renderer->render([
		$dd,
		$modal
	]);
}


function toBulky(string $label): \ILIAS\UI\Component\Button\Bulky
{
	global $DIC;
	$f = $DIC->ui()->factory();
	$ico = $f->icon()->standard('', '')
		->withSize('medium')
		->withAbbreviation('+');

	return $f->button()->bulky($ico, $label, '');
}